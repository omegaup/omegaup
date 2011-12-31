name := "Grader"

version := "1.0"

organization := "omegaup"

scalaVersion := "2.9.1"

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
        "-keepclassmembers class omegaup.grader.Manager { *; }",
        "-keep class scala.collection.JavaConversions",
        "-keep class org.mortbay.log.Slf4jLog",
	"-keep class ch.qos.logback.classic.Logger",
        keepMain("omegaup.grader.Manager"),
        keepLimitedSerializability
)

libraryDependencies ++= Seq(
        "org.mortbay.jetty" % "jetty" % "6.1.26",
        "org.mortbay.jetty" % "jetty-sslengine" % "6.1.26",
        "org.mortbay.jetty" % "jetty-client" % "6.1.26",
        "net.liftweb" % "lift-json_2.9.1" % "2.4-M4",
	"org.slf4j" % "log4j-over-slf4j" % "1.6.2",
	"ch.qos.logback" % "logback-core" % "0.9.24",
	"ch.qos.logback" % "logback-classic" % "0.9.24",
        "org.scalatest" % "scalatest" % "1.4.RC2" % "test",
        "com.h2database" % "h2" % "1.3.153" % "runtime",
        "mysql" % "mysql-connector-java" % "5.1.12" % "runtime",
        "omegaup" % "omegaup-common" % "1.0" from "file://"+(new java.io.File("../common/target/scala-2.9.1/common_2.9.1-1.0.jar").getCanonicalPath),
        "omegaup" % "omegaup-runner" % "1.0" from "file://"+(new java.io.File("../runner/target/scala-2.9.1/runner_2.9.1-1.0.jar").getCanonicalPath)
)
