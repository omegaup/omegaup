import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
	override def proguardInJars = super.proguardInJars +++ scalaLibraryPath
	
	override def mainClass = Some("openjuan.grader.Grader")

	override def proguardOptions = List(
		"-keepclasseswithmembers public class * { public static void main(java.lang.String[]); }",
		"-dontoptimize",
		proguardKeepLimitedSerializability,
		"-keep interface scala.ScalaObject"
	)

	override def libraryDependencies = Set(
		"org.squeryl" % "squeryl_2.8.1" % "0.9.4-RC3",
		"mysql" % "mysql-connector-java" % "5.1.12",
		"org.mortbay.jetty" % "jetty" % "6.1.26"
	) ++ super.libraryDependencies
}
