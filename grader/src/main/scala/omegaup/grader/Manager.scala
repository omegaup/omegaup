package omegaup.grader

import java.io._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty._
import org.mortbay.jetty.handler._
import org.squeryl._
import org.squeryl.PrimitiveTypeMode._
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
	
	def grade(id: Int): GradeOutputMessage = {
		info("Judging {}", id)
		
		transaction {
			val ejecucion = GraderData.ejecuciones.where(_.id === id)
		
			if ( ejecucion.isEmpty ) {
				throw new IllegalArgumentException("Id " + id + " not found")
			} else {
				if (ejecucion.single.problema.single.validador == Validador.Remoto) {
					ejecucion.single.problema.single.servidor match {
						case Some(Servidor.UVa) => drivers.UVa
						case Some(Servidor.LiveArchive) => drivers.LiveArchive
						case Some(Servidor.TJU) => drivers.TJU
						case _ => null
					}
				} else {
					drivers.OmegaUp
				} ! Submission(ejecucion.single)
				
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
	
	def updateVeredict(id: Long, e: Estado, v: Option[Veredicto], points: Double, runtime: Double, memory: Long, compileError: Option[String] = None) = {
		info("Veredict update: {} {} {} {} {} {} {}", id, e, v, points, runtime, memory, compileError)
		
		transaction {
			if(e == Estado.Listo) {
				update(GraderData.ejecuciones) (ej => 
					where(ej.id === id)
					set(
						ej.estado    := e,
						ej.veredicto := v.get,
						ej.puntuacion:= points,
						ej.tiempo    := math.round(100 * runtime),
						ej.memoria   := memory
					)
				)
			} else {
				update(GraderData.ejecuciones) (ej => 
					where(ej.id === id)
					set(
						ej.estado    := e,
						ej.puntuacion:= points,
						ej.tiempo    := math.round(100 * runtime),
						ej.memoria   := memory
					)
				)
			}
		}
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "omegaup"))
		
		// Loading SQL connector driver
		Class.forName("com.mysql.jdbc.Driver")
		SessionFactory.concreteFactory = Some(()=>
			Session.create(
				java.sql.DriverManager.getConnection(
					Config.get("db.url", "jdbc:mysql://localhost/omegaup"),
					Config.get("db.user", "omegaup"),
					Config.get("db.password", "")
				),
				new org.squeryl.adapters.MySQLAdapter
			)
		)

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
