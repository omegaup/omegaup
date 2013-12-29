package omegaup.data

case class NullMessage()

// from Runner
case class RunCaseResult(name: String, status: String, time: Int, memory: Int, output: Option[String] = None, context: Option[String] = None)
case class CaseData(name: String, data: String)

case class CompileInputMessage(lang: String, code: List[(String, String)], master_lang: Option[String] = None, master_code: Option[List[(String, String)]] = None)
case class CompileOutputMessage(status: String = "ok", error: Option[String] = None, token: Option[String] = None)

case class RunInputMessage(token: String, timeLimit: Float = 1, memoryLimit: Int = 65535, outputLimit: Long = 10240, input: Option[String] = None, cases: Option[List[CaseData]] = None)
case class RunOutputMessage(status: String = "ok", error: Option[String] = None, results: Option[List[RunCaseResult]] = None)

case class InputOutputMessage(status: String = "ok", error: Option[String] = None)

// from Grader
case class ReloadConfigInputMessage(overrides: Option[Map[String, String]] = None)
case class ReloadConfigOutputMessage(status: String = "ok", error: Option[String] = None)
case class StatusOutputMessage(status: String = "ok", embedded_runner: Boolean = true, runner_queue_length: Int = 0, runners: Int = 0)
case class GradeInputMessage(id: Int)
case class GradeOutputMessage(status: String = "ok", error: Option[String] = None)
case class RegisterInputMessage(hostname: String, port: Int)
case class RegisterOutputMessage(status: String = "ok", error: Option[String] = None)

// for serializing judgement details
case class CaseVeredictMessage(name: String, veredict:String, score: Double)
case class GroupVeredictMessage(group: String, cases: List[CaseVeredictMessage], score: Double)
