package omegaup.broadcaster

import java.io._
import java.util.zip._
import java.util.concurrent._
import javax.servlet._
import javax.servlet.http._
import org.eclipse.jetty.server.Request
import org.eclipse.jetty.websocket._
import org.eclipse.jetty.server.handler._
import net.liftweb.json._
import scala.collection.{mutable,immutable}
import omegaup._
import omegaup.data._

class Session(var connection: WebSocket.Connection, var user: Long, var contest: Int) {}
case class RunDetails(guid: String, runtime: Double, memory: Long, score: Double, contest_score: Double, status: String, veredict: String, submit_delay: Long, time: Long, language: String)
case class UpdateRunMessage(message: String, run: RunDetails)

object Broadcaster extends Object with Log with Using {
	// A collection of subscribers.
	val subscribers = new ConcurrentLinkedQueue[Session]()
		
	def hashdigest(algorithm: String, s: String): String = {
		val hexdigest = new StringBuffer

		for (c <- java.security.MessageDigest.getInstance(algorithm).digest(s.getBytes)) {
			val hex = Integer.toHexString(0xFF & c)
	                if (hex.length == 1) {
				hexdigest.append('0')
        		}
			hexdigest.append(hex)
		}

		return hexdigest.toString
	}
	
	def update(run: Run): Unit = {
		implicit val formats = Serialization.formats(NoTypeHints)
		val data = Serialization.write(
			UpdateRunMessage("/run/status/",
				RunDetails(
					guid = run.guid,
					runtime = run.runtime,
					memory = run.memory,
					score = run.score,
					contest_score = run.contest_score,
					status = run.status.toString,
					veredict = run.veredict.toString,
					submit_delay = run.submit_delay,
					time = run.time.getTime / 1000,
					language = run.language.toString
				)
			)
		)
		
		warn("Sending some JSON: {}", data)
		
		val it = subscribers.iterator
		while (it.hasNext) {
			val subscriber = it.next
			
			warn("Considering session {}, {} == {}, {}", subscriber.connection, subscriber.user, run.user, subscriber.contest)
			
			if (subscriber.user == run.user) {
				try {
					subscriber.connection.sendMessage(data)
				} catch {
					case e: IOException => {
						it.remove
					}
				}
			}
		}
	}

	def getUserId(request: HttpServletRequest): Int = {
		// Find user ID.
		val userId: String = request.getCookies match {
			case x: Array[Cookie] => {
				val tokens = x.filter(_.getName == "ouat")
				if (tokens.length == 1) {
					tokens(0).getValue
				} else {
					return -1
				}
			}

			case _ => return -1
		}

		val tokens = userId.split('-')

		if (tokens.length != 3) return -1

		val entropy = tokens(0)
		val user = tokens(1)

    		if (tokens(2) == hashdigest("SHA-256", Config.get("omegaup.md5.salt", "") + user + entropy)) {
			try {
				user.toInt
			} catch {
				case e: Exception => -1
			}
		} else {
			-1
		}
	}

	def init() = {
		// the handler
		val handler = new WebSocketHandler() {
			@throws(classOf[IOException])
			@throws(classOf[ServletException])
			override def handle(target: String, baseRequest: Request, request: HttpServletRequest, response: HttpServletResponse): Unit = {
				info("handle {}", target)

				target match {
					case "/broadcast/" => {
						try {
							val data = request.getReader.readLine

							val it = subscribers.iterator
							while (it.hasNext) {
        	    						try {
                							it.next.connection.sendMessage(data)
            							} catch {
									case e: IOException => {
										it.remove
									}
								}
							}

							response.setStatus(HttpServletResponse.SC_OK)
						} catch {
							case e: Exception => {
								response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR)
								warn(e.toString)
							}
						}

						baseRequest.setHandled(true)
					}

					case x: String => {
						if (x.contains("/events/")) {
							if (getUserId(request) == -1) {
								response.setStatus(HttpServletResponse.SC_UNAUTHORIZED)
								baseRequest.setHandled(true)
							} else {
								super.handle(target, baseRequest, request, response)
							}
						}
					}
				}
			}

			override def doWebSocketConnect(request: HttpServletRequest, protocol: String): WebSocket = {
				val userId = getUserId(request)
				val session = new Session(null, userId, -1)

				info("New connection for protocol {}, userID {}", protocol, userId)

				new WebSocket.OnTextMessage() {
					override def onOpen(connection: WebSocket.Connection): Unit = {
        					session.connection = connection
						subscribers.add(session)
					}

					override def onClose(code: Int, message: String): Unit = {
						info("Closed: {}", message)
						subscribers.remove(session)
					}

					override def onMessage(data: String): Unit = {
						debug("Message received: {}", data)
					}
				};
			}
		};

		val server = new org.eclipse.jetty.server.Server()
		
		val broadcasterConnector = new org.eclipse.jetty.server.nio.SelectChannelConnector()
		broadcasterConnector.setPort(Config.get[Int]("broadcaster.port", 39613))
		
		server.setConnectors(List(broadcasterConnector).toArray)
		
		server.setHandler(handler)
		server.start()
		
		info("Registering port {}", broadcasterConnector.getLocalPort())

		server
	}

	def main(args: Array[String]) = {
		// logger
		Logging.init()

		val server = init()
		
		Runtime.getRuntime.addShutdownHook(new Thread() {
			override def run() = {
				info("Shutting down")

				server.stop()
			}
		});
		
		server.join()
	}
}

