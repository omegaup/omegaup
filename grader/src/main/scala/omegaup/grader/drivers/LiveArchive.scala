package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Actor._
import Lenguaje._
import Veredicto._
import Estado._

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
		"Received" ->				Estado.Espera,
		"Compiling"->				Estado.Compilando,
		"Running"->					Estado.Ejecutando
	)
	private val veredict_mapping = Map(
		"Compile Error"->			Veredicto.CompileError,
		"Runtime Error"->			Veredicto.RuntimeError,
		"Wrong Answer"->			Veredicto.WrongAnswer,
		"Time Limit Exceeded"->		Veredicto.TimeLimitExceeded,
		"Memory Limit Exceeded"->	Veredicto.MemoryLimitExceeded,
		"Output Limit Exceeded"->	Veredicto.OutputLimitExceeded,
		"Restricted Function"->		Veredicto.RestrictedFunctionError,
		"Presentation Error"->		Veredicto.PresentationError,
		"Accepted"->				Veredicto.Accepted
	)
	
	def act() = {
		while(true) {
			receive {
				case Submission(ejecucion: Ejecucion) => {
					val id   = ejecucion.id
					val pid  = ejecucion.problema.id_remoto
					val lang = ejecucion.lenguaje
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + ejecucion.guid)
					
					info("LA Submission {} for problem {}", id, pid)
		
					val post_data = Map(
						"paso"     -> "paso",
						"userid"   -> Config.get("driver.livearchive.password", "omegaup"),
						"problem"  -> pid,
						lang match {
							case Lenguaje.C => "language" -> "C"
							case Lenguaje.Cpp => "language" -> "C++"
							case Lenguaje.Java => "language" -> "Java"
							case Lenguaje.Pascal => "language" -> "Pascal"
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
						readVeredict(ejecucion)
					} catch {
						case e: Exception => {
							error("LA Submission {} failed for problem {}", id, pid)
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
			}
		}
	}
	
	private def readVeredict(ejecucion: Ejecucion, triesLeft: Int = 5): Unit = {
		if (triesLeft == 0)
			throw new Exception("Retry limit exceeded")
			
		try { Thread.sleep(3000) }
		
		info("LA Reading response, {} tries left", triesLeft)
		
		try {
			val data = Http.send_wait(status_url)
	
			if(!data.contains("tr align=center")) {
				readVeredict(ejecucion, triesLeft)
			} else {
				val TableRegex(table) = data
				val RowRegex(rid, veredict, cpu, mem) = table
			
				val runId = rid.toInt
				if (runId > last_id ) {
					last_id = runId
				
					ejecucion.estado = Estado.Listo
			
					status_mapping find { (k) => veredict.contains(k._1) } match {
						case Some((_, x: Estado)) => {
							ejecucion.estado = x
						}
						case None => veredict_mapping find { (k) => veredict.contains(k._1) } match {	
							case Some((_, x: Veredicto)) => {
								ejecucion.veredicto = x
							}
							case None => {
								error("LA {} no contiene un veredicto válido", data(2))
								ejecucion.veredicto = Veredicto.JudgeError
							}
						}
					}
		
					ejecucion.memoria = if(mem == "Minimum") {
						0
					} else {
						mem.toInt
					}
					ejecucion.tiempo = math.round(cpu.toFloat * 1000)
					ejecucion.puntuacion = if(ejecucion.veredicto == Veredicto.Accepted) 1 else 0
			
					Manager.updateVeredict(ejecucion)
					
					if(ejecucion.estado != Estado.Listo)
						readVeredict(ejecucion)
				} else {
					readVeredict(ejecucion, triesLeft)
				}
			}
		} catch {
			case e: IOException => {
				error("LA communication error: {}", e.getMessage)
				readVeredict(ejecucion, triesLeft-1)
			}
		}
	}
}
