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
					
					val (host, port) = Grader.getRunner
					
					Grader.addRunner(host, port)
				}
			}
		}
	}
}
