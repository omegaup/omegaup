import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) {
	override def libraryDependencies = Set(
		"net.liftweb" % "lift-json_2.8.1" % "2.2-RC5",
		"org.slf4j" % "slf4j-jdk14" % "1.6.1"
	) ++ super.libraryDependencies
	
	val scalatest = "org.scalatest" % "scalatest" % "1.2" % "test"
}
