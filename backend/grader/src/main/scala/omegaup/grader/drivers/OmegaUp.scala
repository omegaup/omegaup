package omegaup.grader.drivers

import omegaup._
import omegaup.data._
import omegaup.grader._
import java.io._
import java.util.concurrent._
import scala.util.matching.Regex
import scala.collection.mutable.ListBuffer
import Language._
import Veredict._
import Status._
import Validator._

object OmegaUpDriver extends Driver with Log {
  override def run(run: Run, service: RunnerService): Run = {
    // If using the literal validator, we can skip the run.
    if (run.problem.validator == Validator.Literal) return run

    val id = run.id
    val alias = run.problem.alias
    val lang = run.language

    info("OU Compiling {} {} on {}", alias, id, service.name)

    run.status = Status.Compiling
    run.judged_by = Some(service.name)
    Manager.updateVeredict(run)

    val code = FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)
    val output = service.compile(createCompileMessage(run, code))
  
    if(output.status != "ok") {
      val errorFile = new FileWriter(Config.get("grader.root", "grader") + "/" + id + ".err")
      errorFile.write(output.error.get)
      errorFile.close
  
      run.status = Status.Ready
      run.veredict = Veredict.CompileError
      run.memory = 0
      run.runtime = 0
      run.score = 0

      return run
    }

    val input = FileUtil.read(
      Config.get("problems.root", "problems") + "/" + alias + "/inputname"
    ).trim
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
      outputLimit = run.problem.output_limit match {
        case Some(x) => x.toLong
        case _ => 10240
      },
      input = Some(input)
    )
  
    run.status = Status.Running
    Manager.updateVeredict(run)

    val zip = new File(Config.get("grader.root", "grader") + "/" + id + ".zip")
    service.run(msg, zip) match {
      case Some(x) => {
        info("Received a message {}, trying to send input from {}", x, zip.getCanonicalPath)
        val inputZip = new File(Config.get("problems.root", "problems"), alias + "/cases.zip")
        if(
          service.input(
            input,
            new FileInputStream(inputZip), inputZip.length.toInt
          ).status != "ok" ||
          service.run(msg, zip) != None
        ) {
          throw new RuntimeException("OU unable to run submission after sending input. giving up.")
        }
      }
      case _ => {}
    }

    // Finally return the run.
    run
  }

  override def grade(run: Run): Run = {
    run.problem.validator match {
      case Validator.Custom => CustomGrader.grade(run)
      case Validator.Literal => LiteralGrader.grade(run)
      case Validator.Token => TokenGrader.grade(run)
      case Validator.TokenCaseless => TokenCaselessGrader.grade(run)
      case Validator.TokenNumeric => TokenNumericGrader.grade(run)
    }
  }

  @throws(classOf[FileNotFoundException])
  private def createCompileMessage(run: Run, code: String): CompileInputMessage = {
    var validatorLang: Option[String] = None
    var validatorCode: Option[List[(String, String)]] = None

    if (run.problem.validator == Validator.Custom) {
      List("c", "cpp", "py", "p", "rb").map(lang => {
        (lang -> new File(
          Config.get("problems.root", "problems"),
          run.problem.alias + "/validator." + lang)
        )
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
                                          " was set to 'custom', but no validator program" +
                                          " was found.")
        }
      }
    } else {
      debug("OU Using {} validator for problem {}", run.problem.validator, run.problem.alias)
    }

    val codes = new ListBuffer[(String,String)]
    val interactiveRoot = new File(
      Config.get("problems.root", "problems"),
      run.problem.alias + "/interactive"
    )

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
}
