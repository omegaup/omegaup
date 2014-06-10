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
import omegaup.grader._

case class RunDetails(
	username: String,
	contest_alias: Option[String],
	alias: String,
	guid: String,
	runtime: Double,
	memory: Long,
	score: Double,
	contest_score: Option[Double],
	status: String,
	veredict: String,
	submit_delay: Long,
	time: Long,
	language: String
)
case class UpdateRunMessage(message: String, run: RunDetails)

class QueuedElement(val contest: String, val broadcast: Boolean, val targetUser: Long, val userOnly: Boolean) {}
class QueuedRun(contest: String, broadcast: Boolean, targetUser: Long, userOnly: Boolean, val ctx: RunContext)
	extends QueuedElement(contest, broadcast, targetUser, userOnly) {}
class QueuedMessage(contest: String, broadcast: Boolean, targetUser: Long, userOnly: Boolean, val message: String)
	extends QueuedElement(contest, broadcast, targetUser, userOnly) {}

object Broadcaster extends Object with Runnable with Log with Using {
	private val PathRE = """^/([a-zA-Z0-9_-]+)/?""".r
	// A collection of subscribers.
	private val subscribers = new mutable.HashMap[String, mutable.ArrayBuffer[BroadcasterSession]]
	private val subscriberLock = new Object
	private val PoisonPill = new QueuedElement(null, true, -1, false)
	private val queue = new LinkedBlockingQueue[QueuedElement]

	def subscribe(session: BroadcasterSession) = {
		subscriberLock.synchronized {
			if (!subscribers.contains(session.contest)) {
				subscribers.put(session.contest, new mutable.ArrayBuffer[BroadcasterSession])
			}
			subscribers(session.contest) += session
			info("Connected {}->{} ({})", session.user, session.contest, subscribers(session.contest).length)
		}
	}

	def unsubscribe(session: BroadcasterSession) = {
		if (session != null) {
			subscriberLock.synchronized {
				if (subscribers.contains(session.contest)) {
					subscribers(session.contest) -= session
					info("Disconnected {}->{} ({})", session.user, session.contest, subscribers(session.contest).length)
				}
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
	
	def update(ctx: RunContext): Unit = {
		ctx.run.contest match {
			case Some(contest) => {
				ctx.broadcastQueued
				queue.put(new QueuedRun(contest.alias, false, ctx.run.user.id, false, ctx))
			}
			case None => {
				ctx.finish
			}
		}
	}

	def broadcast(
		contest: String,
		message: String,
		broadcast: Boolean,
		targetUser: Long = -1,
		userOnly: Boolean = false
	): BroadcastOutputMessage = {
		queue.put(new QueuedMessage(contest, broadcast, targetUser, userOnly, message))
		new BroadcastOutputMessage(status = "ok")
	}

	private def runLoop(elm: QueuedElement): Unit = {
		val message = elm match {
			case m: QueuedRun => {
				m.ctx.broadcastDequeued
				val run = m.ctx.run
				implicit val formats = Serialization.formats(NoTypeHints)

				if (Config.get("grader.scoreboard_refresh.enable", true)) {
					m.ctx.trace(EventCategory.GraderRefresh) {
						try {
							info("Scoreboard refresh {}",
								Https.post[ScoreboardRefreshResponse](
									Config.get(
										"grader.scoreboard_refresh.url",
										"http://localhost/api/scoreboard/refresh/"
									),
									Map(
										"token" -> Config.get("grader.scoreboard_refresh.token", "secret"),
										"alias" -> elm.contest,
										"run" -> run.id.toString
									),
									runner = false
								)
							)
						} catch {
							case e: Exception => error("Scoreboard refresh", e)
						}
					}
				}

				m.ctx.finish

				Serialization.write(
					UpdateRunMessage("/run/update/",
						RunDetails(
							username = run.user.username,
							contest_alias = Some(elm.contest),
							alias = run.problem.alias,
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
			}

			case m: QueuedMessage => {
				m.message
			}
		}

		val notifyList = subscriberLock.synchronized {
			if (subscribers.contains(elm.contest)) {
				subscribers(elm.contest)
					.filter(subscriber =>
						(
							elm.broadcast ||
							subscriber.admin ||
							elm.targetUser == subscriber.user
						) && (
							!elm.userOnly ||
							!subscriber.admin
						)
					)
			} else {
				null
			}
		}

		if (notifyList != null)
			notifyList.foreach(_.send(message))
	}

	override def run(): Unit = {
		while (true) {
			try {
				val elm = queue.take
				if (elm == PoisonPill) {
					info("Broadcaster thread finished normally")
					return
				}
				runLoop(elm)
			} catch {
				case e: Exception => error("runLoop: {}", e)
			}
		}
	}

	class BroadcasterSession(val user: Int, val contest: String, val admin: Boolean, val session: Session) {
		def send(message: String): Unit = {
			if (!session.isOpen) return
			try {
				session.getRemote.sendString(message)
			} catch {
				case e: Exception => {
					error("Failed to send a message: {}", e)
					close
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
			info("Connecting from {}", sess.getRemoteAddress.getAddress)
			session = getSession(sess)
			if (session == null) {
				sess.close(new CloseStatus(1000, "forbidden"))
			} else {
				subscribe(session)
			}
		}

		private def getScoreboardSession(sess: Session, contest: String): BroadcasterSession = {
			val query = sess.getUpgradeRequest.getRequestURI.getQuery.split("=")
			if (query.length != 2) return null
			try {
				val response = Https.post[ContestRoleResponse](
					Config.get("grader.role.url", "http://localhost/api/contest/role/"),
					Map("token" -> query(1), "contest_alias" -> contest),
					runner = false
				)
				if (response.status == "ok") {
					return new BroadcasterSession(0, contest, response.admin, sess)
				}
			} catch {
				case e: Exception => {
					error("Error getting role", e)
				}
			}
			null
		}

		private def getUserId(request: UpgradeRequest): (Int, String) = {
			// Find user ID.
			val cookies = request.getCookies.filter(_.getName == "ouat")
			val userId = if (cookies.length == 1) {
				cookies(0).getValue
			} else {
				""
			}

			val tokens = userId.split('-')

			if (tokens.length != 3) return (-1, userId)

			val entropy = tokens(0)
			val user = tokens(1)

			if (tokens(2) == hashdigest("SHA-256", Config.get("omegaup.md5.salt", "") + user + entropy)) {
				try {
					(user.toInt, userId)
				} catch {
					case e: Exception => (-1, userId)
				}
			} else {
				(-1, userId)
			}
		}

		private def getSession(sess: Session): BroadcasterSession = {
			val contest = PathRE findFirstIn sess.getUpgradeRequest.getRequestURI.getPath match {
				case Some(PathRE(contest)) => {
					contest
				}
				case None => {
					null
				}
			}
			if (contest == null) return null
			if (sess.getUpgradeRequest.getRequestURI.getQuery != null) {
				return getScoreboardSession(sess, contest)
			}
			val (userId, token) = getUserId(sess.getUpgradeRequest)
			if (userId == -1) return null
			try {
				val response = Https.post[ContestRoleResponse](
					Config.get("grader.role.url", "http://localhost/api/contest/role/"),
					Map("auth_token" -> token, "contest_alias" -> contest),
					runner = false
				)
				if (response.status == "ok") {
					new BroadcasterSession(userId, contest, response.admin, sess)
				} else {
					null
				}
			} catch {
				case e: Exception => {
					error("Error getting role", e)
					null
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

		val thread = new Thread(this, "BroadcastThread")
		thread.start

		info("Broadcaster started")

		new ServiceInterface {
			override def stop(): Unit = {
				info("Broadcaster stopping")
				server.stop
				queue.put(PoisonPill)
			}
			override def join(): Unit = {
				server.join
				thread.join
				info("Broadcaster stopped")
			}
		}
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
		})
		
		server.join()
	}
}

/* vim: set noexpandtab: */
