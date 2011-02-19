package omegaup.grader

import java.io._
import java.util.logging._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty._
import org.mortbay.jetty.handler._
import net.liftweb.json._
import omegaup._
import Estado._
import Lenguaje._
import Veredicto._
import Validador._
import Servidor._

case class Submission(ejecucion: Ejecucion)
case object Login

object Manager extends Object with Log {
	private var runnerQueue = new java.util.concurrent.LinkedBlockingQueue[(String, Int)]()
	// Loading SQL connector driver
	Class.forName("com.mysql.jdbc.Driver")
	val connection = java.sql.DriverManager.getConnection(
		Config.get("db.url", "jdbc:mysql://localhost/omegaup"),
		Config.get("db.user", "omegaup"),
		Config.get("db.password", "")
	)
	
	def grade(id: Long): GradeOutputMessage = {
		info("Judging {}", id)
		
		implicit val conn = connection
		
		GraderData.ejecucion(id) match {
			case None => throw new IllegalArgumentException("Id " + id + " not found")
			case Some(ejecucion) => {
				if (ejecucion.problema.validador == Validador.Remoto) {
					ejecucion.problema.servidor match {
						case Some(Servidor.UVa) => drivers.UVa
						case Some(Servidor.LiveArchive) => drivers.LiveArchive
						case Some(Servidor.TJU) => drivers.TJU
						case _ => null
					}
				} else {
					drivers.OmegaUp
				} ! Submission(ejecucion)
				
				new GradeOutputMessage()
			}
		}
	}
	
	def getRunner(): (String, Int) = {
		runnerQueue.take()
	}
	
	def addRunner(host: String, port: Int) = {
		runnerQueue.put((host, port))
	}
	
	def register(host: String, port: Int): RegisterOutputMessage = {
		info("Registering {}:{}", host, port)
	
		addRunner(host, port)	
		new RegisterOutputMessage()
	}
	
	def deregister(host: String, port: Int): RegisterOutputMessage = {
		info("De-registering {}:{}", host, port)
		
		runnerQueue.remove((host, port))
		new RegisterOutputMessage()
	}
	
	def updateVeredict(ej: Ejecucion): Ejecucion = {
		info("Veredict update: {} {} {} {} {} {} {}", ej.id, ej.estado, ej.veredicto, ej.puntuacion, ej.puntuacion_concurso, ej.tiempo, ej.memoria)
		
		implicit val conn = connection
		
		GraderData.update(ej)
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))
		
		// logger
		if(Config.get("grader.logging.file", "") != "") {
			Logger.getLogger("").addHandler(new FileHandler(Config.get("grader.logfile", "")))
		}
		Logger.getLogger("").setLevel(
			Config.get("grader.logging.level", "info") match {
				case "all" => Level.ALL
				case "finest" => Level.FINEST
				case "finer" => Level.FINER
				case "fine" => Level.FINE
				case "config" => Level.CONFIG
				case "info" => Level.INFO
				case "warning" => Level.WARNING
				case "severe" => Level.SEVERE
				case "off" => Level.OFF
			}
		)
		Logger.getLogger("").getHandlers.foreach { _.setFormatter(LogFormatter) }

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
		val server = new Server()
		
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
		
		java.lang.System.in.read()
		
		server.stop()
		server.join()
	}
}
