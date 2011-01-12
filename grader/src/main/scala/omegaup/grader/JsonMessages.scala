package omegaup.grader

case class GradeInputMessage(id: Int)
case class GradeOutputMessage(status: String = "ok", error: Option[String] = None)
case class RegisterInputMessage(port: Int)
case class RegisterOutputMessage(status: String = "ok", error: Option[String] = None)
case class NullMessage()
