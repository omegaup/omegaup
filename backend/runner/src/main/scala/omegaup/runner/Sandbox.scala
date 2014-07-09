package omegaup.runner

import java.io.BufferedReader
import java.io.File
import java.io.InputStreamReader

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
      "-t", (Config.get("java.compile.time_limit", 30) * 1000).toString,
      "-O", Config.get("runner.compile.output_limit", 64 * 1024 * 1024).toString
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
        List("--", Config.get("c.compiler.path", "/usr/bin/gcc"), "-std=c99", "-O2") ++
        chrootedInputFiles ++ List("-lm")
      case "cpp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/gcc") ++
        commonParams ++
        List("--", Config.get("cpp.compiler.path", "/usr/bin/g++"), "-O2") ++
        chrootedInputFiles ++ List("-lm")
      case "cpp11" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/gcc") ++
        commonParams ++
        List("--", Config.get("cpp.compiler.path", "/usr/bin/g++"), "-O2", "-std=c++11", "-xc++") ++
        chrootedInputFiles ++ List("-lm")
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
        List(
          "--",
          Config.get("ghc.compiler.path", "/usr/lib/ghc/lib/ghc"), "-B/usr/lib/ghc",
          "-O2",
          "-o",
          "Main"
        ) ++
        chrootedInputFiles
      case _ => null
    }

    debug("Compile {}", params.mkString(" "))

    val (status, syscallName) = runMinijail(params)
    if (status != -1) {
      val errorPath = chdir + "/" + errorFile
      // Truncate the compiler error to 8k
      try {
        val outChan = new java.io.FileOutputStream(errorPath, true).getChannel()
        outChan.truncate(8192)
        outChan.close()
      } catch {
        case e: Exception => {
          error("Unable to truncate {}: {}", errorPath, e)
        }
      }
      patchMetaFile(lang, syscallName, None, metaFile)
    }
    callback(status)
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
    if (lang == "java") {
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
      "-O", message.outputLimit.toString
    )

    originalInputFile match {
      case Some(file) => FileUtil.copy(new File(file), new File(chdir, "data.in"))
      case None => {}
    }

    runMetaFile match {
      case Some(file) => FileUtil.copy(new File(file), new File(chdir, "meta.in"))
      case None => {}
    }

    // 16MB + memory limit to prevent some RTE
    val memoryLimit = (16 * 1024 + message.memoryLimit) * 1024
    // "640MB should be enough for anybody"
    val hardLimit = Math.max(
      memoryLimit,
      Config.get("runner.memory.limit", 640) * 1024 * 1024
    ).toString

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
        List("-m", hardLimit, "--", "./a.out")
      case "cpp" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/cpp") ++
        commonParams ++
        List("-m", hardLimit, "--", "./a.out")
      case "cpp11" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/cpp") ++
        commonParams ++
        List("-m", hardLimit, "--", "./a.out")
      case "p" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/pas") ++
        commonParams ++
        List("-m", hardLimit, "--", "/usr/bin/ldwrapper", "./Main")
      case "py" =>
        List("/usr/bin/sudo", minijail, "-S", scripts + "/py") ++
        commonParams ++
        List("-b", Config.get("runner.minijail.path", ".") + "/root-python,/usr/lib/python2.7") ++
        List("-m", hardLimit, "--", "/usr/bin/python", "Main.py")
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
        List("-m", hardLimit, "--", "./Main")
    }) ++ extraParams

    debug("{} {}", logTag, params.mkString(" "))
    val (status, syscallName) = runMinijail(params)
    patchMetaFile(lang, syscallName, Some(message), metaFile)
  }

  private def runMinijail(params: List[String]): (Int, String) = {
    val helperPath = Config.get("runner.minijail.path", ".") + "/bin/minijail_syscall_helper"
    val helperParams = List("/usr/bin/sudo", helperPath)
    val runtime = Runtime.getRuntime
    var status = -1
    var syscallName = ""

    pusing (runtime.exec(helperParams.toArray)) { helper => {
      pusing (runtime.exec(params.toArray)) { minijail =>
        if (minijail != null) {
          status = minijail.waitFor
        }
      }
      if (helper != null) {
        helper.getOutputStream.close
        using (new BufferedReader(new InputStreamReader(helper.getInputStream))) { stream =>
          syscallName = stream.readLine
        }
        helper.getInputStream.close
        helper.waitFor
      }
    }}

    (status, syscallName)
  }

  private def patchMetaFile(lang: String, syscallName: String, message: Option[RunInputMessage], metaFile: String) = {
    val meta = try {
      collection.mutable.Map(MetaFile.load(metaFile).toSeq: _*)
    } catch {
      case e: java.io.FileNotFoundException => collection.mutable.Map("time" -> "0",
                                                                      "time-wall" -> "0",
                                                                      "mem" -> "0",
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
        case "31" => "RE" // SIGSYS
        case "25" => "OL" // SIGFSZ
        case "35" => "OL" // SIGFSZ
        case _ => "JE"
      }

      if (meta("signal") == "31") { // SIGSYS
        meta("syscall") = syscallName
      }
    } else {
      meta("return") = meta("status")
      if (meta("status") == "0" || lang == "c") {
        meta("status") = "OK"
      } else if (meta("status") != "JE") {
        meta("status") = "RE"
      }
    }

    message match {
      case Some(m) => {
        if (lang == "java") {
          // Subtract the core JVM memory consumption. 
          meta("mem") = (meta("mem").toLong - 14000 * 1024).toString
        } else if (meta("status") != "JE" &&
                   meta("mem").toLong > m.memoryLimit * 1024) {
          meta("status") = "ML"
          meta("mem") = (m.memoryLimit * 1024).toString
        }
      }
      case _ => {}
    }

    meta("time") = "%.3f" format (meta("time").toInt / 1e6)
    meta("time-wall") = "%.3f" format (meta("time-wall").toInt / 1e6)
    MetaFile.save(metaFile, meta)
  }
}
