import sbt._
class Project(info: ProjectInfo) extends DefaultProject(info) with ProguardProject {
  override def proguardInJars = super.proguardInJars +++ scalaLibraryPath

  override def proguardOptions = List(
    "-keep class Hi { main; }"
  )
}
