import omegaup.grader._
import omegaup.data._

import java.text.ParseException
import org.scalatest._
import org.scalatest.matchers._
import Matchers._

class RoutingSpec extends FlatSpec {

	"Routing descriptions" should "parse correctly" in {
    var thrown: ParseException = null

    a [ParseException] should be thrownBy RoutingDescription.parse(Array("in"))

    val router = RoutingDescription.parse(Array(
      """contest: problem == "problem" """,
      """urgent: !rejudge && contest == "test_contest" && user in ["test_user"]""",
      """practice: slow"""
    ))
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "foo")),
        problem = new Problem(),
        user = new User(username = "foo")
      ), false, false)
    ) should equal (2)
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(),
        user = new User(username = "foo")
      ), false, false)
    ) should equal (2)
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(),
        user = new User(username = "test_user")
      ), false, false)
    ) should equal (0)
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(),
        user = new User(username = "test_user")
      ), false, true)
    ) should equal (6)
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(slow = true),
        user = new User(username = "test_user")
      ), false, false)
    ) should equal (1)
    router(
      new RunContext(new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(alias = "problem", slow = true),
        user = new User(username = "test_user")
      ), false, false)
    ) should equal (3)
	}
	
}

