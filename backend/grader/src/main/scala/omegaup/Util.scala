package omegaup

import java.io._
import java.net._
import scala.xml._

object Database extends Object with Log with Using {
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
			susing (connection.createStatement) { statement =>
				rusing (statement.executeQuery(q)) { results =>
					results.next match {
						case true => Some(process(results))
						case false => None
					}
				}
			}
		} catch {
			case e: Exception => {
				susing (connection.createStatement) { statement =>
					rusing (statement.executeQuery(q)) { results =>
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
			susing (connection.createStatement) { statement =>
				statement.execute(q)
			}
		} catch {
			case e: Exception => {
				susing (connection.createStatement) { statement =>
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
		susing (connection.createStatement) { statement =>
			rusing (statement.executeQuery(q)) { results =>
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
