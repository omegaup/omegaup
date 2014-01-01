name := "Common"

version := "1.1"

organization := "omegaup"

scalaVersion := "2.10.3"

scalacOptions ++= Seq("-unchecked", "-deprecation", "-feature")

libraryDependencies ++= Seq(
	"net.liftweb" %% "lift-json" % "2.5.1",
	"org.slf4j" % "log4j-over-slf4j" % "1.7.5",
	"ch.qos.logback" % "logback-core" % "1.0.13",
	"ch.qos.logback" % "logback-classic" % "1.0.13",
	"commons-codec" % "commons-codec" % "1.8",
	"org.scalatest" %% "scalatest" % "2.0" % "test"
)
