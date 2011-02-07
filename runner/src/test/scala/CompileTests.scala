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
	}
	
	"OK" should "be correctly handled" in {
		val test1 = Runner.compile("c", List("#include<stdio.h>\nint main() { printf(\"Hello, World!\\n\"); return 0; }"), None, None)
		test1.status should equal ("ok")
		test1.token should not equal None
	}
	
}
