package omegaup.grader

import java.io._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty.Request
import org.mortbay.jetty.handler._
import net.liftweb.json._
import omegaup._
import omegaup.data._
import omegaup.runner._
import Status._
import Language._
import Veredict._
import Validator._
import Server._

case class Submission(run: Run)
case object Login

object Manager extends Object with Log {
	private var runnerQueue = new java.util.concurrent.LinkedBlockingQueue[RunnerService]()
	// Loading SQL connector driver
	Class.forName(Config.get("db.driver", "org.h2.Driver"))
	val connection = java.sql.DriverManager.getConnection(
		Config.get("db.url", "jdbc:h2:file:omegaup"),
		Config.get("db.user", "omegaup"),
		Config.get("db.password", "")
	)
	
	def grade(id: Long): GradeOutputMessage = {
		info("Judging {}", id)
		
		implicit val conn = connection
		
		GraderData.run(id) match {
			case None => throw new IllegalArgumentException("Id " + id + " not found")
			case Some(run) => {
				if (run.problem.validator == Validator.Remote) {
					run.problem.server match {
						case Some(Server.UVa) => drivers.UVa
						case Some(Server.LiveArchive) => drivers.LiveArchive
						case Some(Server.TJU) => drivers.TJU
						case _ => null
					}
				} else {
					drivers.OmegaUp
				} ! Submission(run)
				
				new GradeOutputMessage()
			}
		}
	}
	
	def getRunner(): RunnerService = {
		runnerQueue.take()
	}
	
	def addRunner(service: RunnerService) = {
		runnerQueue.put(service)
	}
	
	def register(host: String, port: Int): RegisterOutputMessage = {
		info("Registering {}:{}", host, port)
	
		addRunner(new RunnerProxy(host, port))
		new RegisterOutputMessage()
	}
	
	def deregister(host: String, port: Int): RegisterOutputMessage = {
		info("De-registering {}:{}", host, port)
		
		runnerQueue.remove((host, port))
		new RegisterOutputMessage()
	}
	
	def updateVeredict(run: Run): Run = {
		info("Veredict update: {} {} {} {} {} {} {}", run.id, run.status, run.veredict, run.score, run.contest_score, run.runtime, run.memory)
		
		implicit val conn = connection
		
		GraderData.update(run)
	}
	
	def main(args: Array[String]) = {
		import omegaup.data._

		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))
		
		// logger
		Logging.init()
	
		// shall we create an embedded runner?
		if(Config.get("grader.embedded_runner.enable", false)) {
			Manager.addRunner(omegaup.runner.Runner)
		}

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			def handle(target: String, request: HttpServletRequest, response: HttpServletResponse, dispatch: Int) = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				response.setContentType("text/json")
				
				Serialization.write(request.getPathInfo() match {
					case "/grade/" => {
						try {
							val req = Serialization.read[GradeInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Manager.grade(req.id)
						} catch {
							case e: IllegalArgumentException => {
								response.setStatus(HttpServletResponse.SC_NOT_FOUND)
								new GradeOutputMessage(status = "error", error = Some(e.getMessage))
							}
							case e: Exception => {
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
				
				request.asInstanceOf[Request].setHandled(true)
			}
		};

		// boilerplate code for jetty with https support	
		val server = new org.mortbay.jetty.Server()
		
		val runnerConnector = new org.mortbay.jetty.security.SslSelectChannelConnector
		runnerConnector.setPort(Config.get[Int]("grader.port", 21680))
		runnerConnector.setKeystore(Config.get[String]("grader.keystore", "omegaup.jks"))
		runnerConnector.setPassword(Config.get[String]("grader.password", "omegaup"))
		runnerConnector.setKeyPassword(Config.get[String]("grader.keystore.password", "omegaup"))
		runnerConnector.setTruststore(Config.get[String]("grader.truststore", "omegaup.jks"))
		runnerConnector.setTrustPassword(Config.get[String]("grader.truststore.password", "omegaup"))
		runnerConnector.setNeedClientAuth(true)
		
		server.setConnectors(List(runnerConnector).toArray)
		
		server.setHandler(handler)
		server.start()

		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				server.stop()
			}
		});
		
		server.join()
	}
}
