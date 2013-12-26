name := "Grader"

version := "1.0"

organization := "omegaup"

scalaVersion := "2.9.1"

libraryDependencies ++= Seq(
		"org.eclipse.jetty" % "jetty-server" % "8.1.14.v20131031",
		"org.eclipse.jetty.orbit" % "javax.servlet" % "3.0.0.v201112011016" artifacts(Artifact("javax.servlet", "jar", "jar")),
		"org.eclipse.jetty" % "jetty-client" % "8.1.14.v20131031",
		"org.eclipse.jetty" % "jetty-security" % "8.1.14.v20131031",
		"org.eclipse.jetty" % "jetty-websocket" % "8.1.14.v20131031",
		"net.liftweb" % "lift-json_2.9.1" % "2.4-M4",
		"org.slf4j" % "log4j-over-slf4j" % "1.7.5",
		"ch.qos.logback" % "logback-core" % "1.0.11",
		"ch.qos.logback" % "logback-classic" % "1.0.11",
		"commons-codec" % "commons-codec" % "1.8",
		"org.scalatest" %% "scalatest" % "1.9.1" % "test",
		"com.h2database" % "h2" % "1.3.153" % "runtime",
		"mysql" % "mysql-connector-java" % "5.1.12" % "runtime",
		"omegaup" % "omegaup-common" % "1.0" from "file://"+(new java.io.File("../common/target/scala-2.9.1/common_2.9.1-1.0.jar").getCanonicalPath),
		"omegaup" % "omegaup-runner" % "1.0" from "file://"+(new java.io.File("../runner/target/scala-2.9.1/runner_2.9.1-1.0.jar").getCanonicalPath)
)

seq(ProguardPlugin.proguardSettings :_*)

proguardOptions ++= Seq(
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
		"-keepclassmembers class omegaup.Service { *; }",
		"-keep class scala.collection.JavaConversions",
		"-keep class org.eclipse.jetty.util.log.Slf4jLog",
		"-keep class ch.qos.logback.classic.Logger",
		keepMain("omegaup.Service"),
		keepLimitedSerializability
)

makeInJarFilter <<= (makeInJarFilter) {
	(makeInJarFilter) => {
		(file) => file match {
			case _ => makeInJarFilter(file) + ",!**/ECLIPSEF.RSA,!**/ECLIPSEF.SF,!about.html"
		}
	}
}
