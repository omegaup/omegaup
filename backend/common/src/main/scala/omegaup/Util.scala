package omegaup

import java.io._
import java.net._
import java.util._
import java.security.KeyStore
import java.security.cert.X509Certificate
import javax.net.ssl._
import net.liftweb.json._
import org.slf4j.{Logger, LoggerFactory}
import org.apache.commons.codec.binary.Base64InputStream
import scala.collection.mutable
import scala.language.implicitConversions

trait Using {
	def using[A, B <: java.io.Closeable] (closeable: B) (f: B => A): A =
		 try { f(closeable) } finally { closeable.close }

	def susing[A, B <: java.sql.Statement] (closeable: B) (f: B => A): A =
		 try { f(closeable) } finally { closeable.close }

	def rusing[A, B <: java.sql.ResultSet] (closeable: B) (f: B => A): A =
		 try { f(closeable) } finally { closeable.close }

	def cusing[A, B <: HttpURLConnection] (disconnectable: B) (f: B => A): A =
		 try { f(disconnectable) } finally { disconnectable.disconnect }

	def pusing[A] (process: Process) (f: Process => A): A =
		 try {
			f(process)
		} finally {
			if (process != null) {
				process.getInputStream.close()
				process.getOutputStream.close()
				process.getErrorStream.close()
			}
		}
}

object Config {
	private val props = new java.util.Properties(System.getProperties)
	load()

	def load(path: String = "omegaup.conf"): Unit = {
		try{
			props.load(new java.io.FileInputStream(path))
		} catch {
			case _: Throwable => {}
		}
	}
	
	def get[T](propname: String, default: T): T = {
		props.getProperty(propname) match {
			case null => default
			case ans:Any => default match {
				case x:String  => ans.asInstanceOf[T]
				case x:Int     => ans.toInt.asInstanceOf[T]
				case x:Boolean => (ans == "true").asInstanceOf[T]
				case _	 => null.asInstanceOf[T]
			}
		}
	}
	
	def set[T](propname: String, value: T): Unit = {
		props.setProperty(propname, value.toString)
	}
}

trait Log {
	private lazy val log = LoggerFactory.getLogger(getClass.getName.replace("$", "#").stripSuffix("#"))

	def trace(message:String, values:Any*) = 
		log.trace(message, values.map(_.asInstanceOf[Object]).toArray:_*)
	def trace(message:String, error:Throwable) = log.trace(message, error)

	def debug(message:String, values:Any*) =
		log.debug(message, values.map(_.asInstanceOf[Object]).toArray:_*)
	def debug(message:String, error:Throwable) = log.debug(message, error)

	def info(message:String, values:Any*) =
		log.info(message, values.map(_.asInstanceOf[Object]).toArray:_*)
	def info(message:String, error:Throwable) = log.info(message, error)

	def warn(message:String, values:Any*) =
		log.warn(message, values.map(_.asInstanceOf[Object]).toArray:_*)
	def warn(message:String, error:Throwable) = log.warn(message, error)

	def error(message:String, values:Any*) = 
		log.error(message, values.map(_.asInstanceOf[Object]).toArray:_*)
	def error(message:String, error:Throwable) = log.error(message, error)
}

object Logging extends Object with Log {
	def init(): Unit = {
		System.setProperty("org.mortbay.log.class", "org.mortbay.log.Slf4jLog")

		val rootLogger = LoggerFactory.getLogger(Logger.ROOT_LOGGER_NAME).asInstanceOf[ch.qos.logback.classic.Logger]

		rootLogger.detachAndStopAllAppenders

		val context = rootLogger.getLoggerContext
		var appender: ch.qos.logback.core.Appender[ch.qos.logback.classic.spi.ILoggingEvent] = null
		val encoderPattern = "%date [%thread] %-5level %logger{35} - %msg%n"

		if (Config.get("logging.file", "") == "syslog") {
			val syslogAppender = new ch.qos.logback.classic.net.SyslogAppender()
			syslogAppender.setFacility("SYSLOG")

			appender = syslogAppender
		} else if (Config.get("logging.file", "") != "") {
			val encoder = new ch.qos.logback.classic.encoder.PatternLayoutEncoder()
			encoder.setContext(context)
			encoder.setPattern(encoderPattern)
			encoder.start()

			val fileAppender = new ch.qos.logback.core.FileAppender[ch.qos.logback.classic.spi.ILoggingEvent]()
			fileAppender.setAppend(true)
			fileAppender.setFile(Config.get("logging.file", ""))
			fileAppender.setEncoder(encoder)

			appender = fileAppender
		} else {
			val encoder = new ch.qos.logback.classic.encoder.PatternLayoutEncoder()
			encoder.setContext(context)
			encoder.setPattern(encoderPattern)
			encoder.start()

			val consoleAppender = new ch.qos.logback.core.ConsoleAppender[ch.qos.logback.classic.spi.ILoggingEvent]()
			consoleAppender.setEncoder(encoder)

			appender = consoleAppender
		}

		appender.setContext(context)
		appender.addFilter(new ch.qos.logback.core.filter.Filter[ch.qos.logback.classic.spi.ILoggingEvent]() {
			override def decide(event: ch.qos.logback.classic.spi.ILoggingEvent): ch.qos.logback.core.spi.FilterReply = {
				val throwable = event.getThrowableProxy()

				if (throwable == null) {
					ch.qos.logback.core.spi.FilterReply.ACCEPT
				} else {
					val message = throwable.getClassName()

					if (message.contains("java.nio.channels.ClosedChannelException") ||
					    message.contains("org.mortbay.jetty.EofException")
					) {
						ch.qos.logback.core.spi.FilterReply.DENY
					} else {
						ch.qos.logback.core.spi.FilterReply.ACCEPT
					}
				}
			}
		})
		appender.start

		rootLogger.addAppender(appender)

		rootLogger.setLevel(
			Config.get("logging.level", "info") match {
				case "all" => ch.qos.logback.classic.Level.TRACE
				case "finest" => ch.qos.logback.classic.Level.TRACE
				case "finer" => ch.qos.logback.classic.Level.TRACE
				case "trace" => ch.qos.logback.classic.Level.TRACE
				case "fine" => ch.qos.logback.classic.Level.DEBUG
				case "config" => ch.qos.logback.classic.Level.DEBUG
				case "debug" => ch.qos.logback.classic.Level.DEBUG
				case "info" => ch.qos.logback.classic.Level.INFO
				case "warn" => ch.qos.logback.classic.Level.WARN
				case "warning" => ch.qos.logback.classic.Level.WARN
				case "error" => ch.qos.logback.classic.Level.ERROR
				case "severe" => ch.qos.logback.classic.Level.ERROR
			}
		)

		info("Logger loaded for {}", Config.get("logging.file", ""))
	}
}

class EnumerationWrapper[T](enumeration:java.util.Enumeration[T]) extends Iterator[T] {
	def hasNext:Boolean = enumeration.hasMoreElements()
	def next:T = enumeration.nextElement()
	def remove:Unit = {}
	
	implicit def enumerationToEnumerationWrapper[T](enumeration:java.util.Enumeration[T]):EnumerationWrapper[T] = {
		new EnumerationWrapper(enumeration)
	}
}

object Https extends Object with Log with Using {
	val defaultSocketFactory = SSLSocketFactory.getDefault().asInstanceOf[SSLSocketFactory]
	val runnerSocketFactory = new SSLSocketFactory {
		private val sslContextFactory = new org.eclipse.jetty.util.ssl.SslContextFactory(
			Config.get("ssl.keystore", "omegaup.jks"))
		sslContextFactory.setKeyManagerPassword(Config.get("ssl.password", "omegaup"))
		sslContextFactory.setKeyStorePassword(Config.get("ssl.keystore.password", "omegaup"))
		sslContextFactory.setTrustStore(FileUtil.loadKeyStore(
			Config.get("ssl.truststore", "omegaup.jks"),
			Config.get("ssl.truststore.password", "omegaup")
		))
		sslContextFactory.setNeedClientAuth(true)
		sslContextFactory.start
		private val socketFactory = sslContextFactory.getSslContext.getSocketFactory

		override def getDefaultCipherSuites(): Array[String] = defaultSocketFactory.getDefaultCipherSuites
		override def getSupportedCipherSuites(): Array[String] = defaultSocketFactory.getSupportedCipherSuites

		@throws(classOf[IOException])
		override def createSocket(host: String, port: Int): Socket =
			socketFactory.createSocket(host, port)

		@throws(classOf[IOException])
		override def createSocket(host: String, port: Int, clientAddress: InetAddress, clientPort: Int): Socket =
			socketFactory.createSocket(host, port, clientAddress, clientPort)

		@throws(classOf[IOException])
		override def createSocket(address: InetAddress, port: Int): Socket =
			socketFactory.createSocket(address, port)

		@throws(classOf[IOException])
		override def createSocket(address: InetAddress, port: Int, clientAddress: InetAddress, clientPort: Int): Socket =
			socketFactory.createSocket(address, port, clientAddress, clientPort)

		@throws(classOf[IOException])
		override def createSocket(socket: Socket, host: String, port: Int, autoClose: Boolean): Socket =
			socketFactory.createSocket(socket, host, port, autoClose)

		@throws(classOf[IOException])
		override def createSocket(): Socket = socketFactory.createSocket
	}

	def get(url: String, runner: Boolean = true):String = {
		debug("GET {}", url)

		if (url.startsWith("https://")) {
			cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
				conn.addRequestProperty("Connection", "close")
				if (runner) {
					conn.setSSLSocketFactory(runnerSocketFactory)
				} else {
					conn.setSSLSocketFactory(defaultSocketFactory)
				}
				conn.setDoOutput(false)
				new BufferedReader(new InputStreamReader(conn.getInputStream())).readLine
			}}
		} else {
			cusing (new URL(url).openConnection().asInstanceOf[HttpURLConnection]) { conn => {
				conn.addRequestProperty("Connection", "close")
				conn.setDoOutput(false)
				new BufferedReader(new InputStreamReader(conn.getInputStream())).readLine
			}}
		}
	}

	def post[T](url: String, data: scala.collection.Map[String, String], runner: Boolean = true)(implicit mf: Manifest[T]):T = {
		debug("POST {}", url)

		val postdata = data.map { case(key, value) =>
			URLEncoder.encode(key, "UTF-8") + "=" + URLEncoder.encode(value, "UTF-8")
		}.mkString("&").getBytes("UTF-8")

		implicit val formats = Serialization.formats(NoTypeHints)

		if (url.startsWith("https://")) {
			cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
				conn.setDoOutput(true)
				conn.setRequestMethod("POST")
				conn.addRequestProperty("Connection", "close")
				conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded")
				conn.setRequestProperty("Content-Length", postdata.length.toString)
				if (runner) {
					conn.setSSLSocketFactory(runnerSocketFactory)
				} else {
					conn.setSSLSocketFactory(defaultSocketFactory)
				}
				val stream = conn.getOutputStream()
				stream.write(postdata, 0, postdata.length)
				stream.close
				Serialization.read[T](new InputStreamReader(conn.getInputStream()))
			}}
		} else {
			cusing (new URL(url).openConnection().asInstanceOf[HttpURLConnection]) { conn => {
				conn.setDoOutput(true)
				conn.setRequestMethod("POST")
				conn.addRequestProperty("Connection", "close")
				conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded")
				conn.setRequestProperty("Content-Length", postdata.length.toString)
				val stream = conn.getOutputStream()
				stream.write(postdata, 0, postdata.length)
				stream.close
				Serialization.read[T](new InputStreamReader(conn.getInputStream()))
			}}
		}
	}
	
  def send[T, W <: AnyRef](url:String, request:W, responseReader: InputStream=>T, runner: Boolean = true)(implicit mf: Manifest[T]):T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
			conn.addRequestProperty("Content-Type", "text/json")
			conn.addRequestProperty("Connection", "close")
			if (runner) {
				conn.setSSLSocketFactory(runnerSocketFactory)
			} else {
				conn.setSSLSocketFactory(defaultSocketFactory)
			}
			conn.setDoOutput(true)
			val writer = new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))
			Serialization.write[W, PrintWriter](request, writer)
			writer.close()
		
			responseReader(conn.getInputStream)
		}}
	}

	def send[T, W <: AnyRef](url:String, request:W, runner: Boolean = true)(implicit mf: Manifest[T]):T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
			conn.addRequestProperty("Content-Type", "text/json")
			conn.addRequestProperty("Connection", "close")
			if (runner) {
				conn.setSSLSocketFactory(runnerSocketFactory)
			} else {
				conn.setSSLSocketFactory(defaultSocketFactory)
			}
			conn.setDoOutput(true)
			val writer = new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))
			Serialization.write[W, PrintWriter](request, writer)
			writer.close()
		
			Serialization.read[T](new InputStreamReader(conn.getInputStream()))
		}}
	}
	
	def zip_send[T](url:String, zipfile:String, zipname:String, runner: Boolean = true)(implicit mf: Manifest[T]): T = {
		val file = new File(zipfile)
		
		zip_send(url, new FileInputStream(zipfile), file.length.toInt, zipname, runner)
	}
	
	def zip_send[T](url:String, inputStream:InputStream, zipSize:Int, zipname:String, runner: Boolean = true)(implicit mf: Manifest[T]): T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
			conn.addRequestProperty("Content-Type", "application/zip")
			conn.addRequestProperty("Content-Disposition", "attachment; filename=" + zipname + ";")
			conn.addRequestProperty("Connection", "close")
			conn.setFixedLengthStreamingMode(zipSize)
			if (runner) {
				conn.setSSLSocketFactory(runnerSocketFactory)
			} else {
				conn.setSSLSocketFactory(defaultSocketFactory)
			}
			conn.setDoOutput(true)
			val outputStream = conn.getOutputStream
			val buffer = Array.ofDim[Byte](1024)
			var read = 0
			var reading = true
		
			while(reading) {
				read = inputStream.read(buffer)
				if (read == -1) reading = false
				else outputStream.write(buffer, 0, read)
			}
		
			inputStream.close
			outputStream.close
		
			Serialization.read[T](new InputStreamReader(conn.getInputStream()))
		}}
	}
	
	def receive_zip[T, W <: AnyRef](url:String, request:W, file:String, runner: Boolean = true)(implicit mf: Manifest[T]): Option[T] = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)

		cusing (new URL(url).openConnection().asInstanceOf[HttpsURLConnection]) { conn => {
			conn.addRequestProperty("Content-Type", "text/json")
			if (runner) {
				conn.setSSLSocketFactory(runnerSocketFactory)
			} else {
				conn.setSSLSocketFactory(defaultSocketFactory)
			}
			conn.setDoOutput(true)
			val writer = new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))
			Serialization.write[W, PrintWriter](request, writer)
			writer.close()
		
			if (conn.getHeaderField("Content-Type") == "application/zip") {
				val outputStream = new FileOutputStream(file)
				val inputStream = conn.getInputStream
				val buffer = Array.ofDim[Byte](1024)
				var read = 0
		
				while( { read = inputStream.read(buffer) ; read > 0 } ) {
					outputStream.write(buffer, 0, read)
				}
		
				inputStream.close
				outputStream.close
			
				None
			} else {
				Some(Serialization.read[T](new InputStreamReader(conn.getInputStream())))
			}
		}}
	}
}

object FileUtil extends Object with Using {
	@throws(classOf[IOException])
	def read(file: String): String = {
		val contents = new StringBuffer
		var ch: Int = 0
		
		using (new FileReader(file)) { fileReader => {
			while( {ch = fileReader.read(); ch != -1} ) {
				contents.appendCodePoint(ch)
			}
		
			contents.toString.trim
		}}
	}
	
	@throws(classOf[IOException])
	def write(file: String, data: String): Unit = {
		using (new FileWriter(file)) { fileWriter =>
			fileWriter.write(data)
		}
	}

	@throws(classOf[IOException])
	def copy(src: File, dest: File): Unit = {
		using (new FileInputStream(src)) { inputStream => {
			using (new FileOutputStream(dest)) { outputStream => {
				copy(inputStream, outputStream)
			}}
		}}
	}

	@throws(classOf[IOException])
	def copy(src: InputStream, dest: OutputStream): Unit = {
		val buffer = Array.ofDim[Byte](1024)
		var read = 0

		while( { read = src.read(buffer) ; read > 0 } ) {
			dest.write(buffer, 0, read)
		}
	}
		
	@throws(classOf[IOException])
	def deleteDirectory(dir: String): Boolean = {
		FileUtil.deleteDirectory(new File(dir))
	}
	
	@throws(classOf[IOException])
	def deleteDirectory(dir: File): Boolean = {
		if(dir.exists) {
			if (dir.isDirectory)
				dir.listFiles.foreach { FileUtil.deleteDirectory(_) }
			dir.delete
		}
		false
	}

	def removeExtension(name: String): String = {
		val extension = name.lastIndexOf('.')
		if (extension != -1) {
			return name.substring(0, extension)
		} else {
			return name
		}
	}

	def basename(path: String): String = {
		val sep = path.lastIndexOf('/')
		if (sep != -1) {
			return path.substring(sep + 1)
		} else {
			return path
		}
	}

	def loadKeyStore(path: String, password: String): KeyStore = {
		val keystore = KeyStore.getInstance(KeyStore.getDefaultType)
		using (new FileInputStream(path)) { (in) => {
			keystore.load(in, password.toCharArray)
			keystore
		}}
	}
}

object MetaFile extends Object with Using {
	@throws(classOf[IOException])
	def load(path: String): scala.collection.Map[String,String] = {
		using (new FileReader(path)) { reader => 
			load(reader)
		}
	}
	
	@throws(classOf[IOException])
	def load(reader: Reader): scala.collection.Map[String,String] = {
		val meta = new mutable.ListMap[String,String]
		using (new BufferedReader(reader)) { bReader => {
			var line: String = null
	
			while( { line = bReader.readLine(); line != null} ) {
				val idx = line.indexOf(':')
			
				if(idx > 0) {
					meta += (line.substring(0, idx) -> line.substring(idx+1))
				}
			}
		
			meta
		}}
	}
	
	@throws(classOf[IOException])
	def save(path: String, meta: scala.collection.Map[String,String]) = {
		using (new PrintWriter(new FileWriter(path))) { writer => {
			for ((key, value) <- meta) writer.printf("%s:%s\n", key, value)
		}}
	}
}

object DataUriStream extends Object with Log {
	def apply(stream: InputStream) = {
		System.out.println(stream)
		debug("Reading data URI")

		val buffer = Array.ofDim[Byte](1024)
		var bytesRead = 0
		var ch = 0
		
		bytesRead = stream.read(buffer, 0, 5)

		if (bytesRead != 5 || new String(buffer, 0, bytesRead) != "data:") {
			debug("Illegal data URI: No \"data\"")
			throw new IOException("Illegal data uri stream")
		}

		while ({ch = stream.read ; bytesRead < buffer.length && ch != -1 && ch != ','}) {
			buffer(bytesRead) = ch.toByte
			bytesRead += 1
		}

		if (ch == -1) {
			debug("Illegal data URI: No comma")
			throw new IOException("Illegal data uri stream")
		}

		if (new String(buffer, 0, bytesRead).contains("base64")) {
			debug("Using base64")
			new Base64InputStream(stream)
		} else {
			debug("Using regular stream")
			stream
		}
	}
}

class DataUriInputStream(stream: InputStream) extends FilterInputStream(DataUriStream(stream)) with Log {}
