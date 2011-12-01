name := "Common"

version := "1.0"

organization := "omegaup"

scalaVersion := "2.9.1"

libraryDependencies ++= Seq(
	"net.liftweb" % "lift-json_2.9.1" % "2.4-M4",
	"org.slf4j" % "log4j-over-slf4j" % "1.6.2",
	"ch.qos.logback" % "logback-core" % "0.9.24",
	"ch.qos.logback" % "logback-classic" % "0.9.24",
	"org.scalatest" % "scalatest" % "1.3" % "test"
)
