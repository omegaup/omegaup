package omegaup.grader.drivers
  
import omegaup._
import omegaup.data._
import omegaup.grader._

trait Driver {
  def run(ctx: RunContext, run: Run): Run
  def grade(ctx: RunContext, run: Run): Run
}
