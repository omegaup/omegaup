package openjuan.runner

import java.io._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty._
import org.mortbay.jetty.handler._
import net.liftweb.json._
import openjuan._

object Runner extends Object with Log {
	def compile(lang: String, code: List[String]): CompileOutputMessage = {
		new CompileOutputMessage()
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("runner.keystore", "openjuan.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("runner.truststore", "openjuan.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("runner.keystore.password", "openjuan"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("runner.truststore.password", "openjuan"))

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			def handle(target: String, request: HttpServletRequest, response: HttpServletResponse, dispatch: Int) = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				response.setContentType("text/json")
				
				Serialization.write(request.getPathInfo() match {
					case "/compile/" => {
						try {
							val req = Serialization.read[CompileInputMessage](request.getReader())
							response.setStatus(HttpServletResponse.SC_OK)
							Runner.compile(req.lang, req.code)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new CompileOutputMessage(status = "error", error = Some(e.getMessage))
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
		runnerConnector.setPort(Config.get("runner.port", 0))
		runnerConnector.setKeystore(Config.get("runner.keystore", "openjuan.jks"))
		runnerConnector.setPassword(Config.get("runner.password", "openjuan"))
		runnerConnector.setKeyPassword(Config.get("runner.keystore.password", "openjuan"))
		runnerConnector.setTruststore(Config.get("runner.truststore", "openjuan.jks"))
		runnerConnector.setTrustPassword(Config.get("runner.truststore.password", "openjuan"))
		runnerConnector.setNeedClientAuth(true)
		
		server.setConnectors(List(runnerConnector).toArray)
		
		server.setHandler(handler)
		server.start()
		
		info("Registering port {}", runnerConnector.getLocalPort())
		
		Https.send[RegisterInputMessage, RegisterOutputMessage](
			Config.get("grader.register.url", "https://localhost:21680/register/"),
			new RegisterInputMessage(runnerConnector.getLocalPort())
		)
		
		java.lang.System.in.read()
		
		Https.send[RegisterInputMessage, RegisterOutputMessage](
			Config.get("grader.deregister.url", "https://localhost:21680/deregister/"),
			new RegisterInputMessage(runnerConnector.getLocalPort())
		)
		
		server.stop()
		server.join()
	}
}
