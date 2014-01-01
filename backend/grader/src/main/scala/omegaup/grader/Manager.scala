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

	def status() =
		StatusOutputMessage(
			embedded_runner = false,
			queues = dispatchers.toMap.map(entry => {entry._1 -> entry._2.status()})
		)

	def register(hostname: String, host: String, port: Int): RegisterOutputMessage = {
		dispatchers(defaultQueueName).register(hostname, host, port)
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
	private val registeredEndpoints = scala.collection.mutable.HashMap.empty[RunnerEndpoint, Long]
	private val runnerQueue = scala.collection.mutable.Queue.empty[RunnerService]
	private val runQueue = scala.collection.mutable.Queue.empty[Run]
	private val runsInFlight = scala.collection.mutable.ListBuffer.empty[(RunnerService,Run)]
	private val executor = Executors.newCachedThreadPool
	private val lock = new Object

	def status() =
		QueueStatus(
			run_queue_length = runQueue.size,
			runner_queue_length = runnerQueue.size,
			runners = registeredEndpoints.size,
			running_runs = runsInFlight.size
		)

	def register(hostname: String, host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(host, port)

		lock.synchronized {
			if (!registeredEndpoints.contains(endpoint)) {
				info("Registering {}({}):{}", endpoint.host, hostname, endpoint.port)
				registeredEndpoints += endpoint -> 0
				addRunner(new RunnerProxy(hostname, endpoint.host, endpoint.port))
			}
			registeredEndpoints(endpoint) = System.currentTimeMillis
		}

		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	def deregister(host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(host, port)

		lock.synchronized {
			if (registeredEndpoints.contains(endpoint)) {
				info("De-registering {}:{}", endpoint.host, endpoint.port)
				registeredEndpoints -= endpoint
			}
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

	private class RunnerEndpoint(val host: String, val port: Int) {
		def ==(o: RunnerEndpoint) = host == o.host && port == o.port
		override def hashCode() = 28227 + 97 * host.hashCode + port
		override def equals(other: Any) = other match {
			case x:RunnerEndpoint => host == x.host && port == x.port
			case _ => false
		}
	}

	private class GradeTask(
		dispatcher: RunnerDispatcher,
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
							dispatcher.addRun(r)

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
				dispatcher.addRunner(runner)
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

		executor.submit(new GradeTask(this, run, runner, OmegaUpDriver))
	}

	private def addRunnerLocked(runner: RunnerService) = {
		debug("Adding runner {}", runner)
		runnerQueue += runner
		dispatchLocked
	}

	private def dispatchLocked() = {
		// Prune any runners that are not registered or haven't communicated in a while.
		debug("Before pruning the queue {}", status)
		runnerQueue.dropWhile (
			_ match {
				case proxy: omegaup.runner.RunnerProxy => {
					val endpoint = new RunnerEndpoint(proxy.host, proxy.port)
					!registeredEndpoints.contains(endpoint) || registeredEndpoints(endpoint) <
					System.currentTimeMillis - Config.get("grader.runner.queue_timeout", 10 * 60 * 1000)
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
			run.status = Status.Waiting
			run.veredict = Veredict.JudgeError
			run.judged_by = None
			GraderData.update(run)

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
			Broadcaster.update(run)
			listeners foreach { listener => listener(run) }
		}

		run
	}
	
	def init(configPath: String) = {
		import omegaup.data._

		// shall we create an embedded runner?
		if(Config.get("grader.embedded_runner.enable", false)) {
			// Choose a sandbox instance
			val sandbox = Config.get("runner.sandbox", "box") match {
				case "box" => Box
				case "minijail" => Minijail
			}
			RunnerRouter.addRunner(new omegaup.runner.Runner("embedded-runner", sandbox))
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
								// Choose a sandbox instance
								val sandbox = Config.get("runner.sandbox", "box") match {
									case "box" => Box
									case "minijail" => Minijail
								}
								RunnerRouter.addRunner(new omegaup.runner.Runner("embedded-runner", sandbox))
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
            RunnerRouter.status
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
							RunnerRouter.register(req.hostname, request.getRemoteAddr, req.port)
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
