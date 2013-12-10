package omegaup.runner

import omegaup._
import omegaup.data._

object Sandbox extends Object with Log with Using {
  def compile[A](lang: String,
                 inputFiles: TraversableOnce[String],
                 chdir: String = "",
                 outputFile: String = "",
                 errorFile: String = "",
                 metaFile: String = "") (callback: Int => A) = {
    val sandbox = Config.get("runner.sandbox.path", ".") + "/box"
    val profile = Config.get("runner.sandbox.path", ".") + "/profiles"
    val runtime = Runtime.getRuntime

    val commonParams = List(
      "-c", chdir,
      "-q",
      "-o", outputFile,
      "-M", metaFile,
      "-r", errorFile,
      "-t", Config.get("java.compile.time_limit", "30"),
      "-w", Config.get("java.compile.time_limit", "30")
    )

    val params = lang match {
      case "java" =>
        List(sandbox, "-S", profile + "/javac") ++
        commonParams ++
        List("--", Config.get("java.compiler.path", "/usr/bin/javac"), "-J-Xmx512M") ++
        inputFiles
      case "c" =>
        List(sandbox, "-S", profile + "/gcc") ++
        commonParams ++
        List("--", Config.get("c.compiler.path", "/usr/bin/gcc"), "-std=c99", "-O2", "-lm") ++
        inputFiles
      case "cpp" =>
        List(sandbox, "-S", profile + "/gcc") ++
        commonParams ++
        List("--", Config.get("cpp.compiler.path", "/usr/bin/g++"), "-O2", "-lm") ++
        inputFiles
      case "p" =>
        List(sandbox, "-S", profile + "/fpc") ++
        commonParams ++
        List(
          "--",
          Config.get("p.compiler.path", "/usr/bin/fpc"),
          "-Tlinux",
          "-O2",
          "-Mobjfpc",
          "-Sc",
          "-Sh"
        ) ++
        inputFiles
      case "py" =>
        List(sandbox, "-S", profile + "/pyc") ++
        commonParams ++
        List("--", Config.get("py.compiler.path", "/usr/bin/python"), "-m", "py_compile") ++
        inputFiles
      case "kj" =>
        List(sandbox, "-S", profile + "/kc") ++
        commonParams ++
        List(
          "--",
          Config.get("kcl.compiler.path", "/usr/bin/kcl"),
          "-lj",
          "-o",
          "Main.kx",
          "-c"
        ) ++
        inputFiles
      case "kp" =>
        List(sandbox, "-S", profile + "/kc") ++
        commonParams ++
        List(
          "--",
          Config.get("kcl.compiler.path", "/usr/bin/kcl"),
          "-lp",
          "-o",
          "Main.kx",
          "-c"
        ) ++
        inputFiles
      case "hs" =>
        List(sandbox, "-S", profile + "/ghc") ++
        commonParams ++
        List("--", Config.get("ghc.compiler.path", "/usr/bin/ghc"), "-O2", "-o", "Main") ++
        inputFiles
      case _ => null
    }

    debug("Compile {}", params.mkString(" "))

    pusing (runtime.exec(params.toArray)) { process => {
      if (process != null) {
        callback(process.waitFor)
      } else {
        callback(-1)
      }
    }}
  }

  def run(message: RunInputMessage,
          lang: String,
          logTag: String = "Run",
          extraParams: TraversableOnce[String] = List[String](),
          chdir: String = "",
          inputFile: String = "",
          outputFile: String = "",
          errorFile: String = "",
          metaFile: String = "",
          originalInputFile: Option[String] = None,
          runMetaFile: Option[String] = None) = {
    val sandbox = Config.get("runner.sandbox.path", ".") + "/box"
    val profile = Config.get("runner.sandbox.path", ".") + "/profiles"
    val runtime = Runtime.getRuntime

    var timeLimit = message.timeLimit
    if (lang == "java" || lang == "p") {
      timeLimit += 1
    }

    var commonParams = List(
      "-c", chdir,
      "-q",
      "-i", inputFile,
      "-o", outputFile,
      "-M", metaFile,
      "-r", errorFile,
      "-t", timeLimit.toString,
      "-w", (timeLimit + 5).toString,
      "-O", message.outputLimit.toString
    ) ++ (originalInputFile match {
      case Some(file) => List("-P", file)
      case None => List()
    }) ++ (runMetaFile match {
      case Some(file) => List("-D", file)
      case None => List()
    })

    val params = (lang match {
      case "java" =>
        List(sandbox, "-S", profile + "/java") ++
        commonParams ++
        List("--", "/usr/bin/java", "-Xmx" + message.memoryLimit + "k", "Main")
      case "c" =>
        List(sandbox, "-S", profile + "/c") ++
        commonParams ++
        List("-m", message.memoryLimit.toString, "--", "./a.out")
      case "cpp" =>
        List(sandbox, "-S", profile + "/c") ++
        commonParams ++
        List("-m", message.memoryLimit.toString, "--", "./a.out")
      case "p" =>
        List(sandbox, "-S", profile + "/p") ++
        commonParams ++
        List("-m", message.memoryLimit.toString, "-n", "--", "./Main")
      case "py" =>
        List(sandbox, "-S", profile + "/py") ++
        commonParams ++
        List("-m", message.memoryLimit.toString, "-n", "--", "/usr/bin/python", "Main.py")
      case "kp" =>
        List(sandbox, "-S", profile + "/kx") ++
        commonParams ++
        List(
          "--",
          Config.get("karel.runtime.path", "/usr/bin/karel"),
          "/dev/stdin",
          "-oi",
          "-q",
          "-p2",
          "Main.kx"
        )
      case "kj" =>
        List(sandbox, "-S", profile + "/kx") ++
        commonParams ++
        List(
          "--",
          Config.get("karel.runtime.path", "/usr/bin/karel"),
          "/dev/stdin",
          "-oi",
          "-q",
          "-p2",
          "Main.kx"
        )
      case "hs" =>
        List(sandbox, "-S", profile + "/hs") ++
        commonParams ++
        List("-m", message.memoryLimit.toString, "--", "./Main")
    }) ++ extraParams

    debug("{} {}", logTag, params.mkString(" "))
    pusing (runtime.exec(params.toArray)) { process =>
      if (process != null) process.waitFor
    }
  }
}
