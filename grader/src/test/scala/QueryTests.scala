import omegaup.data._
import omegaup.Database._

import Veredict._
import Validator._
import Server._
import Language._

import org.scalatest.FlatSpec
import org.scalatest.matchers.ShouldMatchers

class QuerySpec extends FlatSpec with ShouldMatchers {
	"The database wrapper" should "escape properly" in {
		build("?", 5) should equal ("5")
		build("?", 5.0) should equal ("5.0")
		build("?", 1L) should equal ("1")
		build("?", true) should equal ("1")
		build("?", false) should equal ("0")
		build("?", "abc") should equal ("'abc'")
		build("?", Some("abc")) should equal ("'abc'")
		build("?", null) should equal ("null")
		build("?", "'") should equal ("''''")
		build("?", "\\") should equal ("'\\\\'")
		build("?", "\\'") should equal ("'\\\\'''")
		build("?", Some("\\'")) should equal ("'\\\\'''")
		build("""
			SELECT
				*
			FROM
				(
					Runs AS r,
					Problems AS p
				)
			LEFT JOIN
				Contests AS c ON
					c.contest_id = r.contest_id
			LEFT JOIN
				Contest_Problems AS cp ON
					cp.contest_id = r.contest_id AND
					cp.problem_id = p.problem_id
			LEFT JOIN
				Contest_Problem_Opened AS cpo ON
					cpo.contest_id = r.contest_id AND
					cpo.problem_id = p.problem_id AND
					cpo.user_id = r.user_id
			WHERE
				p.problem_id = r.problem_id AND
				r.run_id = ?;
			""",
			1) should equal ("""
			SELECT
				*
			FROM
				(
					Runs AS r,
					Problems AS p
				)
			LEFT JOIN
				Contests AS c ON
					c.contest_id = r.contest_id
			LEFT JOIN
				Contest_Problems AS cp ON
					cp.contest_id = r.contest_id AND
					cp.problem_id = p.problem_id
			LEFT JOIN
				Contest_Problem_Opened AS cpo ON
					cpo.contest_id = r.contest_id AND
					cpo.problem_id = p.problem_id AND
					cpo.user_id = r.user_id
			WHERE
				p.problem_id = r.problem_id AND
				r.run_id = 1;
			""")
		build("INSERT INTO Runs (user_id, problem_id, guid, language, veredict, ip) VALUES(?, ?, ?, ?, ?, ?);",
			1,
			1,
			"123",
			Language.Cpp,
			Veredict.Accepted,
			"127.0.0.1") should equal ("INSERT INTO Runs (user_id, problem_id, guid, language, veredict, ip) VALUES(1, 1, '123', 'cpp', 'AC', '127.0.0.1');" )
	}
}
