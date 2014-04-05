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
							RunnerRouter.register(req.hostname, req.port)
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
							RunnerRouter.deregister(req.hostname, req.port)
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

		info("Omegaup started")

		Manager.recoverQueue

		new ServiceInterface {
			override def stop(): Unit = {
				server.stop
				RunnerRouter.stop
			}
			override def join(): Unit = {
				server.join
				RunnerRouter.join
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
