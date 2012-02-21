package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import java.util.concurrent._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Actor._
import Language._
import Veredict._
import Status._

object UVa extends Actor with Log {
	val home_url   = "http://uva.onlinejudge.org/index.php"
	val login_url  = "http://uva.onlinejudge.org/index.php?option=com_comprofiler&task=login"
	val submit_url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=25&page=save_submission"
	val status_url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=9&limit=50&limitstart=0"
	
	val LoginFormRegex = "(?si).*<form action=\"http://uva.onlinejudge.org/index\\.php\\?option=com_comprofiler&amp;task=login\"(.*?)</form>.*".r
	val InputFormRegex = "name=\"([^\">]*)\"[^>]*value=\"([^\">]*)\"".r
	val ReceiveRegex = "(?si).*Submission.received.with.ID.([0-9]+)".r
	
	val TableRegex = "(?si).*?<!-- #col3: Main Content -->.*?<table(.*?)</table>.*".r
	val RowRegex = "(?si)<tr.*?</tr>".r
	val CellRegex = "(?si)<td.*?>(.*?)</td>".r

	private val mutex = new Semaphore(1)	
	private val locks  = for (i <- 1 until 50) yield new Semaphore(1)
	private var lock_i = 0
	private val rids   = Array.ofDim[Long](50)
	private val runes = Array.ofDim[Run](50)
	private val rretries = Array.ofDim[Int](50)
	
	private val cookies = new scala.collection.mutable.HashMap[String,String]
	private var logged_in = false
	private val status_mapping = Map(
		"Sent to judge" ->			Status.Waiting,
		"Received" ->				Status.Waiting,
		"In judge queue" ->			Status.Waiting,
		"Queued" ->					Status.Waiting,
		"Compiling" ->				Status.Compiling,
		"Linking" ->				Status.Compiling,
		"Running" ->				Status.Running
	)
	private val veredict_mapping = Map(
		"Compilation error"->		Veredict.CompileError,
		"Runtime error"->			Veredict.RuntimeError,
		"Wrong answer"->			Veredict.WrongAnswer,
		"Time limit exceeded"->		Veredict.TimeLimitExceeded,
		"Memory limit exceeded"->	Veredict.MemoryLimitExceeded,
		"Output limit exceeded"->	Veredict.OutputLimitExceeded,
		"Restricted function"->		Veredict.RestrictedFunctionError,
		"Presentation error"->		Veredict.PresentationError,
		"Accepted"->				Veredict.Accepted
	)
	
	def act() = {
		debug("UVa loaded")
		while(true) {
			info("UVa ready")
			receive {
				case Submission(run: Run) => {
					error("UVa Submission!")

					val id   = run.id
					val pid  = run.problem.remote_id.get
					val lang = run.language

					info("UVa Submission {} for problem {}", id, pid)
					mutex.acquire()
						val local_lock_i = lock_i 
						lock_i = if(lock_i == 49) {
							0
						} else {
							lock_i + 1
						}
					mutex.release()
					locks(local_lock_i).acquire()
					info("UVa lock acquired")
					
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)
					
					if(!logged_in) {
						error("UVa not logged in")
						
						run.status = Status.Ready
						run.veredict = Veredict.JudgeError
						run.runtime = 0
						run.memory = 0
						run.score = 0
						Manager.updateVeredict(run)
					} else {
						val post_data = Map(
							"problemid" ->	"",
							"category" ->	"",
							"localid" ->	pid,
							lang match {
								case Language.C => "language" -> "1"
								case Language.Cpp => "language" -> "3"
								case Language.Java => "language" -> "2"
								case Language.Pascal => "language" -> "4"
							},
							"code" ->		code
						)
					
						debug("UVa Sending data: {}", post_data)

						try {
							val ReceiveRegex(rid) = Http.send_wait(submit_url, data = post_data, cookies = cookies)
							
							debug("UVa received with id {}", rid)
							
							rids(local_lock_i) = rid.toInt
							runes(local_lock_i) = run
							rretries(local_lock_i) = 50
							
							veredictReader ! local_lock_i
						} catch {
							case e: Exception => {
								error("UVa Submission {} failed for problem {}", id, pid)
								error(e.getMessage)
								e.getStackTrace.foreach { st =>
									error(st.toString)
								}
								
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
				case Login => {
					info("UVa login")

					try {
						val LoginFormRegex(form: String) = Http.send_wait(home_url, cookies = cookies)
					
						val post_data = Map(
							"username" -> Config.get("driver.uva.user", "omegaup"),
							"passwd"   -> Config.get("driver.uva.password", "omegaup")
						) ++ InputFormRegex.findAllIn(form).matchData.map { (x) => x.group(1) -> x.group(2) }
					
						if(Http.send_wait(login_url, post_data, headers = Map("Referer"->home_url), cookies=cookies).startsWith("http://")) {
							logged_in = true
						}
					} catch {
						case e: Exception => {
							error("UVa communication failure: {}", e.getMessage)
						}
					}
					
					if(logged_in) {
						info("UVa logged in")
					} else {
						error("UVa login failure")
					}
				}
				case x => {
					error("Unknown thing {}", x)
				}
			}
		}
	}
	
	private val veredictReader: Actor = actor {
		while(true) {
			self.receive {
				case x: Int => {
					if (rids(x) != 0) readVeredict()
				}
			}
		}
	}
	
	private def readVeredict(triesLeft: Long = 5): Unit = {
		if (triesLeft == 0)
			throw new Exception("Retry limit exceeded")
		if (!(rids exists { _ != 0 }))
			return;
		
		try { Thread.sleep(10000) }
		
		info("UVa Reading response, {} tries left", triesLeft)
		
		try {
			val TableRegex(data) = Http.send_wait(status_url, cookies = cookies)
			
			RowRegex.findAllIn(data).
				map { (row) => { CellRegex.findAllIn(row).matchData.map { _.group(1) } .toList } }.
				map { (row) => (row, rids.findIndexOf { row(0) == _.toString } ) }.
				filter { (x) => x._2 != -1 }.
				foreach { case (row, id) => {
					var status: Status = Status.Ready
					var veredict: Veredict = Veredict.JudgeError
					
					if (row(3) == "") {
						status = Status.Waiting
					} else {
						status_mapping find { (k) => row(3).contains(k._1) } match {
							case Some((_, x: Status)) => {
								status = x
							}
							case None => veredict_mapping find { (k) => row(3).contains(k._1) } match {	
								case Some((_, x: Veredict)) => {
									veredict = x
								}
								case None => {
									error("UVa {} does not contain a valid veredict", data(2))
									veredict = Veredict.JudgeError
								}
							}
						}
					}
					
					rretries(id) -= 1

					if (rretries(id) <= 0) {
						status = Status.Ready
					}

					runes(id).status = status
					runes(id).veredict = veredict
					runes(id).score = if(runes(id).veredict == Veredict.Accepted) 1 else 0
					runes(id).runtime = math.round(1000 * row(5).toDouble)
					runes(id).memory = 0
					runes(id).problem.points match {
						case None => {}
						case Some(factor) => runes(id).contest_score = runes(id).score * factor
					}

					Manager.updateVeredict(runes(id))
					
					if(status == Status.Ready) {
						rids(id) = 0
						runes(id) = null
						
						locks(id).release
					}
				}}
			
			if( rids exists { _ != 0 } ) {
				readVeredict()
			}
		} catch {
			case e: IOException => {
				error("UVa communication error", e)
				readVeredict(triesLeft-1)
			}
		}
	}
}
