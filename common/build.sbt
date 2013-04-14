name := "Common"

version := "1.0"

organization := "omegaup"

scalaVersion := "2.9.1"

libraryDependencies ++= Seq(
	"net.liftweb" % "lift-json_2.9.1" % "2.4-M4",
	"org.slf4j" % "log4j-over-slf4j" % "1.7.5",
	"ch.qos.logback" % "logback-core" % "1.0.11",
	"ch.qos.logback" % "logback-classic" % "1.0.11",
	"org.scalatest" % "scalatest" % "1.3" % "test"
)
