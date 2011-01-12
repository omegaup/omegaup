package omegaup.runner

case class RunCaseResult(name: String, status: String, time: Int, memory: Int, output: Option[String] = None)
case class CaseData(name: String, data: String)

case class CompileInputMessage(lang: String, code: List[String])
case class CompileOutputMessage(status: String = "ok", error: Option[String] = None, token: Option[String] = None)

case class RunInputMessage(token: String, input: String)
case class RunOutputMessage(status: String = "ok", error: Option[String] = None, results: Option[List[RunCaseResult]] = None)

case class InputInputMessage(input: String, cases: List[CaseData])
case class InputOutputMessage(status: String = "ok", error: Option[String] = None)

case class NullMessage()

// from Grader
case class RegisterInputMessage(port: Int)
case class RegisterOutputMessage(status: String = "ok", error: Option[String] = None)
