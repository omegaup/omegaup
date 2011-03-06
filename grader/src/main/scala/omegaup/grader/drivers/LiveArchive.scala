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

object LiveArchive extends Actor with Log {
	val submit_url = "http://acmicpc-live-archive.uva.es/nuevoportal/mailer.php"
	val status_url = "http://acmicpc-live-archive.uva.es/nuevoportal/status.php?u=" + Config.get("driver.livearchive.user", "omegaup")
	
	val TableRegex = "(?si).*?<tr align=center>(.*?)(?=<(?:tr|/table)).*".r
	val RowRegex = "(?si)<td>&nbsp;([0-9]+)[^<]+<td><font size=2>[^<]+</font>[^<]+<td class=\"[^\"]+\">([^<]+)<td>([^<]+)<td>([^<]+).*".r
	private var last_id:Int = {
		val data = Http.send_wait(status_url)
		
		if(!data.contains("tr align=center")) {
			-1
		} else {
			val TableRegex(table) = data
			val RowRegex(rid, veredict, cpu, memory) = table
			
			rid.toInt
		}
	}
	private val status_mapping = Map(
		"Received" ->				Status.Waiting,
		"Compiling"->				Status.Compiling,
		"Running"->					Status.Running
	)
	private val veredict_mapping = Map(
		"Compile Error"->			Veredict.CompileError,
		"Runtime Error"->			Veredict.RuntimeError,
		"Wrong Answer"->			Veredict.WrongAnswer,
		"Time Limit Exceeded"->		Veredict.TimeLimitExceeded,
		"Memory Limit Exceeded"->	Veredict.MemoryLimitExceeded,
		"Output Limit Exceeded"->	Veredict.OutputLimitExceeded,
		"Restricted Function"->		Veredict.RestrictedFunctionError,
		"Presentation Error"->		Veredict.PresentationError,
		"Accepted"->				Veredict.Accepted
	)
	
	def act() = {
		while(true) {
			receive {
				case Submission(run: Run) => {
					val id   = run.id
					val pid  = run.problem.remote_id
					val lang = run.language
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)
					
					info("LA Submission {} for problem {}", id, pid)
		
					val post_data = Map(
						"paso"     -> "paso",
						"userid"   -> Config.get("driver.livearchive.password", "omegaup"),
						"problem"  -> pid,
						lang match {
							case Language.C => "language" -> "C"
							case Language.Cpp => "language" -> "C++"
							case Language.Java => "language" -> "Java"
							case Language.Pascal => "language" -> "Pascal"
						},
						"comment" -> "",
						"code"    -> code
					)
					
					debug("LA Sending data: {}", post_data)

					try {
						val data = Http.send_wait(submit_url, data = post_data)
						if(!data.contains("Problem submitted successfully")) {
							throw new Exception("Invalid response:\n" + data)
						}
						readVeredict(run)
					} catch {
						case e: Exception => {
							error("LA Submission {} failed for problem {}", e, id, pid)
							
							run.status = Status.Ready
							run.veredict = Veredict.JudgeError
							run.runtime = 0
							run.memory = 0
							run.score = 0
							Manager.updateVeredict(run)
						}
					}
				}
			}
		}
	}
	
	private def readVeredict(run: Run, triesLeft: Int = 5): Unit = {
		if (triesLeft == 0)
			throw new Exception("Retry limit exceeded")
			
		try { Thread.sleep(3000) }
		
		info("LA Reading response, {} tries left", triesLeft)
		
		try {
			val data = Http.send_wait(status_url)
	
			if(!data.contains("tr align=center")) {
				readVeredict(run, triesLeft)
			} else {
				val TableRegex(table) = data
				val RowRegex(rid, veredict, cpu, mem) = table
			
				val runId = rid.toInt
				if (runId > last_id ) {
					last_id = runId
				
					run.status = Status.Ready
			
					status_mapping find { (k) => veredict.contains(k._1) } match {
						case Some((_, x: Status)) => {
							run.status = x
						}
						case None => veredict_mapping find { (k) => veredict.contains(k._1) } match {	
							case Some((_, x: Veredict)) => {
								run.veredict = x
							}
							case None => {
								error("LA {} does not contain a valid veredict", data(2))
								run.veredict = Veredict.JudgeError
							}
						}
					}
		
					run.memory = if(mem == "Minimum") {
						0
					} else {
						mem.toInt
					}
					run.runtime = math.round(cpu.toFloat * 1000)
					run.score = if(run.veredict == Veredict.Accepted) 1 else 0
			
					Manager.updateVeredict(run)
					
					if(run.status != Status.Ready)
						readVeredict(run)
				} else {
					readVeredict(run, triesLeft)
				}
			}
		} catch {
			case e: IOException => {
				error("LA communication error", e)
				readVeredict(run, triesLeft-1)
			}
		}
	}
}
