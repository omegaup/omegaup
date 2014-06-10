package omegaup.grader

import java.util.concurrent._
import java.text.ParseException
import omegaup._
import omegaup.data._
import omegaup.grader.drivers._
import omegaup.runner._
import scala.util.parsing.combinator.syntactical._
import scala.collection.immutable.{HashMap, HashSet}

trait RunRouter {
	def apply(ctx: RunContext): Int
}

class RunnerEndpoint(val hostname: String, val port: Int) {
	def ==(o: RunnerEndpoint) = hostname == o.hostname && port == o.port
	override def hashCode() = 28227 + 97 * hostname.hashCode + port
	override def equals(other: Any) = other match {
		case x:RunnerEndpoint => hostname == x.hostname && port == x.port
		case _ => false
	}
	override def toString() = "%s:%d".format(hostname, port)
}

object RoutingDescription extends StandardTokenParsers with Log {
	lexical.delimiters ++= List("(", ")", "[", "]", "{", "}", ",", ":", "==", "!=", "||", "&&", "!")
	lexical.reserved += ("not", "in", "user", "slow", "problem", "true", "urgent", "contest", "practice", "rejudge")

	def parse(input: Iterable[String]): RunRouter = {
		new RunRouterImpl(input.map { _.trim } .filter { _.length > 0 } .map {line => {
			info("Parsing routing rule: {}", line)
			routingRule(new lexical.Scanner(line)) match {
				case Success(rule, _) => {
					info("Routing rule parsed: {}", rule)
					rule
				}
				case NoSuccess(msg, input) => {
					System.err.println(input.pos.longString)
					throw new ParseException(msg, input.offset)
				}
			}
		}}.toList)
	}

	private def routingRule = phrase(queueName ~ ":" ~ expr) ^^ { case queueId ~ ":" ~ condition => condition -> queueId }
	private def queueName: Parser[Int] = ("urgent" ^^ { case "urgent" => 0 }) | ("contest" ^^ { case "contest" => 2 }) |
			("practice" ^^ { case "practice" => 4 }) | ("rejudge" ^^ { case "rejudge" => 6 })
	private def expr: Parser[RunMatcher] = ( "(" ~> expr <~ ")" ) | orExpr
	private def orExpr: Parser[RunMatcher] = rep1sep(andExpr, "||") ^^ { (andList: List[RunMatcher]) =>
		andList.length match {
			case 1 => andList(0)
			case _ => new OrMatcher(andList)
		}
	}
	private def andExpr: Parser[RunMatcher] = rep1sep(opExpr, "&&") ^^ { (opList: List[RunMatcher]) =>
		opList.length match {
			case 1 => opList(0)
			case _ => new AndMatcher(opList)
		}
	}
	private def opExpr: Parser[RunMatcher] = eqExpr | neqExpr | inExpr | notInExpr | slowExpr | rejudgeExpr | trueExpr | notExpr
	private def notExpr: Parser[RunMatcher] = "!" ~ opExpr ^^ { case "!" ~ cond => new NotMatcher(cond) }
	private def neqExpr: Parser[RunMatcher] = param ~ "!=" ~ stringLit ^^ { case param ~ "!=" ~ arg => new NeqMatcher(param, arg) }
	private def eqExpr: Parser[RunMatcher] = param ~ "==" ~ stringLit ^^ { case param ~ "==" ~ arg => new EqMatcher(param, arg) }
	private def inExpr: Parser[RunMatcher] = param ~ "in" ~ stringList ^^ { case param ~ "in" ~ arg => new InMatcher(param, arg) }
	private def notInExpr: Parser[RunMatcher] = param ~ "not" ~ "in" ~ stringList ^^ {
		case param ~ "not" ~ "in" ~ arg => new NotMatcher(new InMatcher(param, arg))
	}
	private def slowExpr: Parser[RunMatcher] = "slow" ^^ { case "slow" => SlowMatcher }
	private def rejudgeExpr: Parser[RunMatcher] = "rejudge" ^^ { case "rejudge" => RejudgeMatcher }
	private def trueExpr: Parser[RunMatcher] = "true" ^^ { case "true" => TrueMatcher }

	private def param: Parser[String] = "contest" | "user" | "problem"
	private def stringList: Parser[List[String]] = "[" ~> rep1sep(stringLit, ",") <~ "]"

	private class RunRouterImpl(routingMap: Iterable[(RunMatcher, Int)]) extends Object with RunRouter with Log {
		def apply(ctx: RunContext): Int = {
			val slow = if (ctx.run.problem.slow) 1 else 0
			for (entry <- routingMap) {
				debug("Run {} matching against {}", ctx.run, entry._1)
				if (entry._1(ctx)) {
					debug("Run {} matched. Priority {}", ctx.run.id, entry._2 + slow)
					return entry._2 + slow
				}
			}
			val queue = if (ctx.rejudge) {
				6
			} else if (ctx.run.contest.isEmpty) {
				4
			} else if (ctx.run.contest.get.urgent) {
				0
			} else {
				2
			}
			debug("Run {} matched nothing. Default priority of {}", ctx.run.id, queue + slow)
			queue + slow
		}

		override def toString(): String = "RunRouter(" + routingMap.mkString(", ") + ")"
	}

	private trait RunMatcher {
		def apply(ctx: RunContext): Boolean
		def getParam(ctx: RunContext, param: String) = param match {
			case "contest" => {
				ctx.run.contest match {
					case None => ""
					case Some(contest) => contest.alias
				}
			}
			case "problem" => ctx.run.problem.alias
			case "user" => ctx.run.user.username
		}
	}

	private class NeqMatcher(param: String, arg: String) extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = getParam(ctx, param) != arg
		override def toString(): String = param + " != " + arg
	}

	private class EqMatcher(param: String, arg: String) extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = getParam(ctx, param) == arg
		override def toString(): String = param + " == " + arg
	}

	private class InMatcher(param: String, arg: List[String]) extends Object with RunMatcher {
		private val set = new HashSet[String]() ++ arg
		def apply(ctx: RunContext): Boolean = set.contains(getParam(ctx, param))
		override def toString(): String = param + " in " + "[" + set.mkString(", ") + "]"
	}

	private class NotMatcher(expr: RunMatcher) extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = !expr(ctx)
		override def toString(): String = "! " + expr
	}

	private class OrMatcher(arg: List[RunMatcher]) extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = arg.exists(_(ctx))
		override def toString(): String = "(" + arg.mkString(" || ") + ")"
	}

	private class AndMatcher(arg: List[RunMatcher]) extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = arg.forall(_(ctx))
		override def toString(): String = arg.mkString(" && ")
	}

	private object SlowMatcher extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = ctx.run.problem.slow
		override def toString(): String = "slow"
	}

	private object RejudgeMatcher extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = ctx.rejudge
		override def toString(): String = "rejudge"
	}

	private object TrueMatcher extends Object with RunMatcher {
		def apply(ctx: RunContext): Boolean = true
		override def toString(): String = "true"
	}
}

object RunnerDispatcher extends ServiceInterface with Log {
	private val registeredEndpoints = scala.collection.mutable.HashMap.empty[RunnerEndpoint, Long]
	private val runnerQueue = scala.collection.mutable.Queue.empty[RunnerService]
	private val runQueue = new Array[scala.collection.mutable.Queue[RunContext]](8)
	private val runsInFlight = scala.collection.mutable.HashMap.empty[Long, RunContext]
	private val executor = Executors.newCachedThreadPool
	private var flightIndex: Long = 0
	private var runRouter = RoutingDescription.parse(List[String]())
	private var slowThreshold: Int = 50
	private var slowRuns: Int = 0
	private val lock = new Object

	private val pruner = new java.util.Timer("Flight pruner", true)
	pruner.scheduleAtFixedRate(
		new java.util.TimerTask() {
			override def run(): Unit = pruneFlights
		},
		Config.get("grader.flight_pruner.interval", 60) * 1000,
		Config.get("grader.flight_pruner.interval", 60) * 1000
	)

	for (i <- 0 until runQueue.length) runQueue(i) = scala.collection.mutable.Queue.empty[RunContext]

	def status() = lock.synchronized {
		QueueStatus(
			run_queue_length = runQueue.foldLeft(0)(_+_.size),
			runner_queue_length = runnerQueue.size,
			runners = registeredEndpoints.keys.map(_.hostname).toList,
			running = runsInFlight.values.map(ifr => new Running(ifr.service.name, ifr.run.id.toInt)).toList
		)
	}

	def updateConfiguration(description: String, slowThreshold: Int) = lock.synchronized {
		runRouter = RoutingDescription.parse(description.split("\n"))
		this.slowThreshold = slowThreshold

		val runs = scala.collection.mutable.MutableList.empty[RunContext]
		for (i <- 0 until runQueue.length) {
			while (!runQueue(i).isEmpty) runs += runQueue(i).dequeue
		}
		runs.sortBy(_.creationTime)

		runs.foreach { ctx => {
			runQueue(runRouter(ctx)) += ctx
			dispatchLocked
		}}
	}

	def register(hostname: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(hostname, port)

		lock.synchronized {
			if (!registeredEndpoints.contains(endpoint)) {
				val proxy = new RunnerProxy(endpoint.hostname, endpoint.port) 
				info("Registering {}", proxy)
				registeredEndpoints += endpoint -> System.currentTimeMillis
				addRunner(proxy)
			}
			registeredEndpoints(endpoint) = System.currentTimeMillis
		}

		debug("Runner queue register length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	private def deregisterLocked(endpoint: RunnerEndpoint) = {
		if (registeredEndpoints.contains(endpoint)) {
			info("De-registering {}", endpoint)
			registeredEndpoints -= endpoint
		}
	}

	def deregister(hostname: String, port: Int): RegisterOutputMessage = {
		val endpoint = new RunnerEndpoint(hostname, port)

		lock.synchronized {
			deregisterLocked(endpoint)
		}

		debug("Runner queue deregister length {} known endpoints {}", runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	def addRun(ctx: RunContext) = {
		ctx.queued()
		lock.synchronized {
			debug("Adding run {}", ctx)
			runQueue(runRouter(ctx)) += ctx
			dispatchLocked
		}
	}

	def addRunner(runner: RunnerService) = {
		lock.synchronized {
			addRunnerLocked(runner)
		}
	}

	private class GradeTask(
		flightIndex: Long,
		ctx: RunContext,
		driver: Driver
	) extends Runnable {
		override def run(): Unit = {
			try {
				gradeTask
			} catch {
				case e: Exception => {
					error("Error while running {}: {}", ctx.run.id, e)
				}
			}
		}

		private def gradeTask() = {
			val future = RunnerDispatcher.executor.submit(new Callable[Run]() {
					override def call(): Run = ctx.trace(EventCategory.Runner, "runner" -> ctx.service.name) {
						driver.run(ctx, ctx.run.copy)
					}
			})

			ctx.run = try {
				future.get(Config.get("grader.runner.timeout", 10 * 60) * 1000, TimeUnit.MILLISECONDS)
			} catch {
				case e: ExecutionException => {
					error("Submission {} {} failed - {} {}",
						ctx.run.problem.alias,
						ctx.run.id,
						e.getCause.toString,
						e.getCause.getStackTrace
					)

					e.getCause match {
						case inner: java.net.SocketException => {
							// Probably a network error of some sort. No use in re-queueing the runner.
							ctx.service match {
								case proxy: omegaup.runner.RunnerProxy => RunnerDispatcher.deregister(proxy.hostname, proxy.port)
								case _ => {}
							}

							// But do re-queue the run
							RunnerDispatcher.addRun(ctx)

							// And commit suicide
							throw e
						}
						case _ => {
							ctx.run.score = 0
							ctx.run.contest_score = ctx.run.contest match {
								case None => None
								case Some(x) => Some(0)
							}
							ctx.run.status = Status.Ready
							ctx.run.veredict = Veredict.JudgeError
						}
					}

					ctx.run
				}
				case e: TimeoutException => {
					error("Submission {} {} timed out - {} {}",
						ctx.run.problem.alias,
						ctx.run.id,
						e.toString,
						e.getStackTrace)

					// Probably a network error of some sort. No use in re-queueing the runner.
					ctx.service match {
						case proxy: omegaup.runner.RunnerProxy => RunnerDispatcher.deregister(proxy.hostname, proxy.port)
						case _ => {}
					}

					ctx.run
				}
			} finally {
				RunnerDispatcher.flightFinished(flightIndex)
			}

			if (ctx.run.status != Status.Ready) {
				ctx.run = try {
					driver.grade(ctx, ctx.run.copy)
				} catch {
					case e: Exception => {
						error("Error while grading {}", e)
						ctx.run.score = 0
						ctx.run.contest_score = ctx.run.contest match {
							case None => None
							case Some(x) => Some(0)
						}
						ctx.run.status = Status.Ready
						ctx.run.veredict = Veredict.JudgeError

						ctx.run
					}
				}
			}

			Manager.updateVeredict(ctx, ctx.run)
		}
	}

	private def pruneFlights() = lock.synchronized {
		var cutoffTime = System.currentTimeMillis - Config.get("grader.runner.timeout", 10 * 60) * 1000
		var pruned = false
		runsInFlight.foreach { case (i, ctx) => {
			if (ctx.flightTime < cutoffTime) {
				warn("Expiring stale flight {}, run {}", ctx.service, ctx.run.id)
				ctx.service match {
					case proxy: RunnerProxy => deregisterLocked(new RunnerEndpoint(proxy.hostname, proxy.port))
					case _ => {}
				}
				runsInFlight -= i
				if (ctx.run.problem.slow) {
					slowRuns -= 1
				}
				pruned = true
			}
		}}
		if (pruned) {
			dispatchLocked
		}
	}

	private def flightFinished(flightIndex: Long) = lock.synchronized {
		if (runsInFlight.contains(flightIndex)) {
			var ctx = runsInFlight(flightIndex)
			runsInFlight -= flightIndex
			if (ctx.run.problem.slow) {
				slowRuns -= 1
			}
			addRunnerLocked(ctx.service)
		} else {
			error("Lost track of flight {}!", flightIndex)
			throw new RuntimeException("Flight corrupted, bail out")
		}
	}

	private def addRunnerLocked(runner: RunnerService) = {
		debug("Adding runner {}", runner)
		if (!runnerQueue.contains(runner))
			runnerQueue += runner
		runner match {
			case proxy: omegaup.runner.RunnerProxy => {
				val endpoint = new RunnerEndpoint(proxy.hostname, proxy.port)
				registeredEndpoints(endpoint) = System.currentTimeMillis
			}
			case _ => {}
		}
		dispatchLocked
	}

	private def dispatchLocked(): Unit = {
		// Prune any runners that are not registered or haven't communicated in a while.
		debug("Before pruning the queue {}", status)
		var cutoffTime = System.currentTimeMillis -
			Config.get("grader.runner.queue_timeout", 10 * 60 * 1000)
		runnerQueue.dequeueAll (
			_ match {
				case proxy: omegaup.runner.RunnerProxy => {
					val endpoint = new RunnerEndpoint(proxy.hostname, proxy.port)
					// Also expire stale endpoints.
					if (registeredEndpoints.contains(endpoint) &&
							registeredEndpoints(endpoint) < cutoffTime) {
						warn("Stale endpoint {}", proxy)
						deregister(endpoint.hostname, endpoint.port)
					}
					!registeredEndpoints.contains(endpoint)
				}
				case _ => false
			}
		)

		debug("After pruning the queue {}", status)

		while (!runnerQueue.isEmpty) {
			debug("But there's enough to run something!")
			val queue = selectRunQueueLocked
			if (queue.isEmpty) return
			runLocked(runnerQueue.dequeue, runQueue(queue.get).dequeue)
		}
	}

	private def selectRunQueueLocked(): Option[Integer] = {
		val canScheduleSlowRun = 100 * slowRuns < slowThreshold * registeredEndpoints.size
		for (i <- 0 until runQueue.length) {
			if (!runQueue(i).isEmpty && (i % 2 == 0 || canScheduleSlowRun)) {
				return Some(i)
			}
		}
		return None
	}

	private def runLocked(runner: RunnerService, ctx: RunContext) = {
		ctx.dequeued(runner.name)

		ctx.startFlight(runner)
		runsInFlight += flightIndex -> ctx
		if (ctx.run.problem.slow) {
			slowRuns += 1
		}
		executor.submit(new GradeTask(flightIndex, ctx, OmegaUpDriver))

		flightIndex += 1
	}

	override def stop(): Unit = {
		executor.shutdown
	}

	override def join(): Unit = {
		executor.awaitTermination(Long.MaxValue, TimeUnit.NANOSECONDS)
	}
}

/* vim: set noexpandtab: */
