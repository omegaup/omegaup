package omegaup

import java.io._
import java.net._
import org.slf4j.{Logger, LoggerFactory}
import net.liftweb.json._
import javax.net.ssl._
import org.mortbay.io._
import org.mortbay.jetty.client.{HttpClient, ContentExchange}

object Config {
	private val props = new java.util.Properties()
	props.load(new java.io.FileInputStream("omegaup.properties"))
	
	def get[T](propname: String, default: T): T = {
		props.getProperty(propname) match {
			case null => default
			case ans:Any => default match {
				case x:String  => ans.asInstanceOf[T]
				case x:Int     => ans.toInt.asInstanceOf[T]
				case x:Boolean => (ans == "true").asInstanceOf[T]
				case _         => null.asInstanceOf[T]
			}
		}
	}
}

trait Log {
	private val log = LoggerFactory.getLogger(getClass)

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
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		val file = new File(zipfile)
		val conn = new URL(url).openConnection().asInstanceOf[HttpsURLConnection]
		conn.addRequestProperty("Content-Type", "application/zip")
		conn.addRequestProperty("Content-Disposition", "attachment; filename=" + zipname + ";")
		conn.setFixedLengthStreamingMode(file.length.toInt)
		conn.setSSLSocketFactory(socketFactory)
		conn.setDoOutput(true)
		val outputStream = conn.getOutputStream
		val inputStream = new FileInputStream(file)
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

object Http extends Object with Log {
	val client = new HttpClient()
	client.setConnectorType(HttpClient.CONNECTOR_SELECT_CHANNEL)
	val CookieRegex = """\s*([a-zA-Z0-9_-]+)\s*=\s*("(?:\\"|[^"])*"|[^;]*).*""".r
	
	try {
		client.start();
	} catch {
		case e: Exception => error(e.getMessage)
	}
	
	def send(url: String, callback: (String)=>Unit, data: Map[String,_] = null, headers: Map[String,String] = null, cookies: scala.collection.mutable.Map[String,String] = null): Unit = {
		val exchange = new ContentExchange() {
			@throws(classOf[IOException])
			protected override def onResponseComplete: Unit = {
				super.onResponseComplete()
				error(this.getResponseContent())
				callback(this.getResponseContent())
			}
			
			@throws(classOf[IOException])
			protected override def onResponseHeader(name: Buffer, value: Buffer): Unit = {
				if(cookies != null && name.toString == "Set-Cookie") {
					val CookieRegex(cn, cv) = value.toString
					cookies.put(cn, cv)
				}
			}
		}
		
		send_internal(exchange, url, data, headers, cookies)
	}
	
	def send_wait(url: String, data: Map[String,_] = null, headers: Map[String,String] = null, cookies: scala.collection.mutable.Map[String,String] = null): String = {
		var location: String = null
		val exchange = new ContentExchange() {
			@throws(classOf[IOException])
			protected override def onResponseHeader(name: Buffer, value: Buffer): Unit = {
				if(cookies != null && name.toString == "Set-Cookie") {
					val CookieRegex(cn, cv) = value.toString
					cookies.put(cn, cv)
				} else if(name.toString == "Location") {
					location = value.toString
				}
			}
		}
		
		send_internal(exchange, url, data, headers, cookies)
		
		exchange.waitForDone
		
		if(exchange.getResponseStatus == 301)
			location
		else
			exchange.getResponseContent
	}
	
	private def send_internal(exchange: ContentExchange, url: String, data: Map[String,_], headers: Map[String,String], cookies: scala.collection.mutable.Map[String,String]) = {
		exchange.addRequestHeader("User-Agent", "OmegaUp")
		if (headers != null)
			headers.foreach { case (k,v) => exchange.addRequestHeader(k, v) }
		if (cookies != null && !cookies.isEmpty)
			exchange.addRequestHeader("Cookie", (for ((k, v) <- cookies) yield URLEncoder.encode(k, "UTF-8") + "=" + URLEncoder.encode(v.toString, "UTF-8") ).mkString("; "))
		if (data == null) {
			exchange.setMethod("GET")
		} else {
			exchange.setMethod("POST")
			exchange.addRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8")

			exchange.setRequestContent(
				new ByteArrayBuffer(
					(for ((k, v) <- data) yield URLEncoder.encode(k, "UTF-8") + "=" + URLEncoder.encode(v.toString, "UTF-8") ).mkString("&")
				)
			)
		}
		exchange.setURL(url)

		client.send(exchange)
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
}
