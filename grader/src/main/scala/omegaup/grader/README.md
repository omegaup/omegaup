# OmegaUp Grader

* **DAL.scala** -- Data Access Layer. All database queries must be encapsulated here.
* **Grader.scala** -- Main grading logic. Once all cases from a run have been executed, compare their output to the reference output and set the score for the run.
* **Manager.scala** -- The grader web service. Creates a small HTTP server that listens for grading requests.
* **drivers** -- Plugins for different problem-evaluating services.
** **drivers/Driver.scala** -- The interface for all drivers.
