import omegaup.grader._

import java.io._
import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class GraderSpec extends FlatSpec with ShouldMatchers {
	"Grader" should "be tolerant to large files" in {
		TokenNumericGrader.gradeCase(null,
					     "test",
					     new File("src/test/resources/A.out"),
					     new File("src/test/resources/B.out")) should equal (0.0)
		TokenNumericGrader.gradeCase(null,
					     "test",
					     new File("src/test/resources/A.out"),
					     new File("src/test/resources/A.out")) should equal (0.0)
		TokenNumericGrader.gradeCase(null,
					     "test",
					     new File("src/test/resources/B.out"),
					     new File("src/test/resources/B.out")) should equal (1.0)
	}
	
}
