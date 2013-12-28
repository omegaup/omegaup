package omegaup.runner

import java.io.File

import omegaup._
import omegaup.data._

trait Sandbox {
  def compile[A](lang: String,
                 inputFiles: TraversableOnce[String],
                 chdir: String = "",
                 outputFile: String = "",
                 errorFile: String = "",
                 metaFile: String = "") (callback: Int => A): A

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
          runMetaFile: Option[String] = None): Unit
}

object Box extends Object with Sandbox with Log with Using {
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

object Minijail extends Object with Sandbox with Log with Using {
  def compile[A](lang: String,
                 inputFiles: TraversableOnce[String],
                 chdir: String = "",
                 outputFile: String = "",
                 errorFile: String = "",
                 metaFile: String = "") (callback: Int => A) = {
    val minijail = Config.get("runner.minijail.path", ".") + "/bin/minijail0"
    val scripts = Config.get("runner.minijail.path", ".") + "/scripts"
    val runtime = Runtime.getRuntime

    val commonParams = List(
      "-C", Config.get("runner.minijail.path", ".") + "/root-compilers",
      "-d", "/home",
      "-b", chdir + ",/home,1",
      "-1", chdir + "/" + outputFile,
      "-2", chdir + "/" + errorFile,
      "-M", metaFile,
      "-t", (Config.get("java.compile.time_limit", 30) * 1000).toString
    )

    val chrootedInputFiles = inputFiles.map(file => {
      if (!file.startsWith(chdir)) {
        throw new IllegalArgumentException("File " + file + " is not within the chroot jail")
      }
      file.substring(chdir.length + 1)
    })

    val params = lang match {
      case "java" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/javac") ++
        commonParams ++
        List(
          "-b", Config.get("runner.minijail.path", ".") + "/root-openjdk,/usr/lib/jvm",
          "-b", "/sys/,/sys"
        ) ++
        List("--", Config.get("java.compiler.path", "/usr/bin/javac"), "-J-Xmx512M") ++
        chrootedInputFiles
      case "c" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/gcc") ++
        commonParams ++
        List("--", Config.get("c.compiler.path", "/usr/bin/gcc"), "-std=c99", "-O2", "-lm") ++
        chrootedInputFiles
      case "cpp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/gcc") ++
        commonParams ++
        List("--", Config.get("cpp.compiler.path", "/usr/bin/g++"), "-O2", "-lm") ++
        chrootedInputFiles
      case "p" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/fpc") ++
        commonParams ++
        List(
          "--",
          "/usr/bin/ldwrapper", Config.get("p.compiler.path", "/usr/bin/fpc"),
          "-Tlinux",
          "-O2",
          "-Mobjfpc",
          "-Sc",
          "-Sh"
        ) ++
        chrootedInputFiles
      case "py" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/pyc") ++
        commonParams ++
        List("-b", Config.get("runner.minijail.path", ".") + "/root-python,/usr/lib/python2.7") ++
        List("--", Config.get("py.compiler.path", "/usr/bin/python"), "-m", "py_compile") ++
        chrootedInputFiles
      case "kj" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/kcl") ++
        commonParams ++
        List(
          "--",
          "/usr/bin/ldwrapper", Config.get("kcl.compiler.path", "/usr/bin/kcl"),
          "-lj",
          "-o",
          "Main.kx",
          "-c"
        ) ++
        chrootedInputFiles
      case "kp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/kcl") ++
        commonParams ++
        List(
          "--",
          "/usr/bin/ldwrapper", Config.get("kcl.compiler.path", "/usr/bin/kcl"),
          "-lp",
          "-o",
          "Main.kx",
          "-c"
        ) ++
        chrootedInputFiles
      case "hs" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/ghc") ++
        commonParams ++
        List("-b", Config.get("runner.minijail.path", ".") + "/root-hs,/usr/lib/ghc") ++
        List("--", Config.get("ghc.compiler.path", "/usr/bin/ghc"), "-O2", "-o", "Main") ++
        chrootedInputFiles
      case _ => null
    }

    debug("Compile {}", params.mkString(" "))

    pusing (runtime.exec(params.toArray)) { process => {
      if (process != null) {
        val status = process.waitFor
        patchMetaFile(metaFile)
        callback(status)
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
    val minijail = Config.get("runner.minijail.path", ".") + "/bin/minijail0"
    val scripts = Config.get("runner.minijail.path", ".") + "/scripts"
    val runtime = Runtime.getRuntime

    var timeLimit = message.timeLimit
    if (lang == "java" || lang == "p") {
      timeLimit += 1
    }

    val commonParams = List(
      "-C", Config.get("runner.minijail.path", ".") + "/root",
      "-d", "/home",
      "-b", chdir + ",/home",
      "-0", inputFile,
      "-1", outputFile,
      "-2", errorFile,
      "-M", metaFile,
      "-t", (timeLimit * 1000).toInt.toString,
      "-O", "5500000"
    )

    originalInputFile match {
      case Some(file) => FileUtil.copy(new File(file), new File(chdir, "data.in"))
      case None => {}
    }

    runMetaFile match {
      case Some(file) => FileUtil.copy(new File(file), new File(chdir, "meta.in"))
      case None => {}
    }

    val memoryLimit = message.memoryLimit * 1024

    val params = (lang match {
      case "java" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/java") ++
        commonParams ++
        List(
          "-b", Config.get("runner.minijail.path", ".") + "/root-openjdk,/usr/lib/jvm",
          "-b", "/sys/,/sys"
        ) ++
        List("--", "/usr/bin/java", "-Xmx" + memoryLimit, "Main")
      case "c" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/cpp") ++
        commonParams ++
        List("-m", memoryLimit.toString, "--", "./a.out")
      case "cpp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/cpp") ++
        commonParams ++
        List("-m", memoryLimit.toString, "--", "./a.out")
      case "p" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/pas") ++
        commonParams ++
        List("-m", memoryLimit.toString, "--", "/usr/bin/ldwrapper", "./Main")
      case "py" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/py") ++
        commonParams ++
        List("-b", Config.get("runner.minijail.path", ".") + "/root-python,/usr/lib/python2.7") ++
        List("-m", memoryLimit.toString, "--", "/usr/bin/python", "Main.py")
      case "kp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/karel") ++
        commonParams ++
        List(
          "--",
          "/usr/bin/ldwrapper", Config.get("karel.runtime.path", "/usr/bin/karel"),
          "/dev/stdin",
          "-oi",
          "-q",
          "-p2",
          "Main.kx"
        )
      case "kj" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/karel") ++
        commonParams ++
        List(
          "--",
          "/usr/bin/ldwrapper", Config.get("karel.runtime.path", "/usr/bin/karel"),
          "/dev/stdin",
          "-oi",
          "-q",
          "-p2",
          "Main.kx"
        )
      case "hs" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/hs") ++
        commonParams ++
        List("-b", Config.get("runner.minijail.path", ".") + "/root-hs,/usr/lib/ghc") ++
        List("-m", memoryLimit.toString, "--", "./Main")
    }) ++ extraParams

    debug("{} {}", logTag, params.mkString(" "))
    pusing (runtime.exec(params.toArray)) { process =>
      if (process != null) process.waitFor
      patchMetaFile(metaFile)
    }
  }

  def patchMetaFile(metaFile: String) = {
    val meta = try {
      collection.mutable.Map(MetaFile.load(metaFile).toSeq: _*)
    } catch {
      case e: java.io.FileNotFoundException => collection.mutable.Map("time" -> "0",
                                                                      "time-wall" -> "0",
                                                                      "signal" -> "-1")
    }
    if (meta.contains("signal")) {
      meta("status") = meta("signal") match {
        case "4" => "FO"  // SIGILL
        case "6" => "RE"  // SIGABRT
        case "7" => "SG"  // SIGBUS
        case "8" => "RE"  // SIGFPE
        case "9" => "FO"  // SIGKILL
        case "11" => "SG" // SIGSEGV
        case "14" => "TO" // SIGALRM
        case "24" => "TO" // SIGXCPU
        case "30" => "TO" // SIGXCPU
        case "31" => "FO" // SIGSYS
        case "25" => "OL" // SIGFSZ
        case "35" => "OL" // SIGFSZ
        case _ => "JE"
      }
    } else {
      meta("return") = meta("status")
      if (meta("status") == "0") {
        meta("status") = "OK"
      } else if (meta("status") != "JE") {
        meta("status") = "RE"
      }
    }
    meta("time") = (meta("time").toInt / 1e6).toString
    meta("time-wall") = (meta("time-wall").toInt / 1e6).toString
    MetaFile.save(metaFile, meta)
  }
}
