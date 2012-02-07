package omegaup

import java.io._
import java.net._
import java.util._
import javax.net.ssl._
import net.liftweb.json._
import org.slf4j.{Logger, LoggerFactory}
import scala.collection.mutable

object Config {
	private val props = new java.util.Properties(System.getProperties)
	try{
		props.load(new java.io.FileInputStream("omegaup.conf"))
	} catch {
		case _ => {}
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
		log.trace(message, values.map(_.asInstanceOf[Object]).toArray)
	def trace(message:String, error:Throwable) = log.trace(message, error)
	def trace(message:String, error:Throwable, values:Any*) =
		log.trace(message, error, values.map(_.asInstanceOf[Object]).toArray)

	def debug(message:String, values:Any*) =
		log.debug(message, values.map(_.asInstanceOf[Object]).toArray)
	def debug(message:String, error:Throwable) = log.debug(message, error)
	def debug(message:String, error:Throwable, values:Any*) = 
		log.debug(message, error, values.map(_.asInstanceOf[Object]).toArray)

	def info(message:String, values:Any*) =
		log.info(message, values.map(_.asInstanceOf[Object]).toArray)
	def info(message:String, error:Throwable) = log.info(message, error)
	def info(message:String, error:Throwable, values:Any*) = 
		log.info(message, error, values.map(_.asInstanceOf[Object]).toArray)

	def warn(message:String, values:Any*) =
		log.warn(message, values.map(_.asInstanceOf[Object]).toArray)
	def warn(message:String, error:Throwable) = log.warn(message, error)
	def warn(message:String, error:Throwable, values:Any*) = 
		log.warn(message, error, values.map(_.asInstanceOf[Object]).toArray)

	def error(message:String, values:Any*) = 
		log.error(message, values.map(_.asInstanceOf[Object]).toArray)
	def error(message:String, error:Throwable) = log.error(message, error)
	def error(message:String, error:Throwable, values:Any*) =
		log.error(message, error, values.map(_.asInstanceOf[Object]).toArray)
}

object Logging {
	def init(): Unit = {
		System.setProperty("org.mortbay.log.class", "org.mortbay.log.Slf4jLog")

		val rootLogger = LoggerFactory.getLogger(Logger.ROOT_LOGGER_NAME).asInstanceOf[ch.qos.logback.classic.Logger]

		if(Config.get("logging.file", "") != "") {
			rootLogger.detachAndStopAllAppenders

			val appender = new ch.qos.logback.core.FileAppender[ch.qos.logback.classic.spi.ILoggingEvent]()
			val encoder = new ch.qos.logback.classic.encoder.PatternLayoutEncoder()
			val context = rootLogger.getLoggerContext

			encoder.setContext(context);
			encoder.setPattern("%date [%thread] %-5level %logger{35} - %msg%n");
			encoder.start()

			appender.setAppend(true)
			appender.setContext(context)
			appender.setFile(Config.get("logging.file", ""))
			appender.setEncoder(encoder)
			appender.start

			rootLogger.addAppender(appender)
		}

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

object Https extends Object with Log {
	val socketFactory = SSLSocketFactory.getDefault().asInstanceOf[SSLSocketFactory]

	HttpsURLConnection.setDefaultHostnameVerifier(new HostnameVerifier() {
		def verify(hostname:String, session:SSLSession) = {
			debug("Verifying {}", hostname)
			true
		}
	})
	
	def send[T, W <: AnyRef](url:String, request:W)(implicit mf: Manifest[T]):T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		val conn = new URL(url).openConnection().asInstanceOf[HttpsURLConnection]
		conn.addRequestProperty("Content-Type", "text/json")
		conn.setSSLSocketFactory(socketFactory)
		conn.setDoOutput(true)
		val writer = new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))
		Serialization.write[W, PrintWriter](request, writer)
		writer.close()
		
		Serialization.read[T](new InputStreamReader(conn.getInputStream()))
	}
	
	def zip_send[T](url:String, zipfile:String, zipname:String)(implicit mf: Manifest[T]): T = {
		val file = new File(zipfile)
		
		zip_send(url, new FileInputStream(zipfile), file.length.toInt, zipname)
	}
	
	def zip_send[T](url:String, inputStream:InputStream, zipSize:Int, zipname:String)(implicit mf: Manifest[T]): T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		val conn = new URL(url).openConnection().asInstanceOf[HttpsURLConnection]
		conn.addRequestProperty("Content-Type", "application/zip")
		conn.addRequestProperty("Content-Disposition", "attachment; filename=" + zipname + ";")
		conn.setFixedLengthStreamingMode(zipSize)
		conn.setSSLSocketFactory(socketFactory)
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
	}
	
	def receive_zip[T, W <: AnyRef](url:String, request:W, file:String)(implicit mf: Manifest[T]): Option[T] = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)

		val conn = new URL(url).openConnection().asInstanceOf[HttpsURLConnection]
		conn.addRequestProperty("Content-Type", "text/json")
		conn.setSSLSocketFactory(socketFactory)
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
	}
}

object FileUtil {
	@throws(classOf[IOException])
	def read(file: String): String = {
		val contents = new StringBuilder
		
		val fileReader = new BufferedReader(new FileReader(file))
		var line: String = null
	
		while( { line = fileReader.readLine(); line != null} ) {
			contents.append(line)
			contents.append("\n")
		}
		
		fileReader.close
		
		contents.toString.trim
	}
	
	@throws(classOf[IOException])
	def write(file: String, data: String): Unit = {
		val fileWriter = new FileWriter(file)
		fileWriter.write(data)
		fileWriter.close
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
}

object MetaFile {
	@throws(classOf[IOException])
	def load(path: String): scala.collection.Map[String,String] = {
		load(new FileReader(path))
	}
	
	@throws(classOf[IOException])
	def load(reader: Reader): scala.collection.Map[String,String] = {
		val meta = new mutable.ListMap[String,String]
		val bReader = new BufferedReader(reader)
		var line: String = null
	
		while( { line = bReader.readLine(); line != null} ) {
			val idx = line.indexOf(':')
			
			if(idx > 0) {
				meta += (line.substring(0, idx) -> line.substring(idx+1))
			}
		}
		
		bReader.close()
		
		meta
	}
}
