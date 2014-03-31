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

case class RunDetails(
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
	// A collection of subscribers.
		
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
		val server = new org.eclipse.jetty.server.Server()
		
		val broadcasterConnector = new org.eclipse.jetty.server.ServerConnector(server)
		broadcasterConnector.setPort(Config.get[Int]("broadcaster.port", 39613))
		server.setConnectors(List(broadcasterConnector).toArray)

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
