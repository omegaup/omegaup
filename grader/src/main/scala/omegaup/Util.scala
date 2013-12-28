package omegaup

import java.io._
import java.net._
import scala.xml._
import org.eclipse.jetty.io._
import org.eclipse.jetty.client.{HttpClient, ContentExchange}

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
				if(cookies != null && name.toString("US-ASCII") == "Set-Cookie") {
					val CookieRegex(cn, cv) = value.toString("US-ASCII")
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
				if(cookies != null && name.toString("US-ASCII") == "Set-Cookie") {
					val CookieRegex(cn, cv) = value.toString("US-ASCII")
					cookies.put(cn, cv)
				} else if(name.toString("US-ASCII") == "Location") {
					location = value.toString("US-ASCII")
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
	
	def build(query: String, params: Any*): String = {
		params.length match {
			case 0 => query
			case _ => {
				var pqi = 0
				var qi = -1
				var pi = 0
				val ans = new StringBuilder
				
				while( {qi = query.indexOf('?', pqi); qi != -1} ) {
					ans.append(query.substring(pqi, qi))
					
					ans.append(params(pi) match {
						case null => "null"
						case None => "null"
						case Some(x) => x match {
							case y: Int => y.toString
							case y: Long => y.toString
							case y: Float => y.toString
							case y: Double => y.toString
							case y: Boolean => if (y) "1" else "0"
							case y => {
								"'" +
								y.toString.replace("\\", "\\\\").replace("'", "''") +
								"'"
							}
						}
						case x: Int => x.toString
						case x: Long => x.toString
						case x: Float => x.toString
						case x: Double => x.toString
						case x: Boolean => if (x) "1" else "0"
						case x => {
							"'" +
							x.toString.replace("\\", "\\\\").replace("'", "''") +
							"'"
						}
					})
					
					pqi = qi + 1
					pi += 1
				}
				
				if(pqi < query.length)
					ans.append(query.substring(pqi))
				
				ans.toString
			}
		}
	}

	/** Executes the SQL and processes the result set using the specified function. */
	def query[B](sql: String, params: Any*)(process: ResultSet => B)(implicit connection: Connection): Option[B] = {
		val q = build(sql, params : _*)
		trace(q)
		try {
			using (connection.createStatement) { statement =>
				using (statement.executeQuery(q)) { results =>
					results.next match {
						case true => Some(process(results))
						case false => None
					}
				}
			}
		} catch {
			case _ => {
				using (connection.createStatement) { statement =>
					using (statement.executeQuery(q)) { results =>
						results.next match {
							case true => Some(process(results))
							case false => None
						}
					}
				}
			}
		}
	}
	
	def execute(sql: String, params: Any*)(implicit connection: Connection): Unit = {
		val q = build(sql, params : _*)
		trace(q)
		try {
			using (connection.createStatement) { statement =>
				statement.execute(q)
			}
		} catch {
			case _ => {
				using (connection.createStatement) { statement =>
					statement.execute(q)
				}
			}
		}
	}

	/** Executes the SQL and uses the process function to convert each row into a T. */
	def queryEach[T](sql: String, params: Any*)(process: ResultSet => T)(implicit connection: Connection): Iterable[T] = {
		val q = build(sql, params : _*)
		trace(q)
		val ret = new scala.collection.mutable.ListBuffer[T]
		using (connection.createStatement) { statement =>
			using (statement.executeQuery(q)) { results =>
				while (results.next) {
					ret += process(results)
				}
			}
		}
		ret
	}
}

class XmlWalker(xml: String) {
	val root = XML.loadString(xml)

	def get(path: String): String = {
		var node: NodeSeq = root
		
		for (element <- path.split("\\.")) {
			if (element.contains("=")) {
				val search = element.split("=")
				node = node.filter(x => (x \\ search(0)).text == search(1))
			} else {
				node = node \\ element
			}
		}
		
		return node.text.trim
	}
}
