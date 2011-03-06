package omegaup.data

import java.sql._

object Validator extends Enumeration {
	type Validator = Value
	val  Remote = Value(1, "remote")
	val  Literal = Value(2, "literal")
	val  Token = Value(3, "token")
	val  TokenCaseless = Value(4, "token-caseless")
	val  TokenNumeric = Value(5, "token-numeric")
}

object Server extends Enumeration {
	type Servidor = Value
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

import Validator._
import Veredict._
import Status._
import Server._
import Language._
import Order._

class Problem(
	var id: Long,
	var public: Long = 1,
	var author: Long = 0,
	var title: String = "",
	var alias: Option[String] = None,
	var validator: Validator = Validator.TokenNumeric,
	var server: Option[Servidor] = None,
	var remote_id: Option[String] = None,
	var time_limit: Option[Long] = Some(1),
	var memory_limit: Option[Long] = Some(64),
	var vists: Long = 0,
	var submissions: Long = 0,
	var accepted: Long = 0,
	var difficulty: Double = 0,
	var creation_date: Timestamp = new Timestamp(0),
	var source: String = "",
	var order: Order = Order.Normal) {
}

class Run(
	var id: Long = 0,
	var user: Long = 0,
	var problem: Problem = null,
	var contest: Option[Long] = None,
	var guid: String = "",
	var language: Language = Language.C,
	var status: Status = Status.New,
	var veredict: Veredict = Veredict.JudgeError,
	var runtime: Long = 0,
	var memory: Long = 0,
	var score: Double = 0,
	var contest_score: Double = 0,
	var ip: String = "127.0.0.1",
	var timestamp: Timestamp = new Timestamp(0)
) {
}
