import omegaup.runner._

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

case class RandomObject(identifier: Int, name: String)

class CompileSpec extends FlatSpec with ShouldMatchers {

	"Compile error" should "be correctly handled" in {
		val test1 = Runner.compile("c", List("foo"), None, None)
		test1.status should equal ("compile error")
		
		val test2 = Runner.compile("c", List("#include<stdio.h>"), None, None)
		test2.status should equal ("compile error")
		
		val test3 = Runner.compile("c", List("#include</dev/urandom>"), None, None)
		test3.status should equal ("compile error")
		
		val test4 = Runner.compile("cpp", List("foo"), None, None)
		test4.status should equal ("compile error")
		
		val test5 = Runner.compile("cpp", List("#include<stdio.h>"), None, None)
		test5.status should equal ("compile error")
		
		val test6 = Runner.compile("cpp", List("#include</dev/urandom>"), None, None)
		test6.status should equal ("compile error")
		
		val test7 = Runner.compile("java", List("foo"), None, None)
		test7.status should equal ("compile error")
	}
	
	"OK" should "be correctly handled" in {
		val test1 = Runner.compile("c", List("#include<stdio.h>\n#include<stdlib.h>\nint main() { int x; scanf(\"%d\", &x); switch (x) { case 0: printf(\"Hello, World!\\n\"); break; case 1: while(1); break; case 2: fork(); break; case 3: while(1) malloc(1024*1024); break; case 4: while(1) printf(\"trololololo\\n\"); break; case 5: fopen(\"/etc/passwd\", \"r\"); break; case 6: printf(\"%s\", (char*)(x-6)); break; case 7: printf(\"%d\", 1/(x-7)); break; case 8: return 1; } return 0; }"), None, None)
		
		test1.status should equal ("ok")
		test1.token should not equal None
		
		evaluating { Runner.run(test1.token.get, 1, 65536, 1, Some("foo"), None) } should produce [RuntimeException]
		Runner.run(test1.token.get, 1, 65536, 1, None, Some(List(
			new CaseData("ok", "0"),
			new CaseData("tle", "1"),
			new CaseData("rfe", "2"),
			new CaseData("mle", "3"),
			new CaseData("ole", "4"),
			new CaseData("ae", "5"),
			new CaseData("segfault", "6"),
			new CaseData("zerodiv", "7"),
			new CaseData("ret1", "8")
		)))
		
		val test2 = Runner.compile("cpp", List("#include<cstdio>\n#include<cstdlib>\n#include <unistd.h>\nusing namespace std;\nint main() { int x; scanf(\"%d\", &x); switch (x) { case 0: printf(\"Hello, World!\\n\"); break; case 1: while(1); break; case 2: fork(); break; case 3: while(1) malloc(1024*1024); break; case 4: while(1) printf(\"trololololo\\n\"); break; case 5: fopen(\"/etc/passwd\", \"r\"); break; case 6: printf(\"%s\", (char*)(x-6)); break; case 7: printf(\"%d\", 1/(x-7)); break; case 8: return 1;} return 0; }"), None, None)
		test2.status should equal ("ok")
		test2.token should not equal None
		
		Runner.run(test2.token.get, 1, 65536, 1, None, Some(List(
			new CaseData("ok", "0"),
			new CaseData("tle", "1"),
			new CaseData("rfe", "2"),
			new CaseData("mle", "3"),
			new CaseData("ole", "4"),
			new CaseData("ae", "5"),
			new CaseData("segfault", "6"),
			new CaseData("zerodiv", "7"),
			new CaseData("ret1", "8")
		)))
		
		val test3 = Runner.compile("java", List("import java.io.*;\nimport java.util.*;\nclass Main {public static void main(String[] args) throws Exception{Scanner in = new Scanner(System.in); List l = new ArrayList(); switch(in.nextInt()){case 0: System.out.println(\"Hello, World!\\n\"); break; case 1: while(true) {} case 2: Runtime.getRuntime().exec(\"/bin/ls\").waitFor(); break; case 3: while(true) {l.add(new ArrayList(1024*1024));} case 4: while(true) {System.out.println(\"trololololo\");} case 5: new FileInputStream(\"/etc/shadow\"); break; case 6: System.out.println(l.get(0)); break; case 7: System.out.println(1 / (int)(Math.sin(0.1))); break; case 8: System.exit(1); break; }}}"), None, None)
		test3.status should equal ("ok")
		test3.token should not equal None
		
		Runner.run(test3.token.get, 1, 65536, 1, None, Some(List(
			new CaseData("ok", "0"),
			new CaseData("tle", "1"),
			new CaseData("rfe", "2"),
			new CaseData("mle", "3"),
			new CaseData("ole", "4"),
			new CaseData("ae", "5"),
			new CaseData("segfault", "6"),
			new CaseData("zerodiv", "7"),
			new CaseData("ret1", "8")
		)))
	}
}
