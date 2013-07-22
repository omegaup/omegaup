package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import java.util.concurrent._
import scala.util.matching.Regex
import scala.actors.Actor
import scala.actors.Scheduler
import scala.actors.Actor._
import scala.collection.mutable.ListBuffer
import Language._
import Veredict._
import Status._
import Validator._

object OmegaUp extends Actor with Log {
	@throws(classOf[FileNotFoundException])
	def createCompileMessage(run: Run, code: String): CompileInputMessage = {
		var validatorLang: Option[String] = None
		var validatorCode: Option[List[(String, String)]] = None

		if (run.problem.validator == Validator.Custom) {
			List("c", "cpp", "py", "p", "rb").map(lang => {
				(lang -> new File(Config.get("problems.root", "problems") + "/" + run.problem.alias + "/validator." + lang))
			}).find(_._2.exists) match {
				case Some((lang, validator)) => {
					debug("OU Using custom validator {} for problem {}",
					      validator.getCanonicalPath,
					      run.problem.alias)
					validatorLang = Some(lang)
					validatorCode = Some(List(("Main." + lang, FileUtil.read(validator.getCanonicalPath))))
				}

				case _ => {
					throw new FileNotFoundException("OU Validator for problem " + run.problem.alias +
				                                        " was set to 'custom', but no validator program was found.")
				}
			}
		} else {
			debug("OU Using {} validator for problem {}", run.problem.validator, run.problem.alias)
		}

		val codes = new ListBuffer[(String,String)]
		val interactiveRoot = new File(Config.get("problems.root", "problems") + "/" + run.problem.alias + "/interactive")

		if (interactiveRoot.isDirectory) {
			debug("OU Using interactive mode problem {}", run.problem.alias)

			val unitNameFile = new File(interactiveRoot, "unitname")
			if (!unitNameFile.isFile) {
				throw new FileNotFoundException(unitNameFile.getCanonicalPath)
			}

			val langDir = new File(interactiveRoot, run.language.toString)
			if (!langDir.isDirectory) {
				throw new FileNotFoundException(langDir.getCanonicalPath)
			}

			langDir
				.list
				.map(new File(langDir, _))
				.filter(_.isFile)
				.foreach(file => { codes += file.getName -> FileUtil.read(file.getCanonicalPath) })

			val unitName = FileUtil.read(unitNameFile.getCanonicalPath)
			codes += unitName + "." + run.language.toString -> code
	
			if (codes.size < 2) {
				throw new FileNotFoundException(langDir.getCanonicalPath)
			}
		} else {
			codes += "Main." + run.language.toString -> code
		}

		new CompileInputMessage(run.language.toString,
		                        codes.result,
		                        validatorLang,
		                        validatorCode)
	}

	def act() = {
		debug("OmegaUp loaded")
		loop {
			react {
				case Submission(run: Run) => {
					info("OU Submission {} for problem {}", run.id, run.problem.alias)
					
					if (run.problem.validator == Validator.Literal) {
						Scheduler.execute(LiteralGrader.grade(run))
					} else {
						Scheduler.execute(grade(run))
					}
				}
			}
		}
	}

	def grade(run: Run): Unit = {
		// Blocks until a runner gets in the queue.
		val service = Manager.getRunner
		val id = run.id
		val alias = run.problem.alias
		val lang = run.language
		var shouldRequeue = true
	
		try {
			info("OU Compiling {}", id)

			val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)		
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

				if (output.error.get.contains("ptrace(PTRACE_GETREGS")) {
					error("Retrying")
					Manager.grade(run.id)
				} else {
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
			}
		} catch {
			case e: java.net.ConnectException => {
				shouldRequeue = false
				
				error("OU Submission {} failed for problem {} - Runner unavailable", e, id, alias)
				
				run.status = Status.Ready
				run.veredict = Veredict.JudgeError
				run.memory = 0
				run.runtime = 0
				run.score = 0
				Manager.updateVeredict(run)
			}
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
			if (shouldRequeue) {
				Manager.addRunner(service)
			} else {
				service match {
					case proxy: omegaup.runner.RunnerProxy => Manager.deregister(proxy.hostname, proxy.port)
					case _ => {}
				}
				Manager.grade(run.id)
			}
		}
	}
}
