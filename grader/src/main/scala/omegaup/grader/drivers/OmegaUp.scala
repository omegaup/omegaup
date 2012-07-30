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
import Validator._

object OmegaUp extends Actor with Log {
	@throws(classOf[FileNotFoundException])
	def createCompileMessage(run: Run, code: String): CompileInputMessage = {
		if (run.problem.validator == Validator.Custom) {
			List("c", "cpp", "py", "p", "rb").foreach(lang => {
				val validator = new File(Config.get("problems.root", "problems") + "/" + run.problem.alias + "/validator." + lang)
				
				if (validator.exists) {
					debug("OU Using custom validator {} for problem {}", validator.getCanonicalPath, run.problem.alias)
					return new CompileInputMessage(run.language.toString, List(code), Some(lang), Some(List(FileUtil.read(validator.getCanonicalPath))))
				}
			})
			
			throw new FileNotFoundException("OU Validator for problem " + run.problem.alias + " was set to 'custom', but no validator program was found.")
		} else {
			debug("OU Using {} validator for problem {}", run.problem.validator, run.problem.alias)
			new CompileInputMessage(run.language.toString, List(code))
		}
	}
	
	def act() = {
		debug("OmegaUp loaded")
		while(true) {
			receive {
				case Submission(run: Run) => {
					debug("OmegaUp submission!")
					val id = run.id
					val alias = run.problem.alias
					val lang = run.language
					val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)
					
					info("OU Submission {} for problem {}", id, alias)
					
					if (run.problem.validator == Validator.Literal)
						LiteralGrader.grade(run)
					else {
						val service = Manager.getRunner
					
						try {
							info("OU Compiling {}", id)
						
							val output = service.compile(createCompileMessage(run, code))
						
							if(output.status == "ok") {
								val input = FileUtil.read(Config.get("problems.root", "problems") + "/" + alias + "/inputname").trim
								val msg = new RunInputMessage(
									output.token.get,
									timeLimit = run.problem.time_limit match {
										case Some(x) => x / 1000.0f
										case _ => 1.0f
									},
									memoryLimit = run.problem.memory_limit match {
										case Some(x) => x.toInt
										case _ => 65535
									},
									outputLimit = Config.get("grader.memory_limit", 1024),
									input = Some(input)
								)
								val zip = new File(Config.get("grader.root", "grader") + "/" + id + ".zip")
							
								service.run(msg, zip) match {
									case Some(x) => {
										info("Received a message {}, trying to send input from {}", x, zip.getCanonicalPath)
										val inputZip = new File(Config.get("problems.root", "problems") + "/" + alias + "/cases.zip")
										if(
											service.input(input, new FileInputStream(inputZip), inputZip.length.toInt).status != "ok" ||
											service.run(msg, zip) != None
										) {
											throw new RuntimeException("OU unable to run submission " + id + ". giving up.")
										}
									}
									case _ => {}
								}
							
								run.problem.validator match {
									case Validator.Custom => CustomGrader.grade(run)
									case Validator.Token => TokenGrader.grade(run)
									case Validator.TokenCaseless => TokenCaselessGrader.grade(run)
									case Validator.TokenNumeric => TokenNumericGrader.grade(run)
								}
							} else {
								error("OU Compile error {}", output.error.get)

								val errorFile = new FileWriter(Config.get("grader.root", "grader") + "/" + id + ".err")
								errorFile.write(output.error.get)
								errorFile.close
							
								run.status = Status.Ready
								run.veredict = Veredict.CompileError
								run.memory = 0
								run.runtime = 0
								run.score = 0
								Manager.updateVeredict(run)
							}
						} catch {
							case e: Exception => {
								error("OU Submission {} failed for problem {}", e, id, alias)
                                                                error("Stack trace: {}", e.getStackTrace) 
							
								run.status = Status.Ready
								run.veredict = Veredict.JudgeError
								run.memory = 0
								run.runtime = 0
								run.score = 0
								Manager.updateVeredict(run)
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
