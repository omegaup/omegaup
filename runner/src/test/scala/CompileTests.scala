import java.io._

import omegaup._
import omegaup.data._
import omegaup.runner._
import org.slf4j._

import org.scalatest.{FlatSpec, BeforeAndAfterAll}
import org.scalatest.matchers.ShouldMatchers

class CompileSpec extends FlatSpec with ShouldMatchers with BeforeAndAfterAll {
  override def beforeAll() {
    import java.util.zip._

    val root = new File("test-env")

    if (root.exists()) {
      FileUtil.deleteDirectory(root.getCanonicalPath)
    }

    root.mkdir()
    new File(root.getCanonicalPath + "/compile").mkdir()

    Config.set("runner.preserve", true)
    Config.set("compile.root", root.getCanonicalPath + "/compile")
    Config.set("runner.sandbox.path", new File("../sandbox").getCanonicalPath)
    Config.set("logging.level", "debug")

    Logging.init()
  }

  "Compile error" should "be correctly handled" in {
    val test1 = Runner.compile(CompileInputMessage("c", List("foo")))
    test1.status should equal ("compile error")
    
    val test2 = Runner.compile(CompileInputMessage("c", List("#include<stdio.h>")))
    test2.status should equal ("compile error")
    
    val test3 = Runner.compile(CompileInputMessage("c", List("#include</dev/urandom>")))
    test3.status should equal ("compile error")
    
    val test4 = Runner.compile(CompileInputMessage("cpp", List("foo")))
    test4.status should equal ("compile error")
    
    val test5 = Runner.compile(CompileInputMessage("cpp", List("#include<stdio.h>")))
    test5.status should equal ("compile error")
    
    val test6 = Runner.compile(CompileInputMessage("cpp", List("#include</dev/urandom>")))
    test6.status should equal ("compile error")
    
    val test7 = Runner.compile(CompileInputMessage("java", List("foo")))
    test7.status should equal ("compile error")
  }
  
  "OK" should "be correctly handled" in {
    val zipRoot = new File("test-env")

    val test1 = Runner.compile(CompileInputMessage("c", List("#include<stdio.h>\n#include<stdlib.h>\nint main() { int x; scanf(\"%d\", &x); switch (x) { case 0: printf(\"Hello, World!\\n\"); break; case 1: while(1); break; case 2: fork(); break; case 3: while(1) malloc(1024*1024); break; case 4: while(1) printf(\"trololololo\\n\"); break; case 5: fopen(\"/etc/passwd\", \"r\"); break; case 6: printf(\"%s\", (char*)(x-6)); break; case 7: printf(\"%d\", 1/(x-7)); break; case 8: return 1; } return 0; }")))
    
    test1.status should equal ("ok")
    test1.token should not equal None
    
    //evaluating { Runner.run(RunInputMessage(test1.token.get, 1, 65536, 1, Some("foo")), new File(zipRoot.getCanonicalPath + "/test1.zip")) } should produce [RuntimeException]
    Runner.run(RunInputMessage(test1.token.get, 1, 65536, 1, None, Some(List(
      new CaseData("ok", "0"),
      new CaseData("tle", "1"),
      new CaseData("rfe", "2"),
      new CaseData("mle", "3"),
      new CaseData("ole", "4"),
      new CaseData("ae", "5"),
      new CaseData("segfault", "6"),
      new CaseData("zerodiv", "7"),
      new CaseData("ret1", "8")
    ))), new File(zipRoot.getCanonicalPath + "/test1.zip"))
    
    val test2 = Runner.compile(CompileInputMessage("cpp", List("#include<cstdio>\n#include<cstdlib>\n#include <unistd.h>\nusing namespace std;\nint main() { int x; scanf(\"%d\", &x); switch (x) { case 0: printf(\"Hello, World!\\n\"); break; case 1: while(1); break; case 2: fork(); break; case 3: while(1) malloc(1024*1024); break; case 4: while(1) printf(\"trololololo\\n\"); break; case 5: fopen(\"/etc/passwd\", \"r\"); break; case 6: printf(\"%s\", (char*)(x-6)); break; case 7: printf(\"%d\", 1/(x-7)); break; case 8: return 1;} return 0; }")))
    test2.status should equal ("ok")
    test2.token should not equal None
    
    Runner.run(RunInputMessage(test2.token.get, 1, 65536, 1, None, Some(List(
      new CaseData("ok", "0"),
      new CaseData("tle", "1"),
      new CaseData("rfe", "2"),
      new CaseData("mle", "3"),
      new CaseData("ole", "4"),
      new CaseData("ae", "5"),
      new CaseData("segfault", "6"),
      new CaseData("zerodiv", "7"),
      new CaseData("ret1", "8")
    ))), new File(zipRoot.getCanonicalPath + "/test2.zip"))
    
    val test3 = Runner.compile(CompileInputMessage("java", List("import java.io.*;\nimport java.util.*;\nclass Main {public static void main(String[] args) throws Exception{Scanner in = new Scanner(System.in); List l = new ArrayList(); switch(in.nextInt()){case 0: System.out.println(\"Hello, World!\\n\"); break; case 1: while(true) {} case 2: Runtime.getRuntime().exec(\"/bin/ls\").waitFor(); break; case 3: while(true) {l.add(new ArrayList(1024*1024));} case 4: while(true) {System.out.println(\"trololololo\");} case 5: new FileInputStream(\"/etc/shadow\"); break; case 6: System.out.println(l.get(0)); break; case 7: System.out.println(1 / (int)(Math.sin(0.1))); break; case 8: System.exit(1); break; }}}")))
    test3.status should equal ("ok")
    test3.token should not equal None
    
    Runner.run(RunInputMessage(test3.token.get, 1, 65536, 1, None, Some(List(
      new CaseData("ok", "0"),
      new CaseData("tle", "1"),
      new CaseData("rfe", "2"),
      new CaseData("mle", "3"),
      new CaseData("ole", "4"),
      new CaseData("ae", "5"),
      new CaseData("segfault", "6"),
      new CaseData("zerodiv", "7"),
      new CaseData("ret1", "8")
    ))), new File(zipRoot.getCanonicalPath + "/test3.zip"))
  }

  "Exploits" should "be handled" in {
    val zipRoot = new File("test-env")

    val test4 = Runner.compile(CompileInputMessage("cpp", List("int main() { (*(void (*)())\"\\x6a\\x39\\x58\\x0f\\x05\\xeb\\xf9\")(); }")))
    Runner.run(RunInputMessage(test4.token.get, 1, 65536, 1, None, Some(List(
      new CaseData("ok", "0")
    ))), new File(zipRoot.getCanonicalPath + "/test4.zip"))

    val test5 = Runner.compile(CompileInputMessage("cpp", List("int main() { (*(void (*)())\"\\x6a\\x02\\x58\\xcd\\x80\\xeb\\xf9\")(); }")))
    Runner.run(RunInputMessage(test5.token.get, 1, 65536, 1, None, Some(List(
      new CaseData("ok", "0")
    ))), new File(zipRoot.getCanonicalPath + "/test5.zip"))
  }
}
