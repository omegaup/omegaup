package omegaup.grader

import java.io._
import java.util.concurrent._
import javax.servlet._
import javax.servlet.http._
import org.eclipse.jetty.server.Request
import org.eclipse.jetty.server.handler._
import net.liftweb.json._
import omegaup._
import omegaup.data._
import omegaup.grader.drivers._
import omegaup.runner._
import omegaup.broadcaster.Broadcaster
import Status._
import Language._
import Veredict._
import Validator._
import Server._

object RunnerRouter extends Object with Log {
	private val defaultQueueName = "#default"
	private val dispatchers = scala.collection.mutable.HashMap[String, RunnerDispatcher](
		defaultQueueName -> new RunnerDispatcher(defaultQueueName)
	)

	def status() = dispatchers.toMap.map(entry => {entry._1 -> entry._2.status()})

	def register(name: String, host: String, port: Int): RegisterOutputMessage = {
		dispatchers(defaultQueueName).register(name, host, port)
	}

	def deregister(host: String, port: Int): RegisterOutputMessage = {
		dispatchers(defaultQueueName).deregister(host, port)
	}

	def addRunner(runner: RunnerService) = {
		dispatchers(defaultQueueName).addRunner(runner)
	}

	def addRun(run: Run) = {
		dispatchers(defaultQueueName).addRun(run)
	}
}

class RunnerDispatcher(val name: String) extends Object with Log {
	private case class InFlightRun(service: RunnerService, run: Run, timestamp: Long)
	private val registeredEndpoints = scala.collection.mutable.HashMap.empty[RunnerEndpoint, Long]
	private val runnerQueue = scala.collection.mutable.Queue.empty[RunnerService]
	private val runQueue = scala.collection.mutable.Queue.empty[Run]
	private val runsInFlight = scala.collection.mutable.HashMap.empty[Long, InFlightRun]
	private val executor = Executors.newCachedThreadPool
	private var flightIndex: Long = 0
	private val lock = new Object

	private val pruner = new java.util.Timer("Flight pruner", true)
	pruner.scheduleAtFixedRate(
		new java.util.TimerTask() {
			override def run(): Unit = pruneFlights
		},
		Config.get("grader.flight_pruner.interval", 60) * 1000,
		Config.get("grader.flight_pruner.interval", 60) * 1000
	)

	def status() = lock.synchronized {
		QueueStatus(
			run_queue_length = runQueue.size,
			runner_queue_length = runnerQueue.size,
			runners = registeredEndpoints.keys.map(_.name).toList,
			running = runsInFlight.values.map(ifr => new Running(ifr.service.name, ifr.run.id.toInt)).toList
		)
	}

	def register(name: String, host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(name, host, port)

		lock.synchronized {
			if (!registeredEndpoints.contains(endpoint)) {
				val proxy = new RunnerProxy(name, endpoint.host, endpoint.port) 
				info("Registering {}", proxy)
				registeredEndpoints += endpoint -> System.currentTimeMillis
				addRunner(proxy)
			}
			registeredEndpoints(endpoint) = System.currentTimeMillis
		}

		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	private def deregisterLocked(endpoint: RunnerEndpoint) = {
		if (registeredEndpoints.contains(endpoint)) {
			info("De-registering {}", endpoint)
			registeredEndpoints -= endpoint
		}
	}

	def deregister(host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint("", host, port)

		lock.synchronized {
			deregisterLocked(endpoint)
		}

		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	def addRun(run: Run) = {
		lock.synchronized {
			debug("Adding run {}", run)
			runQueue += run
			dispatchLocked
		}
	}

	def addRunner(runner: RunnerService) = {
		lock.synchronized {
			addRunnerLocked(runner)
		}
	}

	private class RunnerEndpoint(val name: String, val host: String, val port: Int) {
		def ==(o: RunnerEndpoint) = host == o.host && port == o.port
		override def hashCode() = 28227 + 97 * host.hashCode + port
		override def equals(other: Any) = other match {
			case x:RunnerEndpoint => host == x.host && port == x.port
			case _ => false
		}
		override def toString() = "RunnerEndpoint(%s, %s:%d)".format(name, host, port)
	}

	private class GradeTask(
		dispatcher: RunnerDispatcher,
		flightIndex: Long,
		var r: Run,
		runner: RunnerService,
		driver: Driver
	) extends Runnable {
		override def run(): Unit = {
			val future = dispatcher.executor.submit(new Callable[Run]() {
					override def call(): Run = {
						driver.run(r.copy, runner)
					}
			})

			r = try {
				future.get(Config.get("grader.runner.timeout", 10 * 60) * 1000, TimeUnit.MILLISECONDS)
			} catch {
				case e: ExecutionException => {
					error("Submission {} {} failed - {} {}",
						r.problem.alias,
						r.id,
						e.getCause.toString,
						e.getCause.getStackTrace
					)

					e.getCause match {
						case inner: java.net.SocketException => {
							// Probably a network error of some sort. No use in re-queueing the runner.
							runner match {
								case proxy: omegaup.runner.RunnerProxy => dispatcher.deregister(proxy.host, proxy.port)
								case _ => {}
							}

							// But do re-queue the run
							RunnerRouter.addRun(r)

							// And commit suicide
							throw e
						}
						case _ => {
							r.score = 0
							r.contest_score = 0
							r.status = Status.Ready
							r.veredict = Veredict.JudgeError
						}
					}

					r
				}
				case e: TimeoutException => {
					error("Submission {} {} timed out - {} {}", r.problem.alias, r.id, e.toString, e.getStackTrace)

					// Probably a network error of some sort. No use in re-queueing the runner.
					runner match {
						case proxy: omegaup.runner.RunnerProxy => dispatcher.deregister(proxy.host, proxy.port)
						case _ => {}
					}

					r
				}
			} finally {
				dispatcher.flightFinished(flightIndex)
			}

			Manager.updateVeredict(
				if (r.status == Status.Ready) {
					r
				} else {
					try {
						driver.grade(r.copy)
					} catch {
						case e: Exception => {
							r.score = 0
							r.contest_score = 0
							r.status = Status.Ready
							r.veredict = Veredict.JudgeError

							r
						}
					}
				}
			)
		}
	}

	private def runLocked() = {
		val runner = runnerQueue.dequeue
		val run = runQueue.dequeue

		runsInFlight += flightIndex -> InFlightRun(runner, run, System.currentTimeMillis)
		executor.submit(new GradeTask(this, flightIndex, run, runner, OmegaUpDriver))

		flightIndex += 1
	}

	private def pruneFlights() = lock.synchronized {
		var cutoffTime = System.currentTimeMillis - Config.get("grader.runner.timeout", 10 * 60) * 1000
		runsInFlight.foreach { case (i, flightInfo) => {
			if (flightInfo.timestamp < cutoffTime) {
				warn("Expiring stale flight {}, run {}", flightInfo.service, flightInfo.run.id)
				flightInfo.service match {
					case proxy: RunnerProxy => deregisterLocked(new RunnerEndpoint(proxy.name, proxy.host, proxy.port))
					case _ => {}
				}
				runsInFlight -= i
			}
		}}
	}

	private def flightFinished(flightIndex: Long) = lock.synchronized {
		if (runsInFlight.contains(flightIndex)) {
			var flightRun = runsInFlight(flightIndex)
			runsInFlight -= flightIndex
			addRunnerLocked(flightRun.service)
		} else {
			error("Lost track of flight {}!", flightIndex)
			throw new RuntimeException("Flight corrupted, bail out")
		}
	}

	private def addRunnerLocked(runner: RunnerService) = {
		debug("Adding runner {}", runner)
		runnerQueue += runner
		dispatchLocked
	}

	private def dispatchLocked() = {
		// Prune any runners that are not registered or haven't communicated in a while.
		debug("Before pruning the queue {}", status)
		var cutoffTime = System.currentTimeMillis -
			Config.get("grader.runner.queue_timeout", 10 * 60 * 1000)
		runnerQueue.dequeueAll (
			_ match {
				case proxy: omegaup.runner.RunnerProxy => {
					val endpoint = new RunnerEndpoint(proxy.name, proxy.host, proxy.port)
					// Also expire stale endpoints.
					if (registeredEndpoints.contains(endpoint) &&
							registeredEndpoints(endpoint) < cutoffTime) {
						warn("Stale endpoint {}", proxy)
						deregister(endpoint.host, endpoint.port)
					}
					!registeredEndpoints.contains(endpoint)
				}
				case _ => false
			}
		)

		debug("After pruning the queue {}", status)

		if (!runnerQueue.isEmpty && !runQueue.isEmpty) {
			debug("But there's enough to run something!")
			runLocked
		}
	}
}

object Manager extends Object with Log {
	private val listeners = scala.collection.mutable.ListBuffer.empty[Run => Unit]

	// Loading SQL connector driver
	Class.forName(Config.get("db.driver", "org.h2.Driver"))
	val connection = java.sql.DriverManager.getConnection(
		Config.get("db.url", "jdbc:h2:file:omegaup"),
		Config.get("db.user", "omegaup"),
		Config.get("db.password", "")
	)

	def addListener(listener: Run => Unit) = listeners += listener

	def removeListener(listener: Run => Unit) = listeners -= listener

	def recoverQueue() = {
		implicit val conn = connection

		val pendingRuns = GraderData.pendingRuns

		info("Recovering previous queue: {} runs re-added", pendingRuns.size)
	
		pendingRuns foreach grade
	}

	def grade(run: Run): GradeOutputMessage = {
		info("Judging {}", run.id)

		implicit val conn = connection

		if (run.problem.validator == Validator.Remote) {
			run.status = Status.Ready
			run.veredict = Veredict.JudgeError
			run.judged_by = Some("Grader")
			GraderData.update(run)

			new GradeOutputMessage(status = "error", error = Some("Remote validators not supported anymore"))
		} else {
			if (run.status != Status.Waiting) {
				run.status = Status.Waiting
				run.veredict = Veredict.JudgeError
				run.judged_by = None
				GraderData.update(run)
			}

			RunnerRouter.addRun(run)
			new GradeOutputMessage()
		}
	}
	
	def grade(id: Long): GradeOutputMessage = {
		implicit val conn = connection
		
		GraderData.run(id) match {
			case None => throw new IllegalArgumentException("Id " + id + " not found")
			case Some(run) => grade(run)
		}
	}

	def updateVeredict(run: Run): Run = {
		implicit val conn = connection
	
		GraderData.update(run)
		if (run.status == Status.Ready) {
			info("Veredict update: {} {} {} {} {} {} {}",
				run.id, run.status, run.veredict, run.score, run.contest_score, run.runtime, run.memory)
			try {
				info("Scoreboard update {}",
					run.contest match {
						case Some(contest) => Https.get(Config.get("grader.scoreboard_refresh.url", "https://localhost/refresh_scoreboard.php?id=") + contest.id)
						case None => "no contest"
					}
				)
			} catch {
				case e: Exception => error("Scoreboard update", e)
			}

			Broadcaster.update(run)
			listeners foreach { listener => listener(run) }
		}

		run
	}
	
	def init(configPath: String) = {
		import omegaup.data._

		// shall we create an embedded runner?
		if(Config.get("grader.embedded_runner.enable", false)) {
			RunnerRouter.addRunner(new omegaup.runner.Runner("#embedded-runner", Minijail))
		}

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			override def handle(
				target: String,
				baseRequest: Request,
				request: HttpServletRequest,
				response: HttpServletResponse
			): Unit = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				response.setContentType("text/json")
				
				Serialization.write(request.getPathInfo() match {
					case "/reload-config/" => {
						try {
							val req = Serialization.read[ReloadConfigInputMessage](request.getReader())
							val embeddedRunner = Config.get("grader.embedded_runner.enable", false)
							Config.load(configPath)

							req.overrides match {
								case Some(x) => {
									info("Configuration reloaded {}", x)
									x.foreach { case (k, v) => Config.set(k, v) }
								}
								case None => info("Configuration reloaded")
							}

							Logging.init()

							if (Config.get("grader.embedded_runner.enable", false) && !embeddedRunner) {
								RunnerRouter.addRunner(new omegaup.runner.Runner("#embedded-runner", Minijail))
							}

							response.setStatus(HttpServletResponse.SC_OK)
							new ReloadConfigOutputMessage()
						} catch {
							case e: Exception => {
								error("Reload config: {}", e)
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new ReloadConfigOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case "/status/" => {
						response.setStatus(HttpServletResponse.SC_OK)
						new StatusOutputMessage(
							embedded_runner = Config.get("grader.embedded_runner.enable", false),
							queues = RunnerRouter.status
						)
					}
					case "/grade/" => {
						try {
							val req = Serialization.read[GradeInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Manager.grade(req.id)
						} catch {
							case e: IllegalArgumentException => {
								error("Grade failed: {}", e)
								response.setStatus(HttpServletResponse.SC_NOT_FOUND)
								new GradeOutputMessage(status = "error", error = Some(e.getMessage))
							}
							case e: Exception => {
								error("Grade failed: {}", e)
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new GradeOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case "/register/" => {
						try {
							val req = Serialization.read[RegisterInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							RunnerRouter.register(req.name, request.getRemoteAddr, req.port)
						} catch {
							case e: Exception => {
								error("Register failed: {}", e)
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new RegisterOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case "/deregister/" => {
						try {
							val req = Serialization.read[RegisterInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							RunnerRouter.deregister(request.getRemoteAddr, req.port)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new RegisterOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case _ => {
						response.setStatus(HttpServletResponse.SC_NOT_FOUND)
						new NullMessage()
					}
				}, response.getWriter())
				
				baseRequest.setHandled(true)
			}
		};

		// boilerplate code for jetty with https support	
		val server = new org.eclipse.jetty.server.Server()
	
		val sslContext = new org.eclipse.jetty.util.ssl.SslContextFactory(
			Config.get("grader.keystore", "omegaup.jks"))
		sslContext.setKeyManagerPassword(Config.get("grader.password", "omegaup"))
		sslContext.setKeyStorePassword(Config.get("grader.keystore.password", "omegaup"))
		sslContext.setTrustStore(Config.get("grader.truststore", "omegaup.jks"))
		sslContext.setTrustStorePassword(Config.get("grader.truststore.password", "omegaup"))
		sslContext.setNeedClientAuth(true)
	
		val graderConnector = new org.eclipse.jetty.server.ssl.SslSelectChannelConnector(sslContext)
		graderConnector.setPort(Config.get("grader.port", 21680))
				
		server.setConnectors(List(graderConnector).toArray)
		
		server.setHandler(handler)
		server.start()

		info("Omegaup started")

		Manager.recoverQueue

		server
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty(
			"javax.net.ssl.keyStore",
			Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty(
			"javax.net.ssl.trustStore",
			Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty(
			"javax.net.ssl.keyStorePassword",
			Config.get("grader.keystore.password", "omegaup"))
		System.setProperty(
			"javax.net.ssl.trustStorePassword",
			Config.get("grader.truststore.password", "omegaup"))
		
		// Parse command-line options.
		var configPath = "omegaup.conf"
		var i = 0
		while (i < args.length) {
			if (args(i) == "--config" && i + 1 < args.length) {
				i += 1
				configPath = args(i)
				Config.load(configPath)
			} else if (args(i) == "--output" && i + 1 < args.length) {
				i += 1
				System.setOut(new java.io.PrintStream(new java.io.FileOutputStream(args(i))))
			}
			i += 1
		}

		// logger
		Logging.init()
		
		val server = init(configPath)

		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")
				server.stop()
			}
		});
		
		server.join()
	}
}
