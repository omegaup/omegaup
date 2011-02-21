import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
	override def proguardInJars = super.proguardInJars +++ scalaLibraryPath
	
	override def mainClass = Some("omegaup.grader.Grader")

	override def proguardOptions = List(
		"-keepclasseswithmembers public class * { public static void main(java.lang.String[]); }",
		"-dontobfuscate",
		proguardKeepLimitedSerializability,
		"-keep interface scala.ScalaObject"
	)

	override def libraryDependencies = Set(
		"mysql" % "mysql-connector-java" % "5.1.12",
		"org.mortbay.jetty" % "jetty" % "6.1.26",
		"org.mortbay.jetty" % "jetty-sslengine" % "6.1.26",
		"org.mortbay.jetty" % "jetty-client" % "6.1.26",
		"net.liftweb" % "lift-json_2.8.1" % "2.2-RC5",
		"org.slf4j" % "slf4j-jdk14" % "1.6.1" % "compile"
	) ++ super.libraryDependencies
	
	val scalatest = "org.scalatest" % "scalatest" % "1.2" % "test"
}
