package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Actor._
import Language._
import Veredict._
import Status._

object TJU extends Actor with Log {
	val submit_url = "http://acm.tju.edu.cn/toj/submit_process.php"
	val status_url = "http://acm.tju.edu.cn/toj/status.php?user=" + Config.get("driver.tju.user", "omegaup")
	private val TableRegex = "(?si).*?<table width=100%[^>]+>(.*)</table>.*".r
	private val ColumnRegex = "<td>(.*?)</td>".r
	private val RuntimeRegex = "(\\d+)'(\\d+)\\.(\\d+)\"".r
	private var last_id = {
		val TableRegex(table) = Http.send_wait(status_url)
		
		ColumnRegex.findFirstMatchIn(table) match {
			case Some(x: Regex.Match) => Integer.parseInt(x.group(1))
			case _ => -1
		}
	}
	private var last_submission:Long = 0
	private val status_mapping = Map(
		"Received" ->			Status.Waiting,
		"Compiling"->			Status.Compiling,
		"Running"->				Status.Running
	)
	private val veredict_mapping = Map(
		"Compilation Error"->	Veredict.CompileError,
		"Runtime Error"->		Veredict.RuntimeError,
		"Wrong Answer"->		Veredict.WrongAnswer,
		"Time Limit Exceed"->	Veredict.TimeLimitExceeded,
		"Memory Limit Exceed"->	Veredict.MemoryLimitExceeded,
		"Output Limit Exceed"->	Veredict.OutputLimitExceeded,
		"Restricted Function"->	Veredict.RestrictedFunctionError,
		"Presentation Error"->	Veredict.PresentationError,
		"Accepted"->			Veredict.Accepted
	)
	
	def act() = {
		while(true) {
			receive {
				case Submission(run: Run) => {
					val time_delta = 10000 - (java.lang.System.currentTimeMillis() - last_submission)
					if(time_delta > 0)
						try { Thread.sleep(time_delta) }
					
					val id   = run.id
					val pid  = run.problem.remote_id.get
					val lang = run.language
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)
					
					info("TJU Submission {} for problem {}", id, pid)
		
					val post_data = Map(
						"user_id"  -> Config.get("driver.tju.user", "omegaup"),
						"passwd"   -> Config.get("driver.tju.password", "omegaup"),
						"prob_id"  -> pid,
						lang match {
							case Language.C => "language" -> "0"
							case Language.Cpp => "language" -> "1"
							case Language.Java => "language" -> "2"
							case Language.Pascal => "language" -> "4"
						},
						"source"   -> code
					)
					
					debug("TJU Sending data: {}", post_data)

					try {
						val data = Http.send_wait(submit_url, data = post_data)
						if(!data.contains("Your source code has been submitted")) {
							throw new Exception("Invalid response:\n" + data)
						}
						readVeredict(run)
					} catch {
						case e: Exception => {
							error("TJU Submission {} failed for problem {}", e, id, pid)
							
							run.status = Status.Ready
							run.veredict = Veredict.JudgeError
							run.memory = 0
							run.runtime = 0
							run.score = 0
							Manager.updateVeredict(run)
						}
					}
					
					last_submission = java.lang.System.currentTimeMillis()
				}
			}
		}
	}
	
	private def readVeredict(run: Run, triesLeft: Int = 5): Unit = {
		if (triesLeft == 0)
			throw new Exception("Retry limit exceeded")
			
		try { Thread.sleep(3000) }
		
		info("TJU Reading response, {} tries left", triesLeft)
		
		try {
			val TableRegex(table) = Http.send_wait(status_url)
		
			val data = ColumnRegex.findAllIn(table).matchData.take(8).map { (x) => x.group(1) }.toList
			
			val runId = Integer.parseInt(data(0))
			
			if (runId > last_id ) {
				last_id = runId
				
				run.status = Status.Ready
				run.veredict = Veredict.JudgeError
			
				status_mapping find { (k) => data(2).contains(k._1) } match {
					case Some((_, x: Status)) => {
						run.status = x
					}
					case None => veredict_mapping find { (k) => data(2).contains(k._1) } match {	
						case Some((_, x: Veredict)) => {
							run.veredict = x
						}
						case None => {
							error("{} does not contain a valid veredict", data(2))
							run.veredict = Veredict.JudgeError
						}
					}
				}
		
				val RuntimeRegex(minutes, seconds, fractions) = data(6)
				run.runtime = 60000 * minutes.toInt + 1000 * seconds.toInt + fractions.toInt * 10
				run.memory = data(7).substring(0, data(7).length - 1).toInt
				run.score = if(run.veredict == Veredict.Accepted) 1 else 0
			
				Manager.updateVeredict(run)
				
				if(run.status != Status.Ready)
					readVeredict(run)
			} else {
				readVeredict(run, triesLeft)
			}
		} catch {
			case e: IOException => {
				error("TJU communication error", e)
				readVeredict(run, triesLeft-1)
			}
		}
	}
}
