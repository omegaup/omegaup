package omegaup.runner

import java.io._
import java.util.zip._
import javax.servlet._
import javax.servlet.http._
import org.mortbay.jetty._
import org.mortbay.jetty.handler._
import net.liftweb.json._
import omegaup._

object Runner extends Object with Log {
	def compile(lang: String, code: List[String], master_lang: Option[String], master_code: Option[List[String]]): CompileOutputMessage = {
		val compileDirectory = new File(Config.get("runner.directory", ".") + "/compile/")
		compileDirectory.mkdirs
		
		var runDirectory = File.createTempFile(compileDirectory.getCanonicalPath, System.nanoTime.toString)
		runDirectory.delete
		
		runDirectory = new File(runDirectory.getCanonicalPath + "." + lang)
		runDirectory.mkdirs
		
		var fileWriter = new FileWriter(runDirectory.getCanonicalPath + "/Main." + lang)
		fileWriter.write(code(0), 0, code(0).length)
		fileWriter.close
		
		for (i <- 1 until code.length) {
			fileWriter = new FileWriter(runDirectory.getCanonicalPath + "/f" + i + "." + lang)
			fileWriter.write(code(i), 0, code(i).length)
			fileWriter.close
		}
		
		new CompileOutputMessage()
	}
	
	def input(request: HttpServletRequest): InputOutputMessage = {
		if(request.getContentType() != "application/zip" || request.getHeader("Content-Disposition") == null) {
			new InputOutputMessage(
				status = "error",
				error = Some("Content-Type must be \"application/zip\", Content-Disposition must be \"attachment\" and a filename must be specified")
			)
		} else {
			val ContentDispositionRegex = "attachment; filename=([a-zA-Z0-9_-][a-zA-Z0-9_.-]*);.*".r
			
			val ContentDispositionRegex(inputName) = request.getHeader("Content-Disposition")
			
			val inputDirectory = new File(Config.get("runner.directory", ".") + "/input/" + inputName)
			inputDirectory.mkdirs()
			
			val input = new ZipInputStream(request.getInputStream)
			var entry: ZipEntry = input.getNextEntry
			val buffer = Array.ofDim[Byte](1024)
			var read: Int = 0
			var reading = true
			
			while(entry != null) {
				val outFile = new File(entry.getName())
				val output = new FileOutputStream(inputDirectory.getCanonicalPath + "/" + outFile.getName)

				while(reading) {
					read = input.read(buffer)
					if (read == -1) reading = false
					else output.write(buffer, 0, read)
				}

				output.close
				input.closeEntry
				entry = input.getNextEntry
			}
			
			input.close
			
			new InputOutputMessage()
		}
	}
	
	def main(args: Array[String]) = {
		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("runner.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("runner.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("runner.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("runner.truststore.password", "omegaup"))

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
							Runner.compile(req.lang, req.code, req.master_lang, req.master_code)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new CompileOutputMessage(status = "error", error = Some(e.getMessage))
							}
						}
					}
					case "/input/" => {
						try {
							response.setStatus(HttpServletResponse.SC_OK)
							Runner.input(request)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								new InputOutputMessage(status = "error", error = Some(e.getMessage))
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
		runnerConnector.setKeystore(Config.get("runner.keystore", "omegaup.jks"))
		runnerConnector.setPassword(Config.get("runner.password", "omegaup"))
		runnerConnector.setKeyPassword(Config.get("runner.keystore.password", "omegaup"))
		runnerConnector.setTruststore(Config.get("runner.truststore", "omegaup.jks"))
		runnerConnector.setTrustPassword(Config.get("runner.truststore.password", "omegaup"))
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
