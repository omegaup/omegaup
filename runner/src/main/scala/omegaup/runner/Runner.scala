package omegaup.runner

import java.io._
import java.util.zip._
import javax.servlet._
import javax.servlet.http._
import org.eclipse.jetty.server.Request
import org.eclipse.jetty.server.handler._
import net.liftweb.json._
import scala.collection.{mutable,immutable}
import omegaup._
import omegaup.data._

object Runner extends RunnerService with Log with Using {
	def compile(runDirectory: File, lang: String, codes: Map[String, String], error_string: String): CompileOutputMessage = {
		runDirectory.mkdirs
		
		val inputFiles = new mutable.ListBuffer[String]
		
		for ((name, code) <- codes) {
			if (name.contains("/")) {
				return new CompileOutputMessage(error_string, error=Some("invalid filenames"))
			}
			inputFiles += runDirectory.getCanonicalPath + "/" + name
			using (new FileWriter(runDirectory.getCanonicalPath + "/" + name)) { fileWriter => {
				fileWriter.write(code, 0, code.length)
			}}
		}
		
		val sandbox = Config.get("runner.sandbox.path", ".") + "/box"
		val profile = Config.get("runner.sandbox.profiles.path", Config.get("runner.sandbox.path", ".") + "/profiles")
		val runtime = Runtime.getRuntime

		val commonParams = List("-c", runDirectory.getCanonicalPath, "-q", "-M", runDirectory.getCanonicalPath + "/compile.meta", "-o", "compile.out", "-r", "compile.err", "-t", Config.get("java.compile.time_limit", "30"), "-w", Config.get("java.compile.time_limit", "30"))
		
		val params = lang match {
			case "java" =>
				List(sandbox, "-S", profile + "/javac") ++ commonParams ++ List("--", Config.get("java.compiler.path", "/usr/bin/javac")) ++ inputFiles
			case "c" =>
				List(sandbox, "-S", profile + "/gcc") ++ commonParams ++ List("--", Config.get("c.compiler.path", "/usr/bin/gcc"), "-std=c99", "-O2", "-lm") ++ inputFiles
			case "cpp" =>
				List(sandbox, "-S", profile + "/gcc") ++ commonParams ++ List("--", Config.get("cpp.compiler.path", "/usr/bin/g++"), "-O2", "-lm") ++ inputFiles
			case "p" =>
				List(sandbox, "-S", profile + "/fpc") ++ commonParams ++ List("--", Config.get("p.compiler.path", "/usr/bin/fpc"), "-Tlinux", "-O2", "-Mobjfpc", "-Sc", "-Sh") ++ inputFiles
			case "py" =>
				List(sandbox, "-S", profile + "/pyc") ++ commonParams ++ List("--", Config.get("py.compiler.path", "/usr/bin/python"), "-m", "py_compile") ++ inputFiles
			case "kj" =>
				List(sandbox, "-S", profile + "/kc") ++ commonParams ++ List("--", Config.get("kcl.compiler.path", "/usr/bin/kcl"), "-lj", "-o", "Main.kx", "-c") ++ inputFiles
			case "kp" =>
				List(sandbox, "-S", profile + "/kc") ++ commonParams ++ List("--", Config.get("kcl.compiler.path", "/usr/bin/kcl"), "-lp", "-o", "Main.kx", "-c") ++ inputFiles
			case _ => null
		}

		debug("Compile {}", params.mkString(" "))

		pusing (runtime.exec(params.toArray)) { process => {
			if(process != null) {
				val status = process.waitFor
	
				if (!Config.get("runner.preserve", false)) inputFiles.foreach { new File(_).delete }
			
				if (status == 0 && (lang != "p" || new File(binDirectory, "Main").exists())) {
					if (!Config.get("runner.preserve", false)) {
						new File(runDirectory.getCanonicalPath + "/compile.meta").delete
						new File(runDirectory.getCanonicalPath + "/compile.out").delete
						new File(runDirectory.getCanonicalPath + "/compile.err").delete
					}
			
					info("compile finished successfully")
					new CompileOutputMessage(token = Some(runDirectory.getParentFile.getName))
				} else {
					val meta = MetaFile.load(runDirectory.getCanonicalPath + "/compile.meta")
			
					val compileError =
						if (meta("status") == "TO")
							"Compilation time exceeded"
						else if (meta.contains("message") && meta("status") != "RE")
							meta("message")
						else if (lang == "p")
							FileUtil.read(runDirectory.getCanonicalPath + "/compile.out").replace(runDirectory.getCanonicalPath + "/", "")
						else
							FileUtil.read(runDirectory.getCanonicalPath + "/compile.err").replace(runDirectory.getCanonicalPath + "/", "")
				
					if (!Config.get("runner.preserve", false)) {
						FileUtil.deleteDirectory(runDirectory.getParentFile.getCanonicalPath)
					}
				
					error("compile finished with errors: {}", compileError)
					new CompileOutputMessage(error_string, error=Some(compileError))
				}
			} else {
				if (!Config.get("runner.preserve", false)) {
					FileUtil.deleteDirectory(runDirectory.getParentFile.getCanonicalPath)
				}

				error("compiler failed to run")
				new CompileOutputMessage(error_string, error=Some("compiler failed to run"))
			}
		}}
	}
	
	def compile(message: CompileInputMessage): CompileOutputMessage = {
		// lang: String, code: Map[String, String], master_lang: Option[String], master_code: Option[Map[String, String]]
		info("compile {}", message.lang)
		
		val compileDirectory = new File(Config.get("compile.root", "."))
		compileDirectory.mkdirs
		
		var runDirectoryFile = File.createTempFile(System.nanoTime.toString, null, compileDirectory)
		runDirectoryFile.delete
		
		val runRoot = runDirectoryFile.getCanonicalPath.substring(0, runDirectoryFile.getCanonicalPath.length - 4) + "." + message.lang

		message.master_lang match {
			case Some(master_lang) => {
				message.master_code match {
					case Some(master_code) => {
						val master_result = compile(new File(runRoot + "/validator"), master_lang, master_code, "judge error")
						
						if (master_result.status != "ok") {
							return master_result
						}
						
						using (new PrintWriter(new FileWriter(new File(runRoot + "/validator/lang")))) { writer => {
							writer.print(master_lang)
						}}
					}
					case None => {
						return new CompileOutputMessage("judge error", error=Some("Missing code"))
					}
				}
			}
			case None => {}
		}
		
		compile(new File(runRoot + "/bin"), message.lang, message.code, "compile error")
	}
	
	def run(message: RunInputMessage, zipFile: File) : Option[RunOutputMessage] = {
		info("run {}", message)
		val casesDirectory:File = message.input match {
			case Some(in) => {
				if (in.contains(".") || in.contains("/")) throw new IllegalArgumentException("Invalid input")
				new File (Config.get("input.root", ".") + "/" + in)
			}
			case None => null
		}
		
		if(message.token.contains("..") || message.token.contains("/")) throw new IllegalArgumentException("Invalid token")
		
		if(casesDirectory != null && !casesDirectory.exists) {
			Some(new RunOutputMessage(error=Some("missing input")))
		} else {
			val runDirectory = new File(Config.get("compile.root", ".") + "/" + message.token)
		
			if(!runDirectory.exists) throw new IllegalArgumentException("Invalid token")
		
			val binDirectory = new File(runDirectory.getCanonicalPath + "/bin")
		
			val lang = message.token.substring(message.token.indexOf(".")+1)
		
			val sandbox = Config.get("runner.sandbox.path", ".") + "/box"
			val profile = Config.get("runner.sandbox.path", ".") + "/profiles"
			val runtime = Runtime.getRuntime
		
			if(casesDirectory != null) {
				casesDirectory.listFiles.filter {_.getName.endsWith(".in")} .foreach { (x) => {
					val caseName = runDirectory.getCanonicalPath + "/" + x.getName.substring(0, x.getName.lastIndexOf('.'))

					var timeLimit = message.timeLimit
					if (lang == "java" || lang == "p") {
						timeLimit += 1
					}

					val commonParams = List("-c", binDirectory.getCanonicalPath, "-q", "-M", caseName + ".meta", "-i", x.getCanonicalPath, "-o", caseName + ".out", "-r", caseName + ".err", "-t", timeLimit.toString, "-w", (message.timeLimit + 60).toString, "-O", message.outputLimit.toString)
				
					val params = lang match {
						case "java" =>
							List(sandbox, "-S", profile + "/java") ++ commonParams ++ List("--", Config.get("java.runtime.path", "/usr/bin/java"), "-Xmx" + message.memoryLimit + "k", "Main")
						case "c" =>
							List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
						case "cpp" =>
							List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
						case "p" =>
							List(sandbox, "-S", profile + "/p") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./Main")
						case "py" =>
							List(sandbox, "-S", profile + "/py") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", Config.get("py.runtime.path", "/usr/bin/python"), "Main.py")
						case "kp" =>
							List(sandbox, "-S", profile + "/kx") ++ commonParams ++ List("--", Config.get("karel.runtime.path", "/usr/bin/karel"), "/dev/stdin", "-oi", "-q", "-p2", "Main.kx")
						case "kj" =>
							List(sandbox, "-S", profile + "/kx") ++ commonParams ++ List("--", Config.get("karel.runtime.path", "/usr/bin/karel"), "/dev/stdin", "-oi", "-q", "-p2", "Main.kx")
					}

					debug("Run {}", params.mkString(" "))
				
					pusing (runtime.exec(params.toArray)) { process => process.waitFor }
				}}
			}
		
			message.cases match {
				case None => {}
				case Some(extra) => {
					extra.foreach { (x: CaseData) => {
						val caseName = x.name
						val casePath = runDirectory.getCanonicalPath + "/" + caseName
						val commonParams = List("-c", binDirectory.getCanonicalPath, "-q", "-M", casePath + ".meta", "-i", casePath + ".in", "-o", casePath + ".out", "-r", casePath + ".err", "-t", message.timeLimit.toString, "-w", (message.timeLimit + 60).toString, "-O", message.outputLimit.toString)
					
						FileUtil.write(casePath + ".in", x.data)
				
						val params = lang match {
							case "java" =>
								List(sandbox, "-S", profile + "/java") ++ commonParams ++ List("--", "/usr/bin/java", "-Xmx" + message.memoryLimit + "k", "Main")
							case "c" =>
								List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
							case "cpp" =>
								List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
							case "p" =>
								List(sandbox, "-S", profile + "/p") ++ commonParams ++ List("-m", message.memoryLimit.toString, "-n", "--", "./Main")
							case "py" =>
								List(sandbox, "-S", profile + "/py") ++ commonParams ++ List("-m", message.memoryLimit.toString, "-n", "--", "/usr/bin/python", "Main.py")
						}
				
						debug("Run {}", params.mkString(" "))

						pusing (runtime.exec(params.toArray)) { process => process.waitFor }
					
						if (!Config.get("runner.preserve", false)) new File(casePath + ".in").delete
					}}
				}
			}
		
			val zipOutput = new ZipOutputStream(new FileOutputStream(zipFile.getCanonicalPath))
		
			runDirectory.listFiles.filter { _.getName.endsWith(".meta") } .foreach { (x) => {
				val buffer = Array.ofDim[Byte](1024)
			
				val meta = MetaFile.load(x.getCanonicalPath)
			
				if(meta("status") == "OK") {
					val validatorDirectory = new File(runDirectory.getCanonicalPath + "/validator")
					if (validatorDirectory.exists) {
						val caseName = x.getName
						val metaFile = validatorDirectory.getCanonicalPath + "/" + caseName;
						var inputFile = new File(x.getCanonicalPath.replace(".meta", ".in"))
						if (!inputFile.exists) {
							inputFile = new File(casesDirectory.getCanonicalPath + "/" + caseName.replace(".meta", ".in"))
						}
						val commonParams = List("-c", validatorDirectory.getCanonicalPath, "-q", "-M", metaFile, "-i", x.getCanonicalPath.replace(".meta", ".out"), "-o", metaFile.replace(".meta", ".out"), "-r", metaFile.replace(".meta", ".err"), "-P", inputFile.getCanonicalPath, "-t", message.timeLimit.toString, "-w", (message.timeLimit + 60).toString, "-O", message.outputLimit.toString)
						
						val validator_lang = using (new BufferedReader(new FileReader(validatorDirectory.getCanonicalPath + "/lang"))) { reader => reader.readLine }
				
						val params = validator_lang match {
							case "java" =>
								List(sandbox, "-S", profile + "/java") ++ commonParams ++ List("--", "/usr/bin/java", "-Xmx" + message.memoryLimit + "k", "Main")
							case "c" =>
								List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
							case "cpp" =>
								List(sandbox, "-S", profile + "/c") ++ commonParams ++ List("-m", message.memoryLimit.toString, "--", "./a.out")
							case "p" =>
								List(sandbox, "-S", profile + "/p") ++ commonParams ++ List("-m", message.memoryLimit.toString, "-n", "--", "./Main")
							case "py" =>
								List(sandbox, "-S", profile + "/py") ++ commonParams ++ List("-m", message.memoryLimit.toString, "-n", "--", "/usr/bin/python", "Main.py")
						}
				
						debug("Validator run {}", params.mkString(" "))

						pusing (runtime.exec(params.toArray)) { process => process.waitFor }
						
						val metaAddendum = try {
							using (new BufferedReader(new FileReader(validatorDirectory.getCanonicalPath + "/" + caseName.replace(".meta", ".out")))) { reader => {
								List(("score" -> Math.max(0.0, Math.min(1.0, reader.readLine.trim.toDouble)).toString))
							}}
						} catch {
							case e: Exception => List(("status", "JE"))
						}
						
						MetaFile.save(x.getCanonicalPath, meta ++ metaAddendum)
					}
					
					val inputStream = new FileInputStream(x.getCanonicalPath.replace(".meta", ".out"))
					zipOutput.putNextEntry(new ZipEntry(x.getName.replace(".meta", ".out")))
					var read: Int = 0
				
					while( { read = inputStream.read(buffer); read > 0 } ) {
						zipOutput.write(buffer, 0, read)
					}
		
					inputStream.close
					zipOutput.closeEntry
				} else if((meta("status") == "RE" && lang == "java") || (meta("status") == "SG" && lang == "cpp")) {
					val inputStream = new FileInputStream(x.getCanonicalPath.replace(".meta", ".err"))
					zipOutput.putNextEntry(new ZipEntry(x.getName.replace(".meta", ".err")))
					var read: Int = 0
				
					while( { read = inputStream.read(buffer); read > 0 } ) {
						zipOutput.write(buffer, 0, read)
					}
		
					inputStream.close
					zipOutput.closeEntry
				}
				
				val inputStream = new FileInputStream(x.getCanonicalPath)
				zipOutput.putNextEntry(new ZipEntry(x.getName))
				var read: Int = 0
	
				while( { read = inputStream.read(buffer); read > 0 } ) {
					zipOutput.write(buffer, 0, read)
				}
			
				inputStream.close
				zipOutput.closeEntry
			
				if (!Config.get("runner.preserve", false)) {
					x.delete
					new File(x.getCanonicalPath.replace(".meta", ".err")).delete
					new File(x.getCanonicalPath.replace(".meta", ".out")).delete
				}
			}}
		
			zipOutput.close
		
			info("run finished token={}", message.token)
			
			None
		}
	}
	
	def removeCompileDir(token: String): Unit = {
		val runDirectory = new File(Config.get("compile.root", ".") + "/" + token)
		
		if(!runDirectory.exists) throw new IllegalArgumentException("Invalid token")
		
		FileUtil.deleteDirectory(runDirectory)
	}
	
	def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage = {
		val inputDirectory = new File(Config.get("input.root", ".") + "/" + inputName)
		inputDirectory.mkdirs()
		
		val input = new ZipInputStream(inputStream)
		var entry: ZipEntry = input.getNextEntry
		val buffer = Array.ofDim[Byte](1024)
		var read: Int = 0
		
		while(entry != null) {
			val outFile = new File(entry.getName())
			val output = new FileOutputStream(inputDirectory.getCanonicalPath + "/" + outFile.getName)

			while( { read = input.read(buffer); read > 0 } ) {
				output.write(buffer, 0, read)
			}

			output.close
			input.closeEntry
			entry = input.getNextEntry
		}
		
		input.close
		
		new InputOutputMessage()
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

		// Setting keystore properties
		System.setProperty("javax.net.ssl.keyStore", Config.get("runner.keystore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.trustStore", Config.get("runner.truststore", "omegaup.jks"))
		System.setProperty("javax.net.ssl.keyStorePassword", Config.get("runner.keystore.password", "omegaup"))
		System.setProperty("javax.net.ssl.trustStorePassword", Config.get("runner.truststore.password", "omegaup"))
		
		// logger
		Logging.init

		// the handler
		val handler = new AbstractHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			override def handle(target: String, baseRequest: Request, request: HttpServletRequest, response: HttpServletResponse) = {
				implicit val formats = Serialization.formats(NoTypeHints)
				
				request.getPathInfo() match {
					case "/run/" => {
						try {
							val req = Serialization.read[RunInputMessage](request.getReader)
							
							val zipFile = new File(Config.get("compile.root", ".") + "/" + req.token + "/output.zip")
							Runner.run(req, zipFile) match {
								case Some(msg: RunOutputMessage) => {
									response.setContentType("text/json")
									response.setStatus(HttpServletResponse.SC_OK)
									
									Serialization.write(msg, response.getWriter())
								}
								case _ => {
									response.setContentType("application/zip")
									response.setStatus(HttpServletResponse.SC_OK)
									response.setContentLength(zipFile.length.asInstanceOf[Int])
							
									val input = new FileInputStream(zipFile)
									val output = response.getOutputStream
									val buffer = Array.ofDim[Byte](1024)
									var read: Int = 0
		
									while( { read = input.read(buffer); read > 0 } ) {
										output.write(buffer, 0, read)
									}

									input.close
									output.close
							
									Runner.removeCompileDir(req.token)
								}
							}
						} catch {
							case e: Exception => {
								error("/run/", e)
								response.setContentType("text/json")
								response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
								Serialization.write(new RunOutputMessage(status = "error", error = Some(e.getMessage)), response.getWriter())
							}
						}
					}
					case _ => {
						response.setContentType("text/json")
						Serialization.write(request.getPathInfo() match {
							case "/compile/" => {
								try {
									val req = Serialization.read[CompileInputMessage](request.getReader())
									response.setStatus(HttpServletResponse.SC_OK)
									Runner.compile(req)
								} catch {
									case e: Exception => {
										error("/compile/", e)
										response.setStatus(HttpServletResponse.SC_BAD_REQUEST)
										new CompileOutputMessage(status = "error", error = Some(e.getMessage))
									}
								}
							}
							case "/input/" => {
								try {
									info("/input/")
									
									response.setStatus(HttpServletResponse.SC_OK)
									if(request.getContentType() != "application/zip" || request.getHeader("Content-Disposition") == null) {
										new InputOutputMessage(
											status = "error",
											error = Some("Content-Type must be \"application/zip\", Content-Disposition must be \"attachment\" and a filename must be specified")
										)
									} else {
										val ContentDispositionRegex = "attachment; filename=([a-zA-Z0-9_-][a-zA-Z0-9_.-]*);.*".r
			
										val ContentDispositionRegex(inputName) = request.getHeader("Content-Disposition")
										Runner.input(inputName, request.getInputStream)
									}
								} catch {
									case e: Exception => {
										error("/inpue/", e)
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
					}
				}
				
				baseRequest.setHandled(true)
			}
		};

		// boilerplate code for jetty with https support	
		val server = new org.eclipse.jetty.server.Server()
		
		val sslContext = new org.eclipse.jetty.util.ssl.SslContextFactory(Config.get[String]("runner.keystore", "omegaup.jks"))
		sslContext.setKeyManagerPassword(Config.get[String]("runner.password", "omegaup"))
		sslContext.setKeyStorePassword(Config.get[String]("runner.keystore.password", "omegaup"))
		sslContext.setTrustStore(Config.get[String]("runner.truststore", "omegaup.jks"))
		sslContext.setTrustStorePassword(Config.get[String]("runner.truststore.password", "omegaup"))
		sslContext.setNeedClientAuth(true)
	
		val runnerConnector = new org.eclipse.jetty.server.ssl.SslSelectChannelConnector(sslContext)
		runnerConnector.setPort(Config.get[Int]("runner.port", 0))
		
		server.setConnectors(List(runnerConnector).toArray)
		
		server.setHandler(handler)
		server.start()
		
		info("Registering port {}", runnerConnector.getLocalPort())
		
		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")
				try {
					// well, at least try to de-register
					Https.send[RegisterOutputMessage, RegisterInputMessage](
					Config.get("grader.deregister.url", "https://localhost:21680/deregister/"),
						new RegisterInputMessage(runnerConnector.getLocalPort())
					)
				} catch {
					case _ => {}
				}

				server.stop()
			}
		});
		
		new Thread() {
			override def run() = {
				while (true) {			
					Https.send[RegisterOutputMessage, RegisterInputMessage](
						Config.get("grader.register.url", "https://localhost:21680/register/"),
						new RegisterInputMessage(runnerConnector.getLocalPort())
					)
					
					Thread.sleep(5 * 60 * 1000)
				}
			}
		}.start()
	
		server.join()
	}
}

