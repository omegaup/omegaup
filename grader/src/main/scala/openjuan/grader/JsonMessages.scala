package openjuan.grader

case class GraderInputMessage(id: Int)
case class GraderOutputMessage(status: String = "ok", error: Option[String] = None)
