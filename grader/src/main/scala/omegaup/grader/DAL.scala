package omegaup.grader

import java.sql._
import omegaup.data._
import omegaup.Database._

import Veredict._
import Validator._
import Server._
import Language._

object GraderData {
	def run(id: Long)(implicit connection: Connection): Option[Run] =
		query("SELECT * FROM Runs AS r, Problems AS p WHERE p.problem_id = r.problem_id AND r.run_id = " + id) { rs =>
			new Run(
				id = rs.getLong("run_id"),
				contest = rs.getLong("contest_id") match {
					case 0 => if(rs.wasNull) None else Some(0)
					case x => Some(x)
				},
				guid = rs.getString("guid"),
				language = Language.withName(rs.getString("language")),
				status = Status.withName(rs.getString("status")),
				veredict = Veredict.withName(rs.getString("veredict")),
				problem = new Problem(
					 id = rs.getLong("p.problem_id"),
					 validator = Validator.withName(rs.getString("validator")),
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
					 }
				)
			)
		}
		
	def update(run: Run)(implicit connection: Connection): Run = {
		execute(
			"UPDATE Runs SET status = '" + run.status + "'" +
			", veredict = '" + run.veredict + "'" +
			", runtime = " + run.runtime +
			", memory = " + run.memory +
			", score = " + run.score +
			", contest_score = " + run.contest_score + " " +
			"WHERE run_id = " + run.id
		)
		run
	}
		
	def insert(run: Run)(implicit connection: Connection): Run = {
		execute(
			"INSERT INTO Runs (user_id, problem_id, guid, language, veredict, ip) VALUES(" +
				run.user + ", " +
				run.problem.id + ", " +
				"'" + run.guid + "', " +
				"'" + run.language + "', " +
				"'" + run.veredict + "', " +
				"'" + run.ip + "'" +
			")"
		)
                run.id = query("SELECT LAST_INSERT_ID()") { rs => rs.getInt(1) }.get
		run
	}
}
