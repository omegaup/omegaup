import omegaup.grader._
import omegaup.data._

import java.text.ParseException
import org.scalatest._
import org.scalatest.matchers._
import Matchers._

class RoutingSpec extends FlatSpec {

	"Routing descriptions" should "parse correctly" in {
    var thrown: ParseException = null

    RoutingDescription.parse("")._1 should equal (Map())

    a [ParseException] should be thrownBy RoutingDescription.parse("in")

    val (mapping, router) = RoutingDescription.parse("""
      {
        name: "slow",
        runners: ["slow"],
        condition: slow
      }
      {
        name: "problem",
        runners: ["problem"],
        condition: problem == "problem"
      }
      {
        name: "test",
        runners: ["hello:1", "world:2"],
        condition: contest == "test_contest" && user in ["test_user"]
      }
    """)
    mapping should equal (Map(
      new RunnerEndpoint("slow", 21681) -> "slow",
      new RunnerEndpoint("problem", 21681) -> "problem",
      new RunnerEndpoint("world", 2) -> "test",
      new RunnerEndpoint("hello", 1) -> "test"
    ))
    mapping(new RunnerEndpoint("problem", 21681)) should equal("problem")
    router(
      new Run(
        contest = Some(new Contest(alias = "foo")),
        problem = new Problem(),
        user = new User(username = "foo")
      )
    ) should equal (RoutingDescription.defaultQueueName)
    router(
      new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(),
        user = new User(username = "foo")
      )
    ) should equal (RoutingDescription.defaultQueueName)
    router(
      new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(),
        user = new User(username = "test_user")
      )
    ) should equal ("test")
    router(
      new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(slow = true),
        user = new User(username = "test_user")
      )
    ) should equal ("slow")
    router(
      new Run(
        contest = Some(new Contest(alias = "test_contest")),
        problem = new Problem(alias = "problem"),
        user = new User(username = "test_user")
      )
    ) should equal ("problem")
	}
	
}

