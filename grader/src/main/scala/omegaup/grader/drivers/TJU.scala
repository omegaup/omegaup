package omegaup.grader.drivers

import omegaup._
import omegaup.grader._
import java.io._
import scala.util.matching.Regex
import Lenguaje._
import Veredicto._
import Estado._

object TJU extends Object with Log {
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
	private val status_mapping = Map(
		"Received" ->			Estado.Espera,
		"Compiling"->			Estado.Compilando,
		"Running"->				Estado.Ejecutando
	)
	private val veredict_mapping = Map(
		"Compilation Error"->	Veredicto.CompileError,
		"Runtime Error"->		Veredicto.RuntimeError,
		"Wrong Answer"->		Veredicto.WrongAnswer,
		"Time Limit Exceed"->	Veredicto.TimeLimitExceeded,
		"Memory Limit Exceed"->	Veredicto.MemoryLimitExceeded,
		"Output Limit Exceed"->	Veredicto.OutputLimitExceeded,
		"Restricted Function"->	Veredicto.RestrictedFunctionError,
		"Presentation Error"->	Veredicto.PresentationError,
		"Accepted"->			Veredicto.Accepted
	)
	
	@throws(classOf[IOException])
	def submit(lang: Lenguaje, pid: Int, code: String): Unit = {
		debug("TJU Sending problem {}", pid)
		
		val post_data = Map(
			"user_id"  -> Config.get("driver.tju.user", "omegaup"),
			"passwd"   -> Config.get("driver.tju.password", "omegaup"),
			"prob_id"  -> pid,
			lang match {
				case Lenguaje.C => "language" -> "0"
				case Lenguaje.Cpp => "language" -> "1"
				case Lenguaje.Java => "language" -> "2"
				case Lenguaje.Pascal => "language" -> "4"
			},
			"source"   -> code
		)
		
		Http.send(submit_url, data = post_data, callback = (data) => {
			// assert(data.contains("Your source code has been submitted"))
			error(data)
			error(readVeredict.toString)
		})
	}
	
	private def readVeredict: (Estado, Option[Veredicto], Double, Int) = {
		try { Thread.sleep(3000) }
		
		info("Leyendo la respuesta de TJU")
		
		try {
			val TableRegex(table) = Http.send_wait(status_url)
		
			val data = ColumnRegex.findAllIn(table).matchData.take(8).map { (x) => x.group(1) }.toList
			
			val runId = Integer.parseInt(data(0))
			
			if (runId > last_id ) {
				last_id = runId
				
				var estado: Estado = Estado.Listo
				var veredicto: Option[Veredicto] = None
			
				status_mapping find { (k) => data(2).contains(k._1) } match {
					case Some((_, x: Estado)) => {
						estado = x
					}
					case None => veredict_mapping find { (k) => data(2).contains(k._1) } match {	
						case Some((_, x: Veredicto)) => {
							veredicto = Some(x)
						}
						case None => {
							error("{} no contiene un veredicto válido", data(2))
							veredicto = Some(Veredicto.JudgeError)
						}
					}
				}
		
				val RuntimeRegex(minutes, seconds, fractions) = data(6)
				val runtime = 60 * minutes.toInt + seconds.toInt + fractions.toInt / 100.0
				val memory = data(7).substring(0, data(7).length - 1).toInt
			
				(estado, veredicto, runtime, memory)
			} else {
				readVeredict
			}
		} catch {
			case e: IOException => {
				error(e.getMessage)
				readVeredict
			}
			case e: Exception => {
				error(e.getMessage)
				(Estado.Listo, Some(Veredicto.JudgeError), 0, 0)
			}
		}
	}
}
