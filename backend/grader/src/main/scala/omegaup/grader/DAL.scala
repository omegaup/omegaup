package omegaup.grader

import java.sql._
import omegaup.data._
import omegaup.Database._

import Veredict._
import Validator._
import Server._
import Language._

object GraderData {
	def hydrateRun(rs: ResultSet) =
		new Run(
			id = rs.getLong("run_id"),
			guid = rs.getString("guid"),
			user = new User(
				id = rs.getLong("user_id"),
				username = rs.getString("username")
			),
			language = Language.withName(rs.getString("language")),
			status = Status.withName(rs.getString("status")),
			veredict = Veredict.withName(rs.getString("veredict")),
			time = rs.getTimestamp("time"),
			submit_delay = rs.getInt("submit_delay"),
			score = rs.getDouble("score"),
			contest_score = rs.getDouble("contest_score"),
			judged_by = rs.getString("judged_by") match {
				case null => None
				case x: String => Some(x)
			},
			problem = new Problem(
				id = rs.getLong("problem_id"),
				validator = Validator.withName(rs.getString("validator")),
				alias = rs.getString("alias"),
				server = rs.getString("server") match {
					case null => None
					case x: String => Some(Server.withName(x))
				},
				remote_id = rs.getString("remote_id") match {
					case null => None
					case x: String => Some(x)
				},
				time_limit = rs.getString("time_limit") match {
					case null => None
					case x: String => Some(x.toLong)
				},
				memory_limit = rs.getString("memory_limit") match {
					case null => None
					case x: String => Some(x.toLong)
				},
				output_limit = rs.getString("output_limit") match {
					case null => None
					case x: String => Some(x.toLong)
				},
				open_time = rs.getTimestamp("open_time") match {
					case null => None
					case x: Timestamp => Some(x)
				},
				points = rs.getString("points") match {
					case null => None
					case x: String => Some(x.toDouble)
				}
			),
			contest = rs.getLong("contest_id") match {
				case 0 => None
				case x: Long => Some(new Contest(
					id = rs.getLong("contest_id"),
					alias = rs.getString("contest_alias"),
					start_time = rs.getTimestamp("start_time"),
					finish_time = rs.getTimestamp("finish_time"),
					points_decay_factor = rs.getDouble("points_decay_factor"),
					partial_score = rs.getInt("partial_score") == 1,
					feedback = Feedback.withName(rs.getString("feedback")),
					penalty = rs.getInt("penalty"),
					penalty_time_start = PenaltyTimeStart.withName(rs.getString("penalty_time_start"))
				))
			}
		)

	def run(id: Long)(implicit connection: Connection): Option[Run] =
		query("""
			SELECT
				r.*, p.*, u.username, cpo.open_time, cp.points, c.alias AS contest_alias,
				c.start_time, c.finish_time, c.points_decay_factor,
				r.submit_delay, c.partial_score, c.feedback, c.penalty,
				c.penalty_time_start, c.penalty_calc_policy
			FROM
				Runs AS r
			INNER JOIN
				Problems AS p ON
					p.problem_id = r.problem_id
			INNER JOIN
				Users AS u ON
					u.user_id = r.user_id
			LEFT JOIN
				Contests AS c ON
					c.contest_id = r.contest_id
			LEFT JOIN
				Contest_Problems AS cp ON
					cp.contest_id = r.contest_id AND
					cp.problem_id = r.problem_id
			LEFT JOIN
				Contest_Problem_Opened AS cpo ON
					cpo.contest_id = r.contest_id AND
					cpo.problem_id = r.problem_id AND
					cpo.user_id = r.user_id
			WHERE
				r.run_id = ?;
			""",
			id
		) { hydrateRun }
		
	def pendingRuns()(implicit connection: Connection): Iterable[Run] =
		queryEach("""
			SELECT
				r.*, p.*, u.username, cpo.open_time, cp.points, c.alias AS contest_alias,
				c.start_time, c.finish_time, c.points_decay_factor,
				r.submit_delay, c.partial_score, c.feedback, c.penalty,
				c.penalty_time_start, c.penalty_calc_policy
			FROM
				Runs AS r
			INNER JOIN
				Problems AS p ON
					p.problem_id = r.problem_id
			INNER JOIN
				Users AS u ON
					u.user_id = r.user_id
			LEFT JOIN
				Contests AS c ON
					c.contest_id = r.contest_id
			LEFT JOIN
				Contest_Problems AS cp ON
					cp.contest_id = r.contest_id AND
					cp.problem_id = r.problem_id
			LEFT JOIN
				Contest_Problem_Opened AS cpo ON
					cpo.contest_id = r.contest_id AND
					cpo.problem_id = r.problem_id AND
					cpo.user_id = r.user_id
			WHERE
				r.status != 'ready';
			"""
		) { hydrateRun }
		
	def update(run: Run)(implicit connection: Connection): Run = {
		execute(
			"UPDATE Runs SET status = ?, veredict = ?, runtime = ?, memory = ?, score = ?, contest_score = ?, judged_by = ? WHERE run_id = ?;",
			run.status,
			run.veredict,
			run.runtime,
			run.memory,
			run.score,
			run.contest_score,
			run.judged_by,
			run.id
		)
		run
	}
		
	def insert(run: Run)(implicit connection: Connection): Run = {
		execute(
			"INSERT INTO Runs (user_id, problem_id, contest_id, guid, language, veredict, ip, time) VALUES(?, ?, ?, ?, ?, ?, ?, ?);",
			run.user.id,
			run.problem.id,
			run.contest match {
				case None => None
				case Some(x) => Some(x.id)
			},
			run.guid,
			run.language,
			run.veredict,
			run.ip,
			run.time
		)
		run.id = query("SELECT LAST_INSERT_ID()") { rs => rs.getInt(1) }.get
		run
	}
}
