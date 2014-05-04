package omegaup.runner

import java.io._
import javax.servlet._
import javax.servlet.http._
import net.liftweb.json._
import org.eclipse.jetty.server.Request
import org.eclipse.jetty.server.handler._
import omegaup._
import omegaup.data._
import org.apache.commons.compress.compressors.bzip2.BZip2CompressorOutputStream

class OmegaUpRunstreamWriter(outputStream: OutputStream) extends Closeable with RunCaseCallback with Log {
  private val bzip2 = new BZip2CompressorOutputStream(outputStream)
  private val dos = new DataOutputStream(bzip2)
  private var finalized = false

  def apply(filename: String, length: Long, stream: InputStream): Unit = {
    if (finalized) return
    debug("Writing {}({}) into runstream", filename, length)
    dos.writeBoolean(true)
    dos.writeUTF(filename)
    dos.writeLong(length)
    val buffer = new Array[Byte](1024)
    var read = 0
    while ( { read = stream.read(buffer, 0, buffer.length); read > 0 } ) {
      dos.write(buffer, 0, read)
    }
    dos.flush
  }

  def finalize(message: RunOutputMessage): Unit = {
    if (finalized) return
    debug("Finalizing runstream with {}", message)
    dos.writeBoolean(false)
    implicit val formats = Serialization.formats(NoTypeHints)
    Serialization.write(message, new OutputStreamWriter(dos))
    finalized = true
  }

  def close(): Unit = {
    bzip2.close
    dos.close
    outputStream.close
  }
}

object Service extends Object with Log with Using {
  def main(args: Array[String]) = {
    // Parse command-line options.
    var configPath = "omegaup.conf"
    var i = 0
    while (i < args.length) {
      if (args(i) == "--config" && i + 1 < args.length) {
        i += 1
        configPath = args(i)
      } else if (args(i) == "--output" && i + 1 < args.length) {
        i += 1
        System.setOut(new PrintStream(new FileOutputStream(args(i))))
      }
      i += 1
    }

    Config.load(configPath)

    // Get local hostname
    val hostname = Config.get("runner.hostname", "")

    if (hostname == "") {
      throw new IllegalArgumentException("runner.hostname configuration must be set")
    }

    var registerThread: RegisterThread = null
    
    // logger
    Logging.init

    // And build a runner instance
    val runner = new Runner(hostname, Minijail)

    // the handler
    val handler = new AbstractHandler() {
      @throws(classOf[IOException])
      @throws(classOf[ServletException])
      override def handle(target: String,
                          baseRequest: Request,
                          request: HttpServletRequest,
                          response: HttpServletResponse) = {
        implicit val formats = Serialization.formats(NoTypeHints)
        
        request.getPathInfo() match {
          case "/run/" => {
            var token: String = null
            response.setContentType("application/x-omegaup-runstream")
            response.setStatus(HttpServletResponse.SC_OK)

            using (new OmegaUpRunstreamWriter(response.getOutputStream)) { callbackProxy => {
              var message: RunOutputMessage = null
              message = try {
                val req = Serialization.read[RunInputMessage](request.getReader)
                token = req.token
                
                val zipFile = new File(Config.get("compile.root", "."), token + "/output.zip")
                runner.run(req, callbackProxy)
              } catch {
                case e: Exception => {
                  error("/run/", e)
                  new RunOutputMessage(status = "error", error = Some(e.getMessage))
                }
              } finally {
                if (token != null && !(message != null && message.error == Some("missing input")))
                  runner.removeCompileDir(token)
              }
              callbackProxy.finalize(message)
            }}
          }
          case _ => {
            response.setContentType("text/json")
            Serialization.write(request.getPathInfo() match {
              case "/compile/" => {
                registerThread.extendDeadline
                try {
                  val req = Serialization.read[CompileInputMessage](request.getReader())
                  response.setStatus(HttpServletResponse.SC_OK)
                  runner.compile(req)
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
                  if(request.getContentType() != "application/zip" ||
                     request.getHeader("Content-Disposition") == null) {
                    new InputOutputMessage(
                      status = "error",
                      error = Some("Content-Type must be \"application/zip\", " +
                                   "Content-Disposition must be \"attachment\" and a filename " +
                                   "must be specified"
                              )
                    )
                  } else {
                    val ContentDispositionRegex =
                      "attachment; filename=([a-zA-Z0-9_-][a-zA-Z0-9_.-]*);.*".r
      
                    val ContentDispositionRegex(inputName) =
                      request.getHeader("Content-Disposition")
                    runner.input(inputName, request.getInputStream)
                  }
                } catch {
                  case e: Exception => {
                    error("/input/", e)
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
    
    val sslContext =
      new org.eclipse.jetty.util.ssl.SslContextFactory(
        Config.get("ssl.keystore", "omegaup.jks")
      )
    sslContext.setKeyManagerPassword(Config.get("ssl.password", "omegaup"))
    sslContext.setKeyStorePassword(Config.get("ssl.keystore.password", "omegaup"))
    sslContext.setTrustStore(FileUtil.loadKeyStore(
      Config.get("ssl.truststore", "omegaup.jks"),
      Config.get("ssl.truststore.password", "omegaup")
    ))
    sslContext.setNeedClientAuth(true)
  
    val runnerConnector = new org.eclipse.jetty.server.ServerConnector(server, sslContext)
    runnerConnector.setPort(Config.get("runner.port", 0))
    
    server.setConnectors(List(runnerConnector).toArray)
    server.setHandler(handler)

    server.start()

    info("Runner {} registering port {}", hostname, runnerConnector.getLocalPort)
    registerThread = new RegisterThread(hostname, runnerConnector.getLocalPort)
    
    Runtime.getRuntime.addShutdownHook(new Thread() {
      override def run() = {
        server.stop
        registerThread.shutdown
      }
    })
		
    // Send a heartbeat every 5 minutes to register
    registerThread.start

    server.join
    registerThread.join
    info("Shut down cleanly")
  }
}
