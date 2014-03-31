package omegaup.broadcaster

import java.io._
import java.util.zip._
import java.util.concurrent._
import javax.servlet._
import javax.servlet.http._
import org.eclipse.jetty.websocket.api._
import org.eclipse.jetty.websocket.servlet._
import org.eclipse.jetty.websocket.server.WebSocketHandler
import org.eclipse.jetty.server._
import org.eclipse.jetty.servlet._
import net.liftweb.json._
import scala.collection.{mutable,immutable}
import scala.collection.JavaConversions._
import omegaup._
import omegaup.data._

case class RunDetails(
	username: String,
	contest_alias: Option[String],
	guid: String,
	runtime: Double,
	memory: Long,
	score: Double,
	contest_score: Double,
	status: String,
	veredict: String,
	submit_delay: Long,
	time: Long,
	language: String
)
case class UpdateRunMessage(message: String, run: RunDetails)

object Broadcaster extends Object with Log with Using {
	private val PathRE = """^/([a-zA-Z0-9_-]+)/?""".r
	// A collection of subscribers.
	private val subscribers = new mutable.HashMap[String, mutable.ArrayBuffer[BroadcasterSession]]
	private val subscriberLock = new Object

	def subscribe(session: BroadcasterSession) = {
		if (!subscribers.contains(session.contest)) {
			subscribers.put(session.contest, new mutable.ArrayBuffer[BroadcasterSession])
		}
		subscribers(session.contest) += session
		info("Connected {} {}", session.user, session.contest)
	}

	def unsubscribe(session: BroadcasterSession) = {
		if (session != null) {
			if (subscribers.contains(session.contest)) {
				subscribers(session.contest) -= session
				info("Disconnected {} {}", session.user, session.contest)
			}
		}
	}
		
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
		val contest = run.contest match {
			case Some(c) => c.alias
			case None => null
		}
		info("Updating run {} {}", contest, subscribers)
		if (contest == null || !subscribers.contains(contest)) return

		val data = Serialization.write(
			UpdateRunMessage("/run/status/",
				RunDetails(
					username = run.user.username,
					contest_alias = Some(contest),
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

		for (subscriber <- subscribers(contest)) {
			if (run.user.id == subscriber.user || subscriber.user == Config.get("grader.broadcaster.root", 1)) {
				if (!subscriber.send(data)) {
					subscriber.close
				}
			}
		}
	}

	def getUserId(request: UpgradeRequest): Int = {
		// Find user ID.
		val cookies = request.getCookies.filter(_.getName == "ouat")
		val userId = if (cookies.length == 1) {
			cookies(0).getValue
		} else {
			""
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

	class BroadcasterSession(val user: Int, val contest: String, val session: Session) {
		def send(message: String): Boolean = {
			if (!session.isOpen) return false
			try {
				session.getRemote.sendString(message)
				true
			} catch {
				case e: IOException => {
					error("Failed to send a message: {}", e)
					false
				}
			}
		}

		def close(): Unit = {
			if (!session.isOpen) return
			try {
				session.close(1000, "done")
			} catch {
				case e: Exception => {
					error("Failed to close the socket: {}", e)
				}
			}
		}

		def isOpen() = session.isOpen
	}

	class BroadcasterSocket extends WebSocketAdapter with Log {
		private var session: BroadcasterSession = null

		override def onWebSocketConnect(sess: Session): Unit = {
			val userId = getUserId(sess.getUpgradeRequest)
			if (userId == -1) {
				sess.close(new CloseStatus(1000, "forbidden"))
			} else {
				PathRE findFirstIn sess.getUpgradeRequest.getRequestURI.getPath match {
					case Some(PathRE(contest)) => {
						session = new BroadcasterSession(userId, contest, sess)
						subscribe(session)
					}
					case None => {
						sess.close(new CloseStatus(1000, "forbidden"))
					}
				}
			}
		}

		override def onWebSocketText(message: String): Unit = {
			if (session == null || !session.isOpen) return
			debug("Received {}", message)
		}

		override def onWebSocketClose(statusCode: Int, reason: String): Unit = {
			info("Closed {} {}", statusCode, reason)
			unsubscribe(session)
			if (session == null || !session.isOpen) return
		}

		override def onWebSocketError(cause: Throwable): Unit = {
			info("Error {}", cause)
			unsubscribe(session)
			if (session == null || !session.isOpen) return
		}
	}

	def init() = {
		val server = new org.eclipse.jetty.server.Server()
		
		val broadcasterConnector = new org.eclipse.jetty.server.ServerConnector(server)
		broadcasterConnector.setPort(Config.get("broadcaster.port", 39613))
		server.addConnector(broadcasterConnector)

		val creator = new WebSocketCreator() {
			override def createWebSocket(req: ServletUpgradeRequest, resp: ServletUpgradeResponse): Object = {
				resp.setAcceptedSubProtocol("com.omegaup.events")
				new BroadcasterSocket
			}
		}

		server.setHandler(new WebSocketHandler() {
			override def configure(factory: WebSocketServletFactory): Unit = {
				factory.setCreator(creator)
			}
		})

		server.start()
		
		info("Registering port {}", broadcasterConnector.getLocalPort)

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
