package omegaup

import java.io._
import java.net._
import org.mortbay.io._
import org.mortbay.jetty.client.{HttpClient, ContentExchange}

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

object Database extends Object with Log {
	def using[Closeable <: {def close(): Unit}, B](closeable: Closeable)(getB: Closeable => B): B =
		try {
			getB(closeable)
		} finally {
			closeable.close()
		}

	def bmap[T](test: => Boolean)(block: => T): List[T] = {
		val ret = new scala.collection.mutable.ListBuffer[T]
		while(test) ret += block
		ret.toList
	}

	import java.sql._

	/** Executes the SQL and processes the result set using the specified function. */
	def query[B](sql: String)(process: ResultSet => B)(implicit connection: Connection): Option[B] = {
		debug(sql)
		using (connection.createStatement) { statement =>
			using (statement.executeQuery(sql)) { results =>
				results.next match {
					case true => Some(process(results))
					case false => None
				}
			}
		}
	}
	
	def execute(sql: String)(implicit connection: Connection): Unit = {
		debug(sql)
		using (connection.createStatement) { statement =>
			statement.execute(sql)
		}
	}

	/** Executes the SQL and uses the process function to convert each row into a T. */
	/*
	def queryEach[T](sql: String)(process: ResultSet => T)(implicit connection: Connection): List[T] =
		query(sql) { results =>
			bmap(results.next) {
				process(results)
			}
		}
	*/
}
