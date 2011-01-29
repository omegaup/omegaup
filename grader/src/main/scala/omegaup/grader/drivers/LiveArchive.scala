package omegaup.grader.drivers

import omegaup._
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
			
			error("last_id from the beginning: {}", rid.toInt)
			
			rid.toInt
		}
	}
	private val status_mapping = Map(
		"Received" ->			Estado.Espera,
		"Compiling"->			Estado.Compilando,
		"Running"->				Estado.Ejecutando
	)
	private val veredict_mapping = Map(
		"Compile Error"->		Veredicto.CompileError,
		"Runtime Error"->		Veredicto.RuntimeError,
		"Wrong Answer"->		Veredicto.WrongAnswer,
		"Time Limit Exceed"->	Veredicto.TimeLimitExceeded,
		"Memory Limit Exceed"->	Veredicto.MemoryLimitExceeded,
		"Output Limit Exceed"->	Veredicto.OutputLimitExceeded,
		"Restricted Function"->	Veredicto.RestrictedFunctionError,
		"Presentation Error"->	Veredicto.PresentationError,
		"Accepted"->			Veredicto.Accepted
	)
	
	def act() = {
		while(true) {
			receive {
				case Submission(id: Int, lang: Lenguaje, pid: Int, code: String) => {
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

					val response = try {
						val data = Http.send_wait(submit_url, data = post_data)
						if(!data.contains("Problem submitted successfully")) {
							throw new Exception("Invalid response:\n" + data)
						}
						readVeredict(5)
					} catch {
						case e: Exception => {
							error("LA Submission {} failed for problem {}", id, pid)
							error(e.getMessage)
							(Estado.Listo, Some(Veredicto.JudgeError), 0, 0)
						}
					}
					
					info("LA Submission {} finished with veredict {}", id, response)
				}
			}
		}
	}
	
	@throws(classOf[IOException])
	private def readVeredict(triesLeft: Int): (Estado, Option[Veredicto], Double, Int) = {
		if (triesLeft == 0)
			throw new Exception("Retry limit exceeded")
			
		try { Thread.sleep(3000) }
		
		info("LA Reading response, {} tries left", triesLeft)
		
		try {
			val data = Http.send_wait(status_url)
	
			if(!data.contains("tr align=center")) {
				readVeredict(triesLeft)
			} else {
				val TableRegex(table) = data
				val RowRegex(rid, veredict, cpu, mem) = table
			
				val runId = rid.toInt
				if (runId > last_id ) {
					last_id = runId
				
					var estado: Estado = Estado.Listo
					var veredicto: Option[Veredicto] = None
			
					status_mapping find { (k) => veredict.contains(k._1) } match {
						case Some((_, x: Estado)) => {
							estado = x
						}
						case None => veredict_mapping find { (k) => veredict.contains(k._1) } match {	
							case Some((_, x: Veredicto)) => {
								veredicto = Some(x)
							}
							case None => {
								error("LA {} no contiene un veredicto válido", data(2))
								veredicto = Some(Veredicto.JudgeError)
							}
						}
					}
		
					val memory = if(mem == "Minimum") {
						0
					} else {
						mem.toInt
					}
			
					(estado, veredicto, cpu.toFloat, memory)
				} else {
					readVeredict(triesLeft)
				}
			}
		} catch {
			case e: IOException => {
				error("LA communication error: {}", e.getMessage)
				readVeredict(triesLeft-1)
			}
		}
	}
}
