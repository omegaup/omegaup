import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
	override def proguardInJars = super.proguardInJars +++ scalaLibraryPath
	
	override def mainClass = Some("omegaup.grader.Manager")

	override def proguardOptions = List(
		"-dontskipnonpubliclibraryclasses",
		"-dontskipnonpubliclibraryclassmembers",
		"-dontobfuscate",
		"-dontpreverify",
		"-dontnote",
		"-dontwarn",
		"-keep interface scala.ScalaObject",
		"-keep class omegaup.*",
		"-keep class omegaup.data.*",
		"-keep class omegaup.grader.*",
		"-keep class omegaup.runner.*",
		"-keepclassmembers class omegaup.data.* { *; }",
		"-keepclassmembers class omegaup.runner.* { *; }",
		"-keepclassmembers class omegaup.grader.Manager { public static void main(String[]); }",
		"-keep class scala.collection.JavaConversions",
		"-keep class org.mortbay.log.Slf4jLog",
		proguardKeepLimitedSerializability
	)

	override def libraryDependencies = Set(
		"org.mortbay.jetty" % "jetty" % "6.1.26",
		"org.mortbay.jetty" % "jetty-sslengine" % "6.1.26",
		"org.mortbay.jetty" % "jetty-client" % "6.1.26",
		"net.liftweb" % "lift-json_2.8.1" % "2.2-RC5",
		"org.slf4j" % "slf4j-jdk14" % "1.6.1"
	) ++ super.libraryDependencies
	
	val mysql = "mysql" % "mysql-connector-java" % "5.1.12" % "runtime"
	val scalatest = "org.scalatest" % "scalatest" % "1.2" % "test"
	
	val common = "omegaup" % "omegaup-common" % "1.0" from "file://"+(new java.io.File("../common/target/scala_2.8.1/common_2.8.1-1.0.jar").getCanonicalPath)
	val runner = "omegaup" % "omegaup-runner" % "1.0" from "file://"+(new java.io.File("../runner/target/scala_2.8.1/omegaup-runner_2.8.1-1.0.jar").getCanonicalPath)
}
