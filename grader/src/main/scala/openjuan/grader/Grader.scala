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

object Grader {
	def grade(id: Int): GraderOutputMessage = {
		println("Judging " + id)
		
		from(GraderData.ejecuciones)(e => where(e.id === id) select(e)).map{println(_)}
		
		new GraderOutputMessage()
	}
	
	def main(args: Array[String]) = {
		
		Class.forName("com.mysql.jdbc.Driver")
		SessionFactory.concreteFactory = Some(()=>
			Session.create(
				java.sql.DriverManager.getConnection(Config.get("db.url", "jdbc:mysql://localhost/openjuan"), Config.get("db.user", "openjuan"), Config.get("db.passwd", "")),
				new org.squeryl.adapters.MySQLAdapter))

		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			def handle(target: String, request: HttpServletRequest, response: HttpServletResponse, dispatch: Int) = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				request.getPathInfo() match {
					case "/grader/" => {
						response.setContentType("text/json")
						
						Serialization.write[GraderOutputMessage, PrintWriter](
							try {
								val req = Serialization.read[GraderInputMessage](request.getReader())
								response.setStatus(HttpServletResponse.SC_OK)
								Grader.grade(req.id)
							} catch {
								case e: Exception => {
									response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
									new GraderOutputMessage(status = "error", error = Some(e.getMessage))
								}
							},
							response.getWriter()
						)
					}
					case _ => {
						response.setStatus(HttpServletResponse.SC_NOT_FOUND)
					}
				}
				
				request.asInstanceOf[Request].setHandled(true)
			}
		};

		val server = new Server(Config.get[Int]("grader.port", 21680))
		server.setHandler(handler)
		server.start()
	}
}
