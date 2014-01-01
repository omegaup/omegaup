import omegaup._
import omegaup.data._
import omegaup.grader._
import omegaup.grader.drivers._

import Language._

import scala.collection.mutable._
import org.scalatest.{FlatSpec, BeforeAndAfterAll}
import org.scalatest.matchers.ShouldMatchers

class DriverSpec extends FlatSpec with ShouldMatchers with BeforeAndAfterAll {
  override def beforeAll() {
    import java.io._
    import java.util.zip._

    val root = new File("test-env")
    if (root.exists()) {
      FileUtil.deleteDirectory(root)
    }
    root.mkdir()

    // populate temp database for problems and contests
    Config.set("db.driver", "org.h2.Driver")
    Config.set(
      "db.url",
      "jdbc:h2:file:" + root.getCanonicalPath + "/omegaup;DB_CLOSE_ON_EXIT=FALSE"
    )
    Config.set("db.user", "sa")
    Config.set("db.password", "")

    Config.set("grader.runner.timeout", "10")
    Config.set("grader.port", "21681")
    Config.set("grader.embedded_runner.enable", "true")
    Config.set("grader.root", root.getCanonicalPath + "/grader")
    Config.set("runner.sandbox.path", new File("../sandbox").getCanonicalPath)
    Config.set("runner.minijail.path", "/var/lib/minijail")
    Config.set(
      "runner.sandbox.profiles.path",
      new File("src/test/resources/sandbox-profiles").getCanonicalPath
    )
    Config.set("submissions.root", root.getCanonicalPath + "/submissions")
    Config.set("problems.root", root.getCanonicalPath + "/problems")
    Config.set("compile.root", root.getCanonicalPath + "/compile")
    Config.set("input.root", root.getCanonicalPath + "/input")
    Config.set("runner.sandbox", "minijail")
    Config.set("runner.preserve", "true")
    Config.set("logging.level", "debug")
    Config.set("logging.file", "")

    Logging.init

    val input = new ZipInputStream(new FileInputStream("src/test/resources/omegaup-base.zip"))
    var entry: ZipEntry = input.getNextEntry
    val buffer = Array.ofDim[Byte](1024)
    var read: Int = 0

    while(entry != null) {
      val outFile = new File(root.getCanonicalPath + "/" + entry.getName)

      if(entry.getName.endsWith("/")) {
        outFile.mkdirs()
      } else {
        val output = new FileOutputStream(outFile)
        while( { read = input.read(buffer); read > 0 } ) {
          output.write(buffer, 0, read)
        }
        output.close
      }

      input.closeEntry
      entry = input.getNextEntry
    }

    input.close

    implicit val conn = Manager.connection

    FileUtil.read("src/main/resources/h2.sql").split("\n\n").foreach { Database.execute(_) }
    FileUtil.read("src/test/resources/h2.sql").split("\n\n").foreach { Database.execute(_) }
  }

  "OmegaUpDriver" should "submit" in {
    System.setProperty("grader.embedded_runner.enable", "true")

    val t = new Thread() { override def run(): Unit = { Manager.main(Array.ofDim[String](0)) } } 
    t.start

    val tests = new ListBuffer[Run => Unit]
    tests += null
    implicit val conn = Manager.connection

    val lock = new Object
    var ready = false
    var exception: Exception = null

    Manager.addListener {
      run => {
        try {
          tests(run.id.toInt)(run)
        } catch {
          case e: Exception => { exception = e }
        }
        ready = true
        lock.synchronized { lock.notify }
      }
    }

    def omegaUpSubmitContest(
      id: Long,
      language: Language,
      code: String,
      user: Int,
      contest: Int,
      date: String
    )(test: (Run) => Unit) = {
      import java.util.Date
      import java.sql.Timestamp
      import java.text.SimpleDateFormat

      val file = java.io.File.createTempFile(
        System.currentTimeMillis.toString,
        "",
        new java.io.File(Config.get("submissions.root", "."))
      )

    implicit val conn = Manager.connection

    FileUtil.write(file.getCanonicalPath, code)

    val submit_id = GraderData.insert(new Run(
      guid = file.getName,
      user = user,
      language = language,
      problem = new Problem(id = id),
      contest = contest match {
        case 0 => None
        case x: Int => Some(new Contest(id = x))
      },
      time = new Timestamp(date match {
          case null => new Date().getTime()
          case x: String => new SimpleDateFormat("yyyy-MM-dd HH:mm:ss").parse(x).getTime()
        })
    )).id

    ready = false
    tests += test
    Manager.grade(submit_id)

    lock.synchronized {
      lock.wait(10000)
    }

    if (!ready) throw new Exception("Test timed out")
    if (exception != null) throw exception
  }

  def omegaUpSubmit (problem_id: Long, language: Language, code: String)(test: (Run) => Unit) = {
    omegaUpSubmitContest(problem_id, language, code, 1, 0, null) { test }
  }

  omegaUpSubmit(1, Language.Cpp, """
    int main() {
      while(true);
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.TimeLimitExceeded)
    run.score should equal (0)
    run.contest_score should equal (0)
  }}

  omegaUpSubmitContest(1, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << "Hello, World!" << endl;
      cout << a + b << endl;

      return EXIT_SUCCESS;
    }
  """, 1, 1, "2000-01-01 00:10:00") { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1)
    run.contest_score should equal (100)
  }}

  omegaUpSubmit(1, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << "Hello, World!" << endl;
      cout << 3 << endl;

      return EXIT_SUCCESS;
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.PartialAccepted)
    run.score should equal (0.5)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(1, Language.Literal, "") { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.WrongAnswer)
    run.score should equal (0.0)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(1, Language.Literal,
    """data:application/x-zip;base64,
    UEsDBAoAAAAAAKis/kI1yT8dEAAAABAAAAAGABwAMDAub3V0VVQJAAN7lPhRe5T4UXV4CwABBOgD
    AAAE6AMAAEhlbGxvLCBXb3JsZCEKMwpQSwMEFAAAAAgAqaz+Qrbi9zMUAAAAFgAAAAYAHAAwMS5v
    dXRVVAkAA32U+FF9lPhRdXgLAAEE6AMAAAToAwAA80jNycnXUQjPL8pJUeQyMgADLgBQSwECHgMK
    AAAAAACorP5CNck/HRAAAAAQAAAABgAYAAAAAAABAAAApIEAAAAAMDAub3V0VVQFAAN7lPhRdXgL
    AAEE6AMAAAToAwAAUEsBAh4DFAAAAAgAqaz+Qrbi9zMUAAAAFgAAAAYAGAAAAAAAAQAAAKSBUAAA
    ADAxLm91dFVUBQADfZT4UXV4CwABBOgDAAAE6AMAAFBLBQYAAAAAAgACAJgAAACkAAAAAAA=
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1.0)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(1, Language.Literal,
    """data:application/x-zip;base64,
    UEsDBAoAAAAAAKis/kI1yT8dEAAAABAAAAAGABwAMDAub3V0VVQJAAN7lPhRe5T4UXV4CwABBOgD
    AAAE6AMAAEhlbGxvLCBXb3JsZCEKMwpQSwMECgAAAAAASwb/QvaaEjYQAAAAEAAAAAYAHAAwMS5v
    dXRVVAkAA73B+FGGlPhRdXgLAAEE6AMAAAToAwAASGVsbG8sIFdvcmxkIQowClBLAQIeAwoAAAAA
    AKis/kI1yT8dEAAAABAAAAAGABgAAAAAAAEAAACkgQAAAAAwMC5vdXRVVAUAA3uU+FF1eAsAAQTo
    AwAABOgDAABQSwECHgMKAAAAAABLBv9C9poSNhAAAAAQAAAABgAYAAAAAAABAAAApIFQAAAAMDEu
    b3V0VVQFAAO9wfhRdXgLAAEE6AMAAAToAwAAUEsFBgAAAAACAAIAmAAAAKAAAAAAAA==
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.PartialAccepted)
    run.score should equal (0.5)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(2, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << a + b << endl;

      return EXIT_SUCCESS;
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(2, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << 3 << endl;

      return EXIT_SUCCESS;
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.PartialAccepted)
    run.score should be (0.2 plusOrMinus 0.001)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(3, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << a + b << endl;

      return EXIT_SUCCESS;
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(3, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      int a, b;
      cin >> a >> b;
      cout << 3 << endl;

      return EXIT_SUCCESS;
    }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.PartialAccepted)
    run.score should be (0.05 plusOrMinus 0.001)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(4, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      double a, b;
      cin >> a >> b;
      cout << a + b << endl;

      return EXIT_SUCCESS;
    }
    """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.PartialAccepted)
    run.score should be (0.71 plusOrMinus 0.01)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(4, Language.Cpp, """
    #include <cstdlib>
    #include <iostream>
    #include <map>
    #include <unistd.h>

    using namespace std;

    int main(int argc, char *argv[]) {
      double a, b;
      cin >> a >> b;
      cout << a*a + b*b << endl;

      return EXIT_SUCCESS;
    }
    """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(5, Language.Cpp, """
    #include "solve.h"

    long long solve(long long a, long long b) { return a + b; }
    """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.Accepted)
    run.score should equal (1)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(5, Language.Cpp, """
    long long solve(long long a, long long b) { return 0; }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.WrongAnswer)
    run.score should equal (0)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(5, Language.Cpp, """
    #include <stdio.h>
    int main() { printf("Hello, World!\n3\n"); }
  """) { run => {
    run.status should equal (Status.Ready)
    run.veredict should equal (Veredict.CompileError)
    run.score should equal (0)
    run.contest_score should equal (0)
  }}

  omegaUpSubmit(6, Language.KarelJava, """
    class program {
      void turn(n) { iterate(n) turnleft(); }

      void avanza()
      {
        if (frontIsClear)
          {
          move();
        }
        else
          {
          turn(2);
          while(frontIsClear)
            move();
          turn(3);
          if (frontIsClear)
            {
            move();
            turn(3);
          }
        }
      }

      void recur(n)
      {
        if (notFacingNorth)
          {
          recuerdame(n);
          regresa();
        }
        else
          {
          turn(3);
          while(frontIsClear)
            {
            move();
          }
          turn(2);
        }
      }

      void regresa()
      {
        if(frontIsClear)
          {
          move();
        }
        else
          {
          turn(1);
          move();
          turnleft();
          while(frontIsClear)
            move();
          turn(2);
        }
      }

      void crece(n)
      {
        if(!iszero(n))
          {
          iterate(4)
          {
            if (frontIsClear)
              {
              move();
              if (notNextToABeeper)
                {
                putbeeper();
              }
              crece(pred(n));
              turn(2);
              move();
              turn(2);
            }
            turnleft();
          }
        }
      }

      void recuerdame(n)
      {
        if (nextToABeeper)
          {
          avanza();
          recur(n);
          crece(n);
        }
        else
          {
          avanza();
          recur(n);
        }
      }

      void cuenta(n)
      {
        if (nextToABeeper)
          {
          pickbeeper();
          cuenta(succ(n));
        }
        else
          {
          while(notFacingEast)
            turnleft();
          recuerdame(n);
        }
      }

      void recoge()
      {
        if (notFacingNorth)
          {
          avanza();
          if (nextToABeeper)
            {
            pickbeeper();
            recoge();
            putbeeper();
          }
          else
            {
            recoge();
          }
        }else
        {
          turn(2);
          while(frontIsClear)
            move();
        }
      }

      program() {
        cuenta(0);
        while(notFacingEast)
          turnleft();
        recoge();
        turnoff();
      }

    }
    """) { run => {
      run.status should equal (Status.Ready)
      run.veredict should equal (Veredict.Accepted)
      run.score should equal (1)
      run.contest_score should equal (0)
    }}
  }
}
