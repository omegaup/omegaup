package openjuan.grader

import java.io._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty._
import org.mortbay.jetty.handler._
import org.squeryl._
import org.squeryl.PrimitiveTypeMode._
import net.liftweb.json._
import openjuan._

object Grader extends Object with Log {
	def grade(id: Int): GradeOutputMessage = {
		println("Judging " + id)
		
		from(GraderData.ejecuciones)(e => where(e.id === id) select(e)).map{println(_)}
		
		new GradeOutputMessage()
	}
	
	def register(host: String, port: Int): RegisterOutputMessage = {
		new RegisterOutputMessage()
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("grader.keystore", "openjuan.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("grader.truststore", "openjuan.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("grader.keystore.password", "openjuan"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("grader.truststore.password", "openjuan"))
		
		// Loading SQL connector driver
		Class.forName("com.mysql.jdbc.Driver")
		SessionFactory.concreteFactory = Some(()=>
			Session.create(
				java.sql.DriverManager.getConnection(Config.get("db.url", "jdbc:mysql://localhost/openjuan"), Config.get("db.user", "openjuan"), Config.get("db.passwd", "")),
				new org.squeryl.adapters.MySQLAdapter))

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			def handle(target: String, request: HttpServletRequest, response: HttpServletResponse, dispatch: Int) = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				response.setContentType("text/json")
				
				Serialization.write(request.getPathInfo() match {
					case "/grader/" => {
						try {
							val req = Serialization.read[GradeInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Grader.grade(req.id)
						} catch {
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
							Grader.register(request.getRemoteAddr, req.port)
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
		runnerConnector.setKeystore(Config.get[String]("grader.keystore", "openjuan.jks"))
		runnerConnector.setPassword(Config.get[String]("grader.password", "openjuan"))
		runnerConnector.setKeyPassword(Config.get[String]("grader.keystore.password", "openjuan"))
		runnerConnector.setTruststore(Config.get[String]("grader.truststore", "openjuan.jks"))
		runnerConnector.setTrustPassword(Config.get[String]("grader.truststore.password", "openjuan"))
		runnerConnector.setNeedClientAuth(true)
		
		server.setConnectors(List(runnerConnector).toArray)
		
		server.setHandler(handler)
		server.start()
		
		java.lang.System.in.read()
		
		server.stop()
		server.join()
	}
}
