import sbt._
class Plugins(info: ProjectInfo) extends PluginDefinition(info) {
	val proguard = "org.scala-tools.sbt" % "sbt-proguard-plugin" % "0.0.5"
	val squeryl = "org.squeryl" % "squeryl_2.8.1" % "0.9.4-RC3"
}
