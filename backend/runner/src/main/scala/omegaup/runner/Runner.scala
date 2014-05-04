package omegaup.runner

import java.io._
import java.util.zip._
import scala.collection.{mutable,immutable}
import omegaup._
import omegaup.data._

class Runner(name: String, sandbox: Sandbox) extends RunnerService with Log with Using {
  def name() = name

  def isInterpreted(lang: String) = lang == "py"

  def compile(runDirectory: File,
              lang: String,
              codes: List[(String, String)],
              error_string: String): CompileOutputMessage = {
    runDirectory.mkdirs
    
    val inputFiles = new mutable.ListBuffer[String]
    
    for ((name, code) <- codes) {
      if (name.contains("/")) {
        return new CompileOutputMessage(error_string, error=Some("invalid filenames"))
      }
      inputFiles += runDirectory.getCanonicalPath + "/" + name
      using (new FileWriter(new File(runDirectory, name))) { fileWriter => {
        fileWriter.write(code, 0, code.length)
      }}
    }

    if (lang == "cat") {
      // Literal. We're done.
      info("compile finished successfully")
      return new CompileOutputMessage(token = Some(runDirectory.getParentFile.getName))
    }

    // Store the first compilation error for multi-file Pascal.
    var previousError: String = null
  
    // Workaround for fpc's weird rules regarding compilation order.
    var pascalMain = runDirectory.getCanonicalPath + "/" + "Main.p"
    if (inputFiles.contains(pascalMain) && inputFiles.size > 1) {
      // Exclude Main.p
      inputFiles -= pascalMain

      // Files need to be compiled individually.
      for (inputFile <- inputFiles) {
        sandbox.compile(
          "p",
          List(inputFile),
          chdir = runDirectory.getCanonicalPath,
          metaFile = runDirectory.getCanonicalPath + "/compile.meta",
          outputFile = "compile.out",
          errorFile = "compile.err"
        ) { status => {
          if(status >= 0) {
            val meta = MetaFile.load(runDirectory.getCanonicalPath + "/compile.meta")

            if (status != 0 && previousError == null) {
              previousError = 
                if (meta("status") == "TO")
                  "Compilation time exceeded"
                else
                  FileUtil.read(runDirectory.getCanonicalPath + "/compile.out")
                    .replace(runDirectory.getCanonicalPath + "/", "")
            }
          } else {
            previousError = "Unable to compile " + inputFile
          }
        }}
      }

      // Now use the regular case to compile Main.
      inputFiles.clear
      inputFiles += pascalMain
    }
 
    sandbox.compile(
      lang,
      inputFiles,
      chdir = runDirectory.getCanonicalPath,
      metaFile = runDirectory.getCanonicalPath + "/compile.meta",
      outputFile = "compile.out",
      errorFile = "compile.err"
    ) { status => {
      if(status >= 0) {
        if (!isInterpreted(lang) && !Config.get("runner.preserve", false)) {
          inputFiles.foreach { new File(_).delete }
        }

        val missingMainClass = lang match {
          case "p" => !(new File(runDirectory, "Main").exists())
          case "java" => !(new File(runDirectory, "Main.class").exists())
          case _ => false
        }
      
        if (previousError == null &&
            status == 0 &&
            !missingMainClass) {
          if (!Config.get("runner.preserve", false)) {
            new File(runDirectory.getCanonicalPath + "/compile.meta").delete
            new File(runDirectory.getCanonicalPath + "/compile.out").delete
            new File(runDirectory.getCanonicalPath + "/compile.err").delete
          }
      
          info("compile finished successfully")
          new CompileOutputMessage(token = Some(runDirectory.getParentFile.getName))
        } else {
          val meta = MetaFile.load(runDirectory.getCanonicalPath + "/compile.meta")
      
          var compileError =
            if (previousError != null)
              previousError
            else if (meta("status") == "TO")
              "Compilation time exceeded"
            else if (meta.contains("message") && meta("status") != "RE")
              meta("message")
            else if (lang == "p")
              FileUtil
                .read(runDirectory.getCanonicalPath + "/compile.out")
                .replace(runDirectory.getCanonicalPath + "/", "")
            else
              FileUtil
                .read(runDirectory.getCanonicalPath + "/compile.err")
                .replace(runDirectory.getCanonicalPath + "/", "")

          if (compileError == "") {
            compileError = "Class should be called \"Main\"."
          }
        
          if (!Config.get("runner.preserve", false)) {
            FileUtil.deleteDirectory(runDirectory.getParentFile.getCanonicalPath)
          }
        
          error("compile finished with errors: {}", compileError)
          new CompileOutputMessage(error_string, error = Some(compileError))
        }
      } else {
        if (!Config.get("runner.preserve", false)) {
          FileUtil.deleteDirectory(runDirectory.getParentFile.getCanonicalPath)
        }

        error("compiler failed to run")
        new CompileOutputMessage(error_string, error = Some("compiler failed to run"))
      }
    }}
  }
  
  def compile(message: CompileInputMessage): CompileOutputMessage = {
    info("compile {}", message.lang)
    
    val compileDirectory = new File(Config.get("compile.root", "."))
    compileDirectory.mkdirs
    
    var runDirectoryFile = File.createTempFile(System.nanoTime.toString, null, compileDirectory)
    runDirectoryFile.delete
    
    val runRoot =
      runDirectoryFile
        .getCanonicalPath
        .substring(0, runDirectoryFile.getCanonicalPath.length - 4) + "." + message.lang

    message.master_lang match {
      case Some(master_lang) => {
        message.master_code match {
          case Some(master_code) => {
            val master_result = compile(new File(runRoot + "/validator"),
                                        master_lang,
                                        master_code,
                                        "judge error")
            
            if (master_result.status != "ok") {
              return master_result
            }
            
            using (new PrintWriter(new FileWriter(runRoot + "/validator/lang"))) { writer => {
              writer.print(master_lang)
            }}
          }
          case None => {
            return new CompileOutputMessage("judge error", error = Some("Missing code"))
          }
        }
      }
      case None => {}
    }
    
    compile(new File(runRoot + "/bin"), message.lang, message.code, "compile error")
  }

  def run(message: RunInputMessage, callback: RunCaseCallback) : RunOutputMessage = {
    info("run {}", message)
    val casesDirectory:File = message.input match {
      case Some(in) => {
        if (in.contains(".") || in.contains("/")) {
          return new RunOutputMessage(status="error", error=Some("Invalid input"))
        }
        new File (Config.get("input.root", ".") + "/" + in)
      }
      case None => null
    }
    
    if(message.token.contains("..") || message.token.contains("/")) {
      return new RunOutputMessage(status="error", error=Some("Invalid token"))
    }
    
    if(casesDirectory != null && !casesDirectory.exists) {
      new RunOutputMessage(status="error", error=Some("missing input"))
    } else {
      val runDirectory = new File(Config.get("compile.root", ".") + "/" + message.token)
    
      if(!runDirectory.exists) return new RunOutputMessage(status="error", error=Some("Invalid token"))
    
      val binDirectory = new File(runDirectory.getCanonicalPath + "/bin")
    
      val lang = message.token.substring(message.token.indexOf(".")+1)

      if (lang == "cat") {
        // Literal. Just copy the "program" as the output and produce a fake .meta.
        try {
          debug("Literal submission {}", new File(binDirectory, "Main.cat"))
          using (new FileInputStream(new File(binDirectory, "Main.cat"))) { fileStream => {
            using (new ZipInputStream(new DataUriInputStream(fileStream))) { stream => {
              debug("Literal stream")
              val inputFiles = casesDirectory.listFiles
                                             .filter {_.getName.endsWith(".in")}
                                             .map { _.getName }
              var entry: ZipEntry = stream.getNextEntry
      
              while(entry != null) {
                debug("Literal stream: {}", entry.getName)
                val caseName = FileUtil.removeExtension(FileUtil.basename(entry.getName))
                if (entry.getName.endsWith(".out") && inputFiles.contains(caseName + ".in")) {
                  using (new FileOutputStream(new File(runDirectory, caseName + ".out"))) {
                    FileUtil.copy(stream, _)
                  }
                  FileUtil.write(new File(runDirectory, caseName + ".meta").getCanonicalPath,
                                 "time:0\ntime-wall:0\nmem:0\nsyscall-count:0\nstatus:OK")
                }
                stream.closeEntry
                entry = stream.getNextEntry
              }
            }}
          }}
        } catch {
          case e: Exception => {
            warn("Literal submission: {}", e)
            val caseName = runDirectory.getCanonicalPath + "/Main"
            FileUtil.copy(new File(binDirectory, "Main.cat"), new File(caseName + ".out"))
            FileUtil.write(caseName + ".meta",
                           "time:0\ntime-wall:0\nmem:0\nsyscall-count:0\nstatus:OK")
          }
        }
      } else {
        if(casesDirectory != null) {
          casesDirectory.listFiles.filter {_.getName.endsWith(".in")} .foreach { (x) => {
            val caseName = runDirectory.getCanonicalPath +
                           "/" +
                           FileUtil.removeExtension(x.getName)

            sandbox.run(message,
                        lang,
                        chdir = binDirectory.getCanonicalPath,
                        metaFile = caseName + ".meta",
                        inputFile = x.getCanonicalPath,
                        outputFile = caseName + ".out",
                        errorFile = caseName + ".err"
            )
          }}
        }
      
        message.cases match {
          case None => {}
          case Some(extra) => {
            extra.foreach { (x: CaseData) => {
              val caseName = x.name
              val casePath = runDirectory.getCanonicalPath + "/" + caseName
            
              FileUtil.write(casePath + ".in", x.data)
         
              sandbox.run(message,
                          lang,
                          chdir = binDirectory.getCanonicalPath,
                          metaFile = casePath + ".meta",
                          inputFile = casePath + ".in",
                          outputFile = casePath + ".out",
                          errorFile = casePath + ".err"
              )
            }}
          }
        }
      }
    
      runDirectory.listFiles.filter { _.getName.endsWith(".meta") } .foreach { (x) => {
        val meta = MetaFile.load(x.getCanonicalPath)
        var addedErr = false
        var addedOut = false
      
        if(meta("status") == "OK") {
          val validatorDirectory = new File(runDirectory.getCanonicalPath + "/validator")
          if (validatorDirectory.exists) {
            val caseName = FileUtil.removeExtension(x.getName)
            val caseFile = new File(validatorDirectory, caseName).getCanonicalPath;
            var inputFile = new File(FileUtil.removeExtension(x.getCanonicalPath) + ".in")
            if (!inputFile.exists) {
              inputFile = new File(casesDirectory, caseName + ".in")
            }
            
            val validator_lang =
              using (new BufferedReader(new FileReader(new File(validatorDirectory, "lang")))) {
                reader => reader.readLine
              }

            sandbox.run(message,
                        validator_lang,
                        logTag = "Validator run",
                        extraParams = List(caseName, lang),
                        chdir = validatorDirectory.getCanonicalPath,
                        metaFile = caseFile + ".meta",
                        inputFile = FileUtil.removeExtension(x.getCanonicalPath) + ".out",
                        outputFile = caseFile + ".out",
                        errorFile = caseFile + ".err",
                        originalInputFile = Some(inputFile.getCanonicalPath),
                        runMetaFile = Some(x.getCanonicalPath)
            )

            if (message.debug) {
              publish(callback, new File(caseFile + ".meta"), "validator/")
              publish(callback, new File(caseFile + ".out"), "validator/")
              publish(callback, new File(caseFile + ".err"), "validator/")
            }
            
            val metaAddendum = try {
              using (new BufferedReader(new FileReader(caseFile + ".out"))) { reader => {
                List(
                  ("score" -> math.max(0.0, math.min(1.0, reader.readLine.trim.toDouble)).toString)
                )
              }}
            } catch {
              case e: Exception => {
                error("validador", caseFile + ".out", e)
                List(("status", "JE"))
              }
            }
            
            MetaFile.save(x.getCanonicalPath, meta ++ metaAddendum)
          }
          
          publish(callback, new File(x.getCanonicalPath.replace(".meta", ".out")))
          addedOut = true
        } else if((meta("status") == "RE" && lang == "java") ||
                  (meta("status") == "SG" && lang == "cpp") ||
                  (meta("status") == "SG" && lang == "cpp11")) {
          publish(callback, new File(x.getCanonicalPath.replace(".meta", ".err")))
          addedErr = true
        }
        
        publish(callback, x)

        if (message.debug) {
          if (!addedErr) {
            publish(callback, new File(x.getCanonicalPath.replace(".meta", ".err")))
          }
          if (!addedOut) {
            publish(callback, new File(x.getCanonicalPath.replace(".meta", ".out")))
          }
        }
      }}
    
      info("run finished token={}", message.token)
      
      new RunOutputMessage()
    }
  }

  def publish(callback: RunCaseCallback, file: File, prefix: String = "") = {
    using (new FileInputStream(file)) {
      callback(prefix + file.getName, file.length, _)
    }
  }
  
  def removeCompileDir(token: String): Unit = {
    val runDirectory = new File(Config.get("compile.root", ".") + "/" + token)
   
    // HACKHACKHACK Investigar por qué esto está ocurriendo.
    // if (!runDirectory.exists) throw new IllegalArgumentException("Invalid token")
    if (!runDirectory.exists) {
      error("Directory {} was removed earlier. This is an error", runDirectory)
      return
    }

    if (!Config.get("runner.preserve", false)) FileUtil.deleteDirectory(runDirectory)
  }

  def input(inputName: String, inputStream: InputStream, size: Int = -1): InputOutputMessage = {
    val inputDirectory = new File(Config.get("input.root", ".") + "/" + inputName)
    inputDirectory.mkdirs()
    
    using (new ZipInputStream(inputStream)) { input => {
      var entry: ZipEntry = input.getNextEntry
    
      while(entry != null) {
        using (new FileOutputStream(new File(inputDirectory, entry.getName))) {
          FileUtil.copy(input, _)
        }
        input.closeEntry
        entry = input.getNextEntry
      }
    }}
    
    new InputOutputMessage()
  }
}

class RegisterThread(hostname: String, port: Int) extends Thread("RegisterThread") with Log {
  private var deadline = 0L
  private var alive = true
  private val lock = new Object

  def extendDeadline() = {
    deadline = System.currentTimeMillis + 5 * 60 * 1000;
  }

  def shutdown() = {
    alive = false
    lock.synchronized {
      lock.notifyAll
    }
    info("Shutting down")
    try {
      // well, at least try to de-register
      Https.send[RegisterOutputMessage, RegisterInputMessage](
        Config.get("grader.deregister.url", "https://localhost:21680/deregister/"),
        new RegisterInputMessage(hostname, port)
      )
    } catch {
      case _: Throwable => {
        // Best effort is best effort.
      }
    }
  }

  private def waitUntilDeadline(): Unit = {
    while (alive) {
      val time = System.currentTimeMillis
      if (time >= deadline) return
      
      try {
        lock.synchronized {
          lock.wait(deadline - time)
        }
      } catch {
        case e: InterruptedException => {}
      }
    }
  }

  override def run(): Unit = {
    while (alive) {
      waitUntilDeadline
      if (!alive) return
      try {
        Https.send[RegisterOutputMessage, RegisterInputMessage](
          Config.get("grader.register.url", "https://localhost:21680/register/"),
          new RegisterInputMessage(hostname, port)
        )
      } catch {
        case e: IOException => {
          error("Failed to register", e)
        }
      }
      extendDeadline
    }
  }
}
