package omegaup.grader.drivers
  
import omegaup._
import omegaup.data._

trait Driver {
  def run(run: Run, runner: RunnerService): Run
  def grade(run: Run): Run
}
