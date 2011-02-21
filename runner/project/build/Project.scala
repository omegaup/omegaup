import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
	override def proguardInJars = super.proguardInJars +++ scalaLibraryPath
	
	override def mainClass = Some("omegaup.runner.Runner")

	override def proguardOptions = List(
		"-dontskipnonpubliclibraryclasses",
		"-dontskipnonpubliclibraryclassmembers",
		"-dontobfuscate",
		"-dontpreverify",
		"-dontnote",
		"-dontwarn",
		"-keep interface scala.ScalaObject",
		"-keep class omegaup.*",
		"-keep class omegaup.runner.*",
		"-keepclassmembers class omegaup.runner.* { *; }",
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
	
	val scalatest = "org.scalatest" % "scalatest" % "1.2" % "test"
}
