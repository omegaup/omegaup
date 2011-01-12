package openjuan

import java.io._
import java.net._
import org.slf4j.{Logger, LoggerFactory}
import net.liftweb.json._
import javax.net.ssl._

object Config {
	private val props = new java.util.Properties()
	props.load(new java.io.FileInputStream("openjuan.properties"))
	
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
	
	def send[W <: AnyRef, T](url:String, request:W)(implicit mf: Manifest[T]):T = {
		debug("Requesting {}", url)
		
		implicit val formats = Serialization.formats(NoTypeHints)
		
		val conn = new URL(url).openConnection().asInstanceOf[HttpsURLConnection]
		conn.setDoOutput(true)
		conn.setSSLSocketFactory(socketFactory)
		val writer = new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))
		Serialization.write[W, PrintWriter](request, writer)
		writer.close()
		
		Serialization.read[T](new InputStreamReader(conn.getInputStream()))
	}
}
