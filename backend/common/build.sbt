name := "Common"

version := "1.1"

organization := "omegaup"

scalaVersion := "2.10.3"

scalacOptions ++= Seq("-unchecked", "-deprecation", "-feature")

libraryDependencies ++= Seq(
	"net.liftweb" %% "lift-json" % "2.5.1",
	"org.slf4j" % "log4j-over-slf4j" % "1.7.6",
	"org.eclipse.jetty" % "jetty-util" % "9.1.3.v20140225",
	"ch.qos.logback" % "logback-core" % "1.1.1",
	"ch.qos.logback" % "logback-classic" % "1.1.1",
	"commons-codec" % "commons-codec" % "1.9",
	"org.scalatest" %% "scalatest" % "2.1.2" % "test"
)
