package omegaup.grader.drivers

import omegaup._
import omegaup.grader._
import java.io._
import java.util.concurrent._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Actor._
import Lenguaje._
import Veredicto._
import Estado._

object OmegaUp extends Actor with Log {
	def act() = {
		while(true) {
			receive {
				case Submission(id: Int, lang: Lenguaje, pid: Int, code: String) => {
					info("OU Submission {} for problem {}", id, pid)
					
					val (host, port) = Manager.getRunner
					val url = "https://" + host + ":" + port
					
					try {
						val output = Https.send[CompileOutputMessage, CompileInputMessage](url + "/compile/",
							new CompileInputMessage(lang.toString, List(code))
						)
						
						if(output.status == "ok") {
							val input = FileUtil.read(Config.get("problems.root", ".") + "/" + pid + "/inputname").trim
							val msg = new RunInputMessage(output.token.get, input = Some(input))
							val zip = Config.get("grader.root", ".") + "/" + id + ".zip"
							
							Https.receive_zip[RunOutputMessage, RunInputMessage](url + "/run/", msg, zip) match {
								case Some(x) => {
									if(Https.zip_send[InputOutputMessage](url + "/input/", Config.get("problems.root", ".") + "/" + pid + "/cases.zip", input).status != "ok" || Https.receive_zip[RunOutputMessage, RunInputMessage](url + "/run/", msg, zip) != None) {
										throw new RuntimeException("OU unable to run submission " + id + ". giving up.")
									}
								}
								case _ => {}
							}
							
							Manager.updateVeredict(id, Estado.Listo, Some(Veredicto.Accepted), 1, 1, 1)
						} else {
							Manager.updateVeredict(id, Estado.Listo, Some(Veredicto.CompileError), 0, 0, 0, output.error)
						}
					} catch {
						case e: Exception => {
							error("OU Submission {} failed for problem {}", id, pid)
							error(e.getMessage)
							Manager.updateVeredict(id, Estado.Listo, Some(Veredicto.JudgeError), 0, 0, 0)
						}
					}
					
					Manager.addRunner(host, port)
				}
			}
		}
	}
}
