parallelExecution in Global := false

lazy val root = project.in(file(".")).aggregate(runner, grader)

lazy val common = project

lazy val runner = project.dependsOn(common)

lazy val grader = project.dependsOn(common, runner)
