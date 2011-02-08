package omegaup.runner

case class RunCaseResult(name: String, status: String, time: Int, memory: Int, output: Option[String] = None, context: Option[String] = None)
case class CaseData(name: String, data: String)

case class CompileInputMessage(lang: String, code: List[String], master_lang: Option[String] = None, master_code: Option[List[String]] = None)
case class CompileOutputMessage(status: String = "ok", error: Option[String] = None, token: Option[String] = None)

case class RunInputMessage(token: String, timeLimit: Float = 1, memoryLimit: Int = 65535, outputLimit: Int = 10, input: Option[String] = None, cases: Option[List[CaseData]] = None)
case class RunOutputMessage(status: String = "ok", error: Option[String] = None, results: Option[List[RunCaseResult]] = None)

case class InputOutputMessage(status: String = "ok", error: Option[String] = None)

case class NullMessage()

// from Grader
case class RegisterInputMessage(port: Int)
case class RegisterOutputMessage(status: String = "ok", error: Option[String] = None)
