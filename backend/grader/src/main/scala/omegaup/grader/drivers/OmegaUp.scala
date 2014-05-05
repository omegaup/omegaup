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

    info("Compiling {} {} on {}", alias, id, service.name)

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
      debug = run.debug,
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

    val target = new File(Config.get("grader.root", "grader") + "/" + id + "/")
    FileUtil.deleteDirectory(target)
    target.mkdir
    val placer = new CasePlacer(target)

    info("Running {}({}) on {}", alias, id, service.name)
    var response = service.run(msg, placer)
    debug("Ran {} {}, returned {}", alias, id, response)
    if (response.status != "ok") {
      if (response.error.get ==  "missing input") {
        info("Received a missing input message, trying to send input from {} ({})", alias, service.name)
        val inputZip = new File(Config.get("problems.root", "problems"), alias + "/cases.zip")
        if(service.input(input, new FileInputStream(inputZip), inputZip.length.toInt).status != "ok") {
          throw new RuntimeException("Unable to send input. giving up.")
        }
        response = service.run(msg, placer)
        if (response.status != "ok") {
          error("Second try, ran {}({}) on {}, returned {}", alias, id, service.name, response)
          throw new RuntimeException("Unable to run submission after sending input. giving up.")
        }
      } else {
        throw new RuntimeException(response.error.get)
      }
    }

    info("Grading {} {}", alias, id)

    // Finally return the run.
    run
  }

  class CasePlacer(directory: File) extends Object with RunCaseCallback with Using with Log {
    def apply(filename: String, length: Long, stream: InputStream): Unit = {
      debug("Placing file {}({}) into {}", filename, length, directory)
      val target = new File(directory, filename)
      if (!target.getParentFile.exists) {
        target.getParentFile.mkdirs
      }
      using (new FileOutputStream(new File(directory, filename))) {
        FileUtil.copy(stream, _)
      }
    }
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
          debug("Using custom validator {} for problem {}",
                validator.getCanonicalPath,
                run.problem.alias)
          validatorLang = Some(lang)
          validatorCode = Some(List(("Main." + lang, FileUtil.read(validator.getCanonicalPath))))
        }

        case _ => {
          throw new FileNotFoundException("Validator for problem " + run.problem.alias +
                                          " was set to 'custom', but no validator program" +
                                          " was found.")
        }
      }
    } else {
      debug("Using {} validator for problem {}", run.problem.validator, run.problem.alias)
    }

    val codes = new ListBuffer[(String,String)]
    val interactiveRoot = new File(
      Config.get("problems.root", "problems"),
      run.problem.alias + "/interactive"
    )

    if (interactiveRoot.isDirectory) {
      debug("Using interactive mode problem {}", run.problem.alias)

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
