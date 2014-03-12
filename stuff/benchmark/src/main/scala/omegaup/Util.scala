package omegaup

import java.io._
import java.net._
import javax.net.ssl._
import scala.collection.immutable._
import scala.language.implicitConversions

import org.json4s._
import org.json4s.native._
import org.json4s.JsonDSL._

class Log(filename: String) extends Closeable {
	val writer = new PrintWriter(new FileWriter(filename))
	
	override def close() = {
		writer.close
	}

	def add(request: String, start: Long, time: Long) = {
		val threadId = Thread.currentThread.getId
		writer.print(s"$threadId $start $request $time\n")
	}
}

trait Using {
	def using[A, B <: Closeable] (closeable: B) (f: B => A): A =
		 try { f(closeable) } finally { closeable.close }

	def cusing[A, B <: HttpURLConnection] (disconnectable: B) (f: B => A): A =
		 try { f(disconnectable) } finally { disconnectable.disconnect }
}

trait Timer {
	def time[A <: Any] (request: String)(f: => A)(implicit log: Log): A = {
		val t0 = System.nanoTime
		try {
			f
		} finally {
			val t1: Long = System.nanoTime
			log.add(request, t1, t1 - t0)
		}
	}
}

object Api {
  implicit val formats = new DefaultFormats {
    override val dateFormat = new DateFormat {
      def format(d: java.util.Date) = ""
      def parse(s: String) = None
    }
  }

  def toJValue(doc: Any) = (Extraction.decompose(doc)(formats).noNulls)
}

object Http extends Object with Using with Timer {
	def post(url: String, data: Map[String,String])(implicit log: Log):String = {
		val postData = data.map({ x => URLEncoder.encode(x._1) + "=" + URLEncoder.encode(x._2) }).mkString("&")
		
		time(url) {
			cusing (new URL(url).openConnection().asInstanceOf[HttpURLConnection]) { conn => {
				conn.addRequestProperty("Content-Type", "application/x-www-form-urlencoded")
				conn.addRequestProperty("Connection", "close")
				conn.setFixedLengthStreamingMode(postData.length)
				conn.setDoOutput(true)
				conn.setDoInput(true)
				using (new PrintWriter(new OutputStreamWriter(conn.getOutputStream()))) { writer => {
					writer.print(postData)
				}}
				using (new BufferedReader(new InputStreamReader(conn.getInputStream()))) { reader => {
					reader.readLine
				}}
			}}
		}
	}
}
