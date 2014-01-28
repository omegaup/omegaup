package omegaup.data

import java.sql._

object Validator extends Enumeration {
	type Validator = Value
	val  Remote = Value(1, "remote")
	val  Literal = Value(2, "literal")
	val  Token = Value(3, "token")
	val  TokenCaseless = Value(4, "token-caseless")
	val  TokenNumeric = Value(5, "token-numeric")
	val  Custom = Value(6, "custom")
	val  Karel = Value(7, "karel")
}

object Server extends Enumeration {
	type Server = Value
	val  UVa = Value(1, "uva")
	val  LiveArchive = Value(2, "livearchive")
	val  PKU = Value(3, "pku")
	val  TJU = Value(4, "tju")
	val  SPOJ = Value(5, "spoj")
}

object Language extends Enumeration {
	type Language = Value
	val  C = Value(1, "c")
	val  Cpp = Value(2, "cpp")
	val  Java = Value(3, "java")
	val  Python = Value(4, "py")
	val  Ruby = Value(5, "rb")
	val  Perl = Value(6, "pl")
	val  CSharp = Value(7, "cs")
	val  Pascal = Value(8, "p")
	val  KarelPascal = Value(9, "kp")
	val  KarelJava = Value(10, "kj")
	val  Literal = Value(11, "cat")
	val  Haskell = Value(12, "hs")
	val  Cpp11 = Value(13, "cpp11")
}

object Status extends Enumeration {
	type Status = Value
	val  New = Value(1, "new")
	val  Waiting = Value(2, "waiting")
	val  Compiling = Value(3, "compiling")
	val  Running = Value(4, "running")
	val  Ready = Value(5, "ready")
}

object Veredict extends Enumeration {
	type Veredict = Value
	val  Accepted = Value(1, "AC")
	val  PartialAccepted = Value(2, "PA")
	val  PresentationError = Value(3, "PE")
	val  WrongAnswer = Value(4, "WA")
	val  TimeLimitExceeded = Value(5, "TLE")
	val  OutputLimitExceeded = Value(6, "OLE")
	val  MemoryLimitExceeded = Value(7, "MLE")
	val  RuntimeError = Value(8, "RTE")
	val  RestrictedFunctionError = Value(9, "RFE")
	val  CompileError = Value(10, "CE")
	val  JudgeError = Value(11, "JE")
}

object Order extends Enumeration {
	type Order = Value
	val Normal = Value(1, "normal")
	val Inverse = Value(2, "inverse")
}

object Feedback extends Enumeration {
	type Feedback = Value
	val Yes = Value(1, "yes")
	val No = Value(2, "no")
	val Partial = Value(3, "partial")
}

object PenaltyTimeStart extends Enumeration {
	type PenaltyTimeStart = Value
	val Contest = Value(1, "contest")
	val Problem = Value(2, "problem")
	val NoPenalty = Value(3, "none")
}

import Validator._
import Veredict._
import Status._
import Server._
import Language._
import Order._
import Feedback._
import PenaltyTimeStart._

class Contest(
	var id: Long = 0,
	var title: String = "",
	var description: String = "",
	var start_time: Timestamp = new Timestamp(0),
	var finish_time: Timestamp = new Timestamp(0),
	var window_length: Option[Int] = None,
	var director_id: Long = 0,
	var rerun_id: Int = 0,
	var public: Boolean = true,
	var token: String = "",
	var points_decay_factor: Double = 0,
	var scoreboard: Int = 80,
	var partial_score: Boolean = true,
	var submissions_gap: Int = 0,
	var feedback: Feedback = Feedback.Yes,
	var penalty: Int = 20,
	var penalty_time_start: PenaltyTimeStart = PenaltyTimeStart.Contest
) {
  def copy() =
    new Contest(
      id = this.id,
      title = this.title,
      description = this.description,
      start_time = this.start_time,
      finish_time = this.finish_time,
      window_length = this.window_length,
      director_id = this.director_id,
      rerun_id = this.rerun_id,
      public = this.public,
      token = this.token,
      points_decay_factor = this.points_decay_factor,
      scoreboard = this.scoreboard,
      partial_score = this.partial_score,
      submissions_gap = this.submissions_gap,
      feedback = this.feedback,
      penalty = this.penalty,
      penalty_time_start = this.penalty_time_start
    )
}

class Problem(
	var id: Long = 0,
	var public: Long = 1,
	var author: Long = 0,
	var title: String = "",
	var alias: String = "",
	var validator: Validator = Validator.TokenNumeric,
	var server: Option[Server] = None,
	var remote_id: Option[String] = None,
	var time_limit: Option[Long] = Some(3000),
	var memory_limit: Option[Long] = Some(64),
	var output_limit: Option[Long] = Some(10240),
	var visits: Long = 0,
	var submissions: Long = 0,
	var accepted: Long = 0,
	var difficulty: Double = 0,
	var creation_date: Timestamp = new Timestamp(0),
	var source: String = "",
	var order: Order = Order.Normal,
	var open_time: Option[Timestamp] = None,
	var points: Option[Double] = None,
	var tolerance: Double = 1e-6
) {
  def copy() =
    new Problem(
      id = this.id,
      public = this.public,
      author = this.author,
      title = this.title,
      alias = this.alias,
      validator = this.validator,
      server = this.server,
      remote_id = this.remote_id,
      time_limit = this.time_limit,
      memory_limit = this.memory_limit,
      output_limit = this.output_limit,
      visits = this.visits,
      submissions = this.submissions,
      accepted = this.accepted,
      difficulty = this.difficulty,
      creation_date = this.creation_date,
      source = this.source,
      order = this.order,
      open_time = this.open_time,
      points = this.points,
      tolerance = this.tolerance
    )
}

class Run(
	var id: Long = 0,
	var user: Long = 0,
	var problem: Problem = null,
	var contest: Option[Contest] = None,
	var guid: String = "",
	var language: Language = Language.C,
	var status: Status = Status.New,
	var veredict: Veredict = Veredict.JudgeError,
	var runtime: Long = 0,
	var memory: Long = 0,
	var score: Double = 0,
	var contest_score: Double = 0,
	var ip: String = "127.0.0.1",
	var time: Timestamp = new Timestamp(0),
	var submit_delay: Int = 0,
	var judged_by: Option[String] = None
) {
  def copy() =
    new Run(
      id = this.id,
      user = this.user,
      problem = this.problem.copy,
      contest = this.contest map(_.copy),
      guid = this.guid,
      language = this.language,
      status = this.status,
      veredict = this.veredict,
      runtime = this.runtime,
      memory = this.memory,
      score = this.score,
      contest_score = this.contest_score,
      ip = this.ip,
      time = this.time,
      submit_delay = this.submit_delay,
      judged_by = this.judged_by
    )
}
