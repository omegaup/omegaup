package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import java.util.concurrent._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Actor._
import Lenguaje._
import Veredicto._
import Estado._

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
	
	private val locks  = for (i <- 1 until 50) yield new Semaphore(1)
	private var lock_i = 0
	private val rids   = Array.ofDim[Long](50)
	private val ejecuciones = Array.ofDim[Ejecucion](50)
	
	private val cookies = new scala.collection.mutable.HashMap[String,String]
	private var logged_in = false
	private val status_mapping = Map(
		"Sent to judge" ->			Estado.Espera,
		"Received" ->				Estado.Espera,
		"In judge queue" ->			Estado.Espera,
		"Queued" ->					Estado.Espera,
		"Compiling" ->				Estado.Compilando,
		"Linking" ->				Estado.Compilando,
		"Running" ->				Estado.Ejecutando
	)
	private val veredict_mapping = Map(
		"Compilation error"->		Veredicto.CompileError,
		"Runtime error"->			Veredicto.RuntimeError,
		"Wrong answer"->			Veredicto.WrongAnswer,
		"Time limit exceeded"->		Veredicto.TimeLimitExceeded,
		"Memory limit exceeded"->	Veredicto.MemoryLimitExceeded,
		"Output limit exceeded"->	Veredicto.OutputLimitExceeded,
		"Restricted function"->		Veredicto.RestrictedFunctionError,
		"Presentation error"->		Veredicto.PresentationError,
		"Accepted"->				Veredicto.Accepted
	)
	
	def act() = {
		while(true) {
			receive {
				case Login => {
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
				case Submission(ejecucion: Ejecucion) => {
					locks(lock_i).acquire()
					
					val id   = ejecucion.id
					val pid  = ejecucion.problema.id_remoto
					val lang = ejecucion.lenguaje
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + ejecucion.guid)
					
					info("UVa Submission {} for problem {}", id, pid)
					
					if(!logged_in) {
						error("UVa not logged in")
						
						ejecucion.estado = Estado.Listo
						ejecucion.veredicto = Veredicto.JudgeError
						ejecucion.tiempo = 0
						ejecucion.memoria = 0
						ejecucion.puntuacion = 0
						Manager.updateVeredict(ejecucion)
					} else {
						val post_data = Map(
							"problemid" ->	"",
							"category" ->	"",
							"localid" ->	pid,
							lang match {
								case Lenguaje.C => "language" -> "1"
								case Lenguaje.Cpp => "language" -> "3"
								case Lenguaje.Java => "language" -> "2"
							},
							"code" ->		code
						)
					
						debug("UVa Sending data: {}", post_data)

						try {
							val ReceiveRegex(rid) = Http.send_wait(submit_url, data = post_data, cookies = cookies)
							
							debug("UVa received with id {}", rid)
							
							rids(lock_i) = rid.toInt
							ejecuciones(lock_i) = ejecucion
							
							veredictReader ! lock_i
						} catch {
							case e: Exception => {
								error("UVa Submission {} failed for problem {}", id, pid)
								error(e.getMessage)
								e.getStackTrace.foreach { st =>
									error(st.toString)
								}
								
								ejecucion.estado = Estado.Listo
								ejecucion.veredicto = Veredicto.JudgeError
								ejecucion.tiempo = 0
								ejecucion.memoria = 0
								ejecucion.puntuacion = 0
								Manager.updateVeredict(ejecucion)
							}
						}
					}
					
					lock_i = if(lock_i == 49) {
						0
					} else {
						lock_i + 1
					}
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
		
		try { Thread.sleep(3000) }
		
		info("UVa Reading response, {} tries left", triesLeft)
		
		try {
			val TableRegex(data) = Http.send_wait(status_url, cookies = cookies)
			
			RowRegex.findAllIn(data).
				map { (row) => { CellRegex.findAllIn(row).matchData.map { _.group(1) } .toList } }.
				map { (row) => (row, rids.findIndexOf { row(0) == _.toString } ) }.
				filter { (x) => x._2 != -1 }.
				foreach { case (row, id) => {
					var estado: Estado = Estado.Listo
					var veredicto: Veredicto = Veredicto.JudgeError
					
					if (row(3) == "") {
						 estado = Estado.Espera
					} else {
						status_mapping find { (k) => row(3).contains(k._1) } match {
							case Some((_, x: Estado)) => {
								estado = x
							}
							case None => veredict_mapping find { (k) => row(3).contains(k._1) } match {	
								case Some((_, x: Veredicto)) => {
									veredicto = x
								}
								case None => {
									error("UVa {} no contiene un veredicto válido", data(2))
									veredicto = Veredicto.JudgeError
								}
							}
						}
					}
					
					ejecuciones(id).estado = estado
					ejecuciones(id).veredicto = veredicto
					ejecuciones(id).puntuacion = if(ejecuciones(id).veredicto == Veredicto.Accepted) 1 else 0
					ejecuciones(id).tiempo = math.round(1000 * row(5).toDouble)
					ejecuciones(id).memoria = 0
					Manager.updateVeredict(ejecuciones(id))
					
					if(estado == Estado.Listo) {
						rids(id) = 0
						ejecuciones(id) = null
						
						locks(id).release
					}
				}}
			
			if( rids exists { _ != 0 } ) {
				readVeredict()
			}
		} catch {
			case e: IOException => {
				error("UVa communication error: {}", e.getMessage)
				error(e.getMessage)
				e.getStackTrace.foreach { st =>
					error(st.toString)
				}
				readVeredict(triesLeft-1)
			}
		}
	}
}
