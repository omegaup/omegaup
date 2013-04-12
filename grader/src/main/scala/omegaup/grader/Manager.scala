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
import Language._
import Veredict._
import Validator._
import Server._

class RunnerEndpoint(val host: String, val port: Int) {
	def ==(o: RunnerEndpoint) = host == o.host && port == o.port
	override def hashCode() = 28227 + 97 * host.hashCode + port
	override def equals(other: Any) = other match {
		case x:RunnerEndpoint => host == x.host && port == x.port
		case _ => false
	}
}

object Manager extends Object with Log {
	private val registeredEndpoints = scala.collection.mutable.HashSet.empty[RunnerEndpoint]
	private val runnerQueue = new java.util.concurrent.LinkedBlockingQueue[RunnerService]()

	// Loading SQL connector driver
	Class.forName(Config.get("db.driver", "org.h2.Driver"))
	val connection = java.sql.DriverManager.getConnection(
		Config.get("db.url", "jdbc:h2:file:omegaup"),
		Config.get("db.user", "omegaup"),
		Config.get("db.password", "")
	)
	
	def grade(id: Long): GradeOutputMessage = {
		info("Judging {}", id)
		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)
	
		implicit val conn = connection
		
		GraderData.run(id) match {
			case None => throw new IllegalArgumentException("Id " + id + " not found")
			case Some(run) => {
				run.status = Status.Compiling
				run.veredict = Veredict.JudgeError

				GraderData.update(run)

				val driver = if (run.problem.validator == Validator.Remote) {
					run.problem.server match {
						case Some(Server.UVa) => drivers.UVa
						case Some(Server.LiveArchive) => drivers.LiveArchive
						case Some(Server.TJU) => drivers.TJU
						case _ => null
					}
				} else {
					drivers.OmegaUp
				}
			
				info("Using driver {}", driver)

				driver ! drivers.Submission(run)
				
				new GradeOutputMessage()
			}
		}
	}
	
	def getRunner(): RunnerService = {
		var r: RunnerService = null

		while (r == null) {
			info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)
			r = runnerQueue.take()
			if (!Config.get("grader.embedded_runner.enable", false) && r == omegaup.runner.Runner) {
				// don't put this runner back in the queue.
				r = null
			}
		}

		r
	}
	
	def addRunner(service: RunnerService) = {
		runnerQueue.put(service)
		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)
	}
	
	def register(host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(host, port)
	
		synchronized (registeredEndpoints) {
			if (!registeredEndpoints.contains(endpoint)) {
				info("Registering {}:{}", endpoint.host, endpoint.port)
				registeredEndpoints += endpoint
				addRunner(new RunnerProxy(endpoint.host, endpoint.port))
			}
			endpoint
		}

		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)
				
		new RegisterOutputMessage()
	}
	
	def deregister(host: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(host, port)
		
		synchronized (registeredEndpoints) {
			if (registeredEndpoints.contains(endpoint)) {
				info("De-registering {}:{}", endpoint.host, endpoint.port)
				registeredEndpoints -= endpoint
				runnerQueue.remove(new RunnerProxy(endpoint.host, endpoint.port))
			}
			endpoint
		}

		info("Runner queue length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)
		
		new RegisterOutputMessage()
	}
	
	def updateVeredict(run: Run): Run = {
		info("Veredict update: {} {} {} {} {} {} {}", run.id, run.status, run.veredict, run.score, run.contest_score, run.runtime, run.memory)
		
		implicit val conn = connection
		
		Broadcaster.update(run)
		GraderData.update(run)
	}
	
	def init() = {
		import omegaup.data._
		
		// shall we create an embedded runner?
		if(Config.get("grader.embedded_runner.enable", false)) {
			Manager.addRunner(omegaup.runner.Runner)
		}

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			override def handle(target: String, baseRequest: Request, request: HttpServletRequest, response: HttpServletResponse): Unit = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				response.setContentType("text/json")
				
				Serialization.write(request.getPathInfo() match {
					case "/reload-config/" => {
						try {
							val req = Serialization.read[ReloadConfigInputMessage](request.getReader())
							val embeddedRunner = Config.get("grader.embedded_runner.enable", false)
							Config.load()

							req.overrides match {
								case Some(x) => {
									info("Configuration reloaded {}", x)
									x.foreach { case (k, v) => Config.set(k, v) }
								}
								case None => info("Configuration reloaded")
							}	

							if (Config.get("grader.embedded_runner.enable", false) && !embeddedRunner) {
								Manager.addRunner(omegaup.runner.Runner)
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
							Manager.register(request.getRemoteAddr, req.port)
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
							Manager.deregister(request.getRemoteAddr, req.port)
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

		// start the drivers
		drivers.OmegaUp.start
		//drivers.UVa.start
		//drivers.TJU.start
		//drivers.LiveArchive.start

		//drivers.UVa ! drivers.Login

		// boilerplate code for jetty with https support	
		val server = new org.eclipse.jetty.server.Server()
	
		val sslContext = new org.eclipse.jetty.util.ssl.SslContextFactory(Config.get[String]("grader.keystore", "omegaup.jks"))
		sslContext.setKeyManagerPassword(Config.get[String]("grader.password", "omegaup"))
		sslContext.setKeyStorePassword(Config.get[String]("grader.keystore.password", "omegaup"))
		sslContext.setTrustStore(Config.get[String]("grader.truststore", "omegaup.jks"))
		sslContext.setTrustStorePassword(Config.get[String]("grader.truststore.password", "omegaup"))
		sslContext.setNeedClientAuth(true)
	
		val graderConnector = new org.eclipse.jetty.server.ssl.SslSelectChannelConnector(sslContext)
		graderConnector.setPort(Config.get[Int]("grader.port", 21680))
				
		server.setConnectors(List(graderConnector).toArray)
		
		server.setHandler(handler)
		server.start()

		info("Omegaup started")
		
		server
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))
		
		// logger
		Logging.init()
		
		val server = init()

		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")
				server.stop()
			}
		});
		
		server.join()
	}
}
