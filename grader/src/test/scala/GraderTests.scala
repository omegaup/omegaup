import omegaup.grader._

import java.io._
import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class GraderSpec extends FlatSpec with ShouldMatchers {
	"Tokenizers" should "tokenize properly" in {
		var tok = new Tokenizer(new File("src/test/resources/token_test.txt"),
				        !_.isWhitespace)

		tok.hasNext should equal (true)
		tok.next.toString should equal ("public")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("static")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("void")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("1")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("2.0")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("-3.0.0")
		tok.hasNext should equal (true)
		tok.next.toString should equal ("1234567890123456789012345678901234567890.1234567890123456789012345678901234567890")
		tok.hasNext should equal (false)

		tok = new Tokenizer(new File("src/test/resources/token_test.txt"),
		  		    (c) => c.isDigit || c == '.' || c == '-')

		tok.hasNext should equal (true)
		tok.next.toDouble should equal (1.0)
		tok.hasNext should equal (true)
		tok.next.toDouble should equal (2.0)
		tok.hasNext should equal (true)
		tok.next.toDouble should equal (-3.0)
		tok.hasNext should equal (true)
		tok.next.toDouble should equal (1.2345678901234568E39)
		tok.hasNext should equal (false)
	}

	"Grader" should "work properly" in {
		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/A/sample.out"),
					      new File("src/test/resources/B/sample.out")) should equal (1.0)
		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/A/easy.00.out"),
					      new File("src/test/resources/B/easy.00.out")) should equal (1.0)
		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/A/easy.01.out"),
					      new File("src/test/resources/B/easy.01.out")) should equal (0.0)
		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/A/medium.00.out"),
					      new File("src/test/resources/B/medium.00.out")) should equal (0.0)
		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/A/medium.01.out"),
					      new File("src/test/resources/B/medium.01.out")) should equal (0.0)

		TokenCaselessGrader.gradeCase(null,
					      "test",
					      new File("src/test/resources/grade_A.out"),
					      new File("src/test/resources/grade_B.out")) should equal (1.0)
		TokenGrader.gradeCase(null,
				      "test",
				      new File("src/test/resources/grade_A.out"),
				      new File("src/test/resources/grade_B.out")) should equal (0.0)
		TokenNumericGrader.gradeCase(null,
					     "test",
					     new File("src/test/resources/grade_A.out"),
					     new File("src/test/resources/grade_B.out")) should equal (1.0)
	}

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
