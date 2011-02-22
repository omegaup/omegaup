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
import Validador._

object OmegaUp extends Actor with Log {
	def act() = {
		while(true) {
			receive {
				case Submission(ejecucion: Ejecucion) => {
					val id   = ejecucion.id
					val pid  = ejecucion.problema.id
					val lang = ejecucion.lenguaje
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + ejecucion.guid)
					
					info("OU Submission {} for problem {}", id, pid)
					
					if (ejecucion.problema.validador == Validador.Literal)
						LiteralGrader.grade(ejecucion)
					else {
						val service = Manager.getRunner
					
						try {
							info("OU Compiling {}", id)
						
							val output = service.compile(
								new CompileInputMessage(lang.toString, List(code))
							)
						
							if(output.status == "ok") {
								val input = FileUtil.read(Config.get("problems.root", ".") + "/" + pid + "/inputname").trim
								val msg = new RunInputMessage(output.token.get, input = Some(input))
								val zip = new File(Config.get("grader.root", ".") + "/" + id + ".zip")
							
								service.run(msg, zip) match {
									case Some(x) => {
										if(
											service.input(input, new FileInputStream(zip), zip.length.toInt).status != "ok" ||
											service.run(msg, zip) != None
										) {
											throw new RuntimeException("OU unable to run submission " + id + ". giving up.")
										}
									}
									case _ => {}
								}
							
								ejecucion.problema.validador match {
									case Validador.Token => TokenGrader.grade(ejecucion)
									case Validador.TokenCaseless => TokenCaselessGrader.grade(ejecucion)
									case Validador.TokenNumeric => TokenNumericGrader.grade(ejecucion)
								}
							} else {
								val errorFile = new FileWriter(Config.get("grader.root", ".") + "/" + id + ".err")
								errorFile.write(output.error.get)
							
								ejecucion.estado = Estado.Listo
								ejecucion.veredicto = Veredicto.CompileError
								ejecucion.memoria = 0
								ejecucion.tiempo = 0
								ejecucion.puntuacion = 0
								Manager.updateVeredict(ejecucion)
							}
						} catch {
							case e: Exception => {
								error("OU Submission {} failed for problem {}", id, pid)
								error(e.getMessage)
								e.getStackTrace.foreach { st =>
									error(st.toString)
								}
							
								ejecucion.estado = Estado.Listo
								ejecucion.veredicto = Veredicto.JudgeError
								ejecucion.memoria = 0
								ejecucion.tiempo = 0
								ejecucion.puntuacion = 0
								Manager.updateVeredict(ejecucion)
							}
						} finally {
							Manager.addRunner(service)
						}
					}
				}
			}
		}
	}
}
