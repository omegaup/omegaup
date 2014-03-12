package omegaup.tools

import omegaup._

import org.json4s._
import org.json4s.native._
import org.json4s.JsonDSL._

import scala.collection.mutable.{MutableList, HashMap}

case class Problem(
	title: String,
	alias: String,
	validator: String,
	time_limit: String,
	memory_limit: String,
	visits: String,
	submissions: String,
	accepted: String,
	difficulty: Option[String],
	order: String,
	points: String,
	letter: String)

class Session(val domain: String, val username: String, password: String)(implicit log: Log) {
	val auth_token = {
		val json = Http.post("http://" + domain + "/api/user/login/",
			Map("usernameOrEmail" -> username,
			    "password" -> password))
		val value = parseJson(json)
		implicit val formats = Api.formats
		(value \ "auth_token").extract[String]
	}

	def openContest(contest: String) = {
		val json = Http.post("http://" + domain + "/api/contest/details/contest_alias/" + contest + "/", Map("auth_token" -> auth_token))
		val value = parseJson(json)
		implicit val formats = Api.formats
		(value \ "problems").extract[List[Problem]].map(_.alias)
	}

	def openProblem(contest: String, problem: String) = {
		Http.post("http://" + domain + "/api/problem/details/contest_alias/" + contest + "/problem_alias/" + problem + "/", Map("auth_token" -> auth_token, "lang" -> "es"))
	}

	def sendRun(contest: String, problem: String, language: String, source: String) = {
		val json = Http.post("http://" + domain + "/api/run/create/", Map("auth_token" -> auth_token, "contest_alias" -> contest, "problem_alias" -> problem, "language" -> language, "source" -> source))
		val value = parseJson(json)
		implicit val formats = Api.formats
		(value \ "guid").extract[String]
	}

	def scoreboard(contest: String) = {
		Http.post("http://" + domain + "/api/contest/scoreboard/contest_alias/" + contest + "/", Map("auth_token" -> auth_token))
	}

	def scoreboardEvents(contest: String) = {
		Http.post("http://" + domain + "/api/contest/scoreboardevents/contest_alias/" + contest + "/", Map("auth_token" -> auth_token))
	}

	def clarifications(contest: String) = {
		Http.post("http://" + domain + "/api/contest/clarifications/contest_alias/" + contest + "/offset/0/rowcount/20/", Map("auth_token" -> auth_token))
	}

	def runStatus(guid: String) = {
		Http.post("http://" + domain + "/api/run/status/run_alias/" + guid + "/", Map("auth_token" -> auth_token))
	}

	def createUser(username: String, password: String) = {
		val email = username + "@example.org"
		val json = Http.post("http://" + domain + "/api/user/create/", Map("auth_token" -> auth_token, "username" -> username, "email" -> email, "password" -> password))
		val value = parseJson(json)
		implicit val formats = Api.formats
		(value \ "problems").extract[List[Problem]].map(_.alias)
	}
}

class Client(session: Session, contest: String) extends Thread(session.username) {
	var running = true

	override def run(): Unit = {
		val problems = session.openContest(contest)
		val openedProblems = new HashMap[String, Boolean]
		problems.foreach(openedProblems += _ -> false)
		val rand = new java.util.Random
		while (running) {
			val p = rand.nextInt(100)
			if (p < 1) {
				session.clarifications(contest)
				session.scoreboard(contest)
				session.scoreboardEvents(contest)
			} else {
				val problem = problems(rand.nextInt(problems.length))
				if (!openedProblems(problem)) {
					session.openProblem(contest, problem)
					openedProblems(problem) = true
				}
				val guid = session.sendRun(contest, problem, "cat", rand.nextInt(1000000).toString)
				for (checks <- 0 until rand.nextInt(5) + 1) {
					Thread.sleep(1000)
					session.runStatus(guid)
				}
				if (p % 3 == 0) {
					session.scoreboard(contest)
					session.scoreboardEvents(contest)
				}
			}
			Thread.sleep(1000)
		}
	}
}

object Benchmark extends Object with Using {
	def connect(session: Session, contest: String, username: String)(implicit log: Log) = {
		val password = "qwertyuiop_" + username
		new Client(
			try {
				new Session(session.domain, username, password)
			} catch {
				case e: Exception => {
					session.createUser(username, password)
					new Session(session.domain, username, password)
				}
			},
			contest
		)
	}

	def main(args: Array[String]): Unit = {
		val domain = "localhost"
		val initialClients = 1
		val finalClients = 50
		val warmupTime = 30 * 1000
		val rampupTime = 5 * 60 * 1000
		val testTime = 5 * 60 * 1000
		val logfile = "benchmark.log"
		val username = "omegaup"
		val password = "omegaup"
		val contest = "benchmark"
		val prefix = contest + (System.nanoTime % 1000)

		using (new Log(logfile)) { l => {
			implicit val log = l
			val s = new Session(domain, username, password)
			val threads = new MutableList[Client]
			for (i <- 0 until initialClients) {
				threads += connect(s, contest, prefix + i)
			}
			System.out.println("Warmup...")
			threads.foreach(_.start)
			Thread.sleep(warmupTime)

			val rampupStart = System.currentTimeMillis
			for (i <- initialClients until finalClients) {
				val thread = connect(s, contest, prefix + i)
				System.out.print("Ramp up: " + (i + 1) + " clients running\r")
				System.out.flush
				threads += thread
				thread.start
				val nextSpawn = rampupStart + (i + 1 - initialClients) * rampupTime / (finalClients - initialClients) - System.currentTimeMillis
				if (nextSpawn > 0) {
					Thread.sleep(nextSpawn)
				}
			}
			System.out.println("\nTest time")
			Thread.sleep(testTime)
			threads.foreach(_.running = false)
			System.out.println("Stopping")
			threads.foreach(_.join)
			System.out.println("Done")
		}}
	}
}
