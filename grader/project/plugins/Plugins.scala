import sbt._
class Plugins(info: ProjectInfo) extends PluginDefinition(info) {
	val proguard = "org.scala-tools.sbt" % "sbt-proguard-plugin" % "0.0.5"
	val squeryl = "org.squeryl" % "squeryl_2.8.1" % "0.9.4-RC3"
	val mysql = "mysql" % "mysql-connector-java" % "5.1.12"
	val jetty6 = "org.mortbay.jetty" % "jetty" % "6.1.26"
}
