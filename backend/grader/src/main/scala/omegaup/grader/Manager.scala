package omegaup.grader

import java.io._
import javax.servlet._
import javax.servlet.http._
import org.eclipse.jetty.server.Request
import org.eclipse.jetty.server.handler._
import net.liftweb.json._
import omegaup._
import omegaup.data._
import omegaup.runner._
import omegaup.broadcaster.Broadcaster
import Status._
import Veredict._
import Validator._
import Server._

object EventCategory extends Enumeration {
	type EventCategory = Value
	val Task = Value(0)
	val Queue = Value(1)
	val UpdateVeredict = Value(2)
	val Runner = Value(3)
	val Compile = Value(4)
	val Input = Value(5)
	val Run = Value(6)
	val Grade = Value(7)
	val BroadcastQueue = Value(8)
	val GraderRefresh = Value(9)
	val Requeue = Value(10)
}
import EventCategory.EventCategory

case class Event(begin: Boolean, category: EventCategory, args: (String, String)*) {
	val time: Long = System.nanoTime
	override def toString(): String = {
		val argString = if (args.length == 0) {
			""
		} else {
			""","args":{""" + args.map(arg => s""""${arg._1}":"${arg._2}"""").mkString(",") + "}"
		}
		s"""{"name":"$category","tid":0,"pid":0,"ts":${time / 1000},"ph":"${if (begin) 'B' else 'E'}"$argString}"""
	}
}

case class FlowEvent(begin: Boolean, id: Long, category: EventCategory) {
	val time: Long = System.nanoTime
	override def toString(): String = {
		s"""{"name":"$category","tid":0,"pid":0,"id":0x${id.toHexString},"ts":${time / 1000},"ph":"${if (begin) 's' else 'f'}"}"""
	}
}

case class CompleteEvent(category: EventCategory, time: Long, duration: Long, args: Seq[(String, String)]) {
	override def toString(): String = {
		val argString = if (args.length == 0) {
			""
		} else {
			""","args":{""" + args.map(arg => s""""${arg._1}":"${arg._2}"""").mkString(",") + "}"
		}
		s"""{"name":"$category","tid":0,"pid":0,"ts":${time / 1000},"dur":${duration / 1000},"ph":"X"$argString}"""
	}
}

class RunContext(var run: Run, val debug: Boolean) extends Object with Log {
	val rejudges: Int = 0
	val eventList = new scala.collection.mutable.MutableList[AnyRef]
	var service: RunnerService = null
	var flightTime: Long = 0
	private var hasBeenDequeued: Boolean = false
	eventList += new Event(true, EventCategory.Task)

	def startFlight(service: RunnerService) = {
		this.service = service
		this.flightTime = System.currentTimeMillis
	}

	def trace[T](category: EventCategory, args: (String, String)*)(f: => T): T = {
		val t0 = System.nanoTime
		try {
			f
		} finally {
			val t1 = System.nanoTime
			eventList += new CompleteEvent(category, t0, t1 - t0, args)
		}
	}

	def queued(): Unit = {
		// If this event has been previously dequeued, log whatever we had previously and start anew
		if (hasBeenDequeued) {
			val flowId = (run.id.toLong << 32) | System.currentTimeMillis
			eventList += new FlowEvent(true, flowId, EventCategory.Requeue)
			finish
			eventList.clear
			eventList += new FlowEvent(false, flowId, EventCategory.Requeue)
			eventList += new Event(true, EventCategory.Task, "requeued" -> "true")
		}
		eventList += new Event(true, EventCategory.Queue)
	}
	def dequeued(name: String): Unit = {
		eventList += new Event(false, EventCategory.Queue, "queue" -> name)
		hasBeenDequeued = true
	}

	def broadcastQueued(): Unit = eventList += new Event(true, EventCategory.BroadcastQueue)
	def broadcastDequeued(): Unit = eventList += new Event(false, EventCategory.BroadcastQueue)

	def finish() = {
		eventList += new Event(false, EventCategory.Task, "run_id" -> run.id.toString)
		info("[" + eventList.map(_.toString).mkString(",") + "]")
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
	val runnerRouter = RoutingDescription.parse(
		try {
			FileUtil.read(Config.get("grader.routing.file", "routing.conf"))
		} catch {
			case e: FileNotFoundException => ""
		}
	) match {
		case (dispatcherNames, runRouter) => new RunnerRouter(dispatcherNames, runRouter)
	}

	def addListener(listener: Run => Unit) = listeners += listener

	def removeListener(listener: Run => Unit) = listeners -= listener

	def recoverQueue() = {
		implicit val conn = connection

		val pendingRuns = GraderData.pendingRuns

		info("Recovering previous queue: {} runs re-added", pendingRuns.size)
	
		pendingRuns foreach(run => grade(new RunContext(run, false)))
	}

	def grade(ctx: RunContext): GradeOutputMessage = {
		info("Judging {}", ctx.run.id)

		implicit val conn = connection

		if (ctx.run.problem.validator == Validator.Remote) {
			ctx.run.status = Status.Ready
			ctx.run.veredict = Veredict.JudgeError
			ctx.run.judged_by = Some("Grader")
			GraderData.update(ctx.run)

			new GradeOutputMessage(status = "error", error = Some("Remote validators not supported anymore"))
		} else {
			if (ctx.run.status != Status.Waiting) {
				ctx.run.status = Status.Waiting
				ctx.run.veredict = Veredict.JudgeError
				ctx.run.judged_by = None
				ctx.trace(EventCategory.UpdateVeredict) {
					GraderData.update(ctx.run)
				}
			}

			runnerRouter.addRun(ctx)
			new GradeOutputMessage()
		}
	}

	def grade(id: Long, debug: Boolean): GradeOutputMessage = {
		implicit val conn = connection
		
		GraderData.run(id) match {
			case None => throw new IllegalArgumentException("Id " + id + " not found")
			case Some(run) => grade(new RunContext(run, debug))
		}
	}

	def updateVeredict(ctx: RunContext, run: Run): Run = {
		implicit val conn = connection
	
		ctx.trace(EventCategory.UpdateVeredict) {
			GraderData.update(run)
		}
		if (run.status == Status.Ready) {
			info("Veredict update: {} {} {} {} {} {} {}",
				run.id, run.status, run.veredict, run.score, run.contest_score, run.runtime, run.memory)
			Broadcaster.update(ctx)
			listeners foreach { listener => listener(run) }
		}

		run
	}
	
	def init(configPath: String) = {
		import omegaup.data._

		// shall we create an embedded runner?
		if(Config.get("grader.embedded_runner.enable", false)) {
			runnerRouter.addRunner(new omegaup.runner.Runner("#embedded-runner", Minijail))
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
								runnerRouter.addRunner(new omegaup.runner.Runner("#embedded-runner", Minijail))
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
							queues = runnerRouter.status
						)
					}
					case "/grade/" => {
						try {
							val req = Serialization.read[GradeInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Manager.grade(req.id, req.debug)
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
							runnerRouter.register(req.hostname, req.port)
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
							runnerRouter.deregister(req.hostname, req.port)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new RegisterOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case "/broadcast/" => {
						try {
							val req = Serialization.read[BroadcastInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Broadcaster.broadcast(
								req.contest,
								req.message,
								req.broadcast,
								req.targetUser,
								req.userOnly
							)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								error("Broadcast failed: {}", e)
								new BroadcastOutputMessage(status = "error", error = Some(e.getMessage))
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
			Config.get("ssl.keystore", "omegaup.jks"))
		sslContext.setKeyManagerPassword(Config.get("ssl.password", "omegaup"))
		sslContext.setKeyStorePassword(Config.get("ssl.keystore.password", "omegaup"))
		sslContext.setTrustStore(FileUtil.loadKeyStore(
			Config.get("ssl.truststore", "omegaup.jks"),
			Config.get("ssl.truststore.password", "omegaup")
		))
		sslContext.setNeedClientAuth(true)
	
		val graderConnector = new org.eclipse.jetty.server.ServerConnector(server, sslContext)
		graderConnector.setPort(Config.get("grader.port", 21680))
				
		server.setConnectors(List(graderConnector).toArray)
		
		server.setHandler(handler)
		server.start()

		info("omegaUp manager started")

		Manager.recoverQueue

		new ServiceInterface {
			override def stop(): Unit = {
				info("omegaUp manager stopping")
				server.stop
				runnerRouter.stop
			}
			override def join(): Unit = {
				server.join
				runnerRouter.join
				info("omegaUp manager stopped")
			}
		}
	}
	
	def main(args: Array[String]) = {
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

/* vim: set noexpandtab: */
