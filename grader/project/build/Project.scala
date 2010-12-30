import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
	override def proguardInJars = super.proguardInJars +++ scalaLibraryPath
	
	override def mainClass = Some("openjuan.grader.Grader")

	override def proguardOptions = List(
		"-keepclasseswithmembers public class * { public static void main(java.lang.String[]); }",
		"-dontobfuscate",
		proguardKeepLimitedSerializability,
		"-keep interface scala.ScalaObject"
	)

	override def libraryDependencies = Set(
		"org.squeryl" % "squeryl_2.8.1" % "0.9.4-RC3",
		"mysql" % "mysql-connector-java" % "5.1.12",
		"org.mortbay.jetty" % "jetty" % "6.1.26",
		"net.liftweb" % "lift-json_2.8.1" % "2.2-RC5",
		"org.clapper" %% "grizzled-slf4j" % "0.3.2"
	) ++ super.libraryDependencies
	
	val scalatest = "org.scalatest" % "scalatest" % "1.2"
}
