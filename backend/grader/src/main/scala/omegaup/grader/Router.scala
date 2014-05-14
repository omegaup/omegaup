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
	def apply(run: Run): String
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
	val defaultQueueName = "#default"
	lexical.delimiters ++= List("(", ")", "[", "]", "{", "}", ",", ":", "==", "||", "&&")
	lexical.reserved += ("in", "runners", "condition", "contest", "user", "name", "slow", "problem", "true")

	def parse(input: String): (Map[RunnerEndpoint,String], RunRouter) = {
		info("Parsing RoutingDescription: {}", input)
		routingTable(new lexical.Scanner(input)) match {
			case Success(table, _) => {
				info("RoutingDescription parsed: {}", table)
				table
			}
			case NoSuccess(msg, input) => {
				System.err.println(input.pos.longString)
				throw new ParseException(msg, input.offset)
			}
		}
	}

	private def routingTable = phrase(rep(queue)) ^^ { (list: List[(String, List[RunnerEndpoint], RunMatcher)]) => (
		new HashMap[RunnerEndpoint,String] ++ list.map { case (name, runners, condition) =>
			runners.map(_ -> name)
		}.flatten,
		new RunRouterImpl(list.map { case (name, runners, condition) => condition -> name })
	)}
	private def queue = "{" ~ name ~ "," ~ runners ~ "," ~ condition ~ "}" ^^ { case "{" ~ name ~ "," ~ runners ~ "," ~ condition ~ "}" => (name, runners, condition) }
	private def name = "name" ~ ":" ~> stringLit
	private def runners = "runners" ~ ":" ~> stringList ^^ (
		_.map( x => {
				val idx = x.indexOf(":")
				if (idx == -1)
					new RunnerEndpoint(x, 21681)
				else
					new RunnerEndpoint(x.substring(0, idx), x.substring(idx + 1).toInt)
		})
	)
	private def condition = "condition" ~ ":" ~> expr
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
	private def opExpr: Parser[RunMatcher] = eqExpr | inExpr | slowExpr | trueExpr
	private def eqExpr: Parser[RunMatcher] = param ~ "==" ~ stringLit ^^ { case param ~ "==" ~ arg => new EqMatcher(param, arg) }
	private def inExpr: Parser[RunMatcher] = param ~ "in" ~ stringList ^^ { case param ~ "in" ~ arg => new InMatcher(param, arg) }
	private def slowExpr: Parser[RunMatcher] = "slow" ^^ { case "slow" => SlowMatcher }
	private def trueExpr: Parser[RunMatcher] = "true" ^^ { case "true" => TrueMatcher }

	private def param: Parser[String] = "contest" | "user" | "problem"
	private def stringList: Parser[List[String]] = "[" ~> rep1sep(stringLit, ",") <~ "]"

	private class RunRouterImpl(routingMap: List[(RunMatcher, String)]) extends Object with RunRouter with Log {
		def apply(run: Run): String = {
			for (entry <- routingMap) {
				debug("Run {} matching against {}", run, entry._1)
				if (entry._1(run)) {
					debug("Run {} matched into {}", run.id, entry._2)
					return entry._2
				}
			}
			debug("Run {} matched nothing. Returning {}", run.id, defaultQueueName)
			defaultQueueName
		}

		override def toString(): String = "RunRouter(" + routingMap.mkString(", ") + ")"
	}

	private trait RunMatcher {
		def apply(run: Run): Boolean
		def getParam(run: Run, param: String) = param match {
			case "contest" => {
				run.contest match {
					case None => ""
					case Some(contest) => contest.alias
				}
			}
			case "problem" => run.problem.alias
			case "user" => run.user.username
		}
	}

	private class EqMatcher(param: String, arg: String) extends Object with RunMatcher {
		def apply(run: Run): Boolean = getParam(run, param) == arg
		override def toString(): String = param + " == " + arg
	}

	private class InMatcher(param: String, arg: List[String]) extends Object with RunMatcher {
		private val set = new HashSet[String]() ++ arg
		def apply(run: Run): Boolean = set.contains(getParam(run, param))
		override def toString(): String = param + " in " + "[" + set.mkString(", ") + "]"
	}

	private class OrMatcher(arg: List[RunMatcher]) extends Object with RunMatcher {
		def apply(run: Run): Boolean = arg.exists(_(run))
		override def toString(): String = "(" + arg.mkString(" || ") + ")"
	}

	private class AndMatcher(arg: List[RunMatcher]) extends Object with RunMatcher {
		def apply(run: Run): Boolean = arg.forall(_(run))
		override def toString(): String = arg.mkString(" && ")
	}

	private object SlowMatcher extends Object with RunMatcher {
		def apply(run: Run): Boolean = run.problem.slow
		override def toString(): String = "slow"
	}

	private object TrueMatcher extends Object with RunMatcher {
		def apply(run: Run): Boolean = true
		override def toString(): String = "true"
	}
}

class RunnerRouter(dispatcherNames: Map[RunnerEndpoint, String], runRouter: RunRouter) extends ServiceInterface with Log {
	private val dispatchers = new HashMap[RunnerEndpoint, RunnerDispatcher] ++
	(new HashSet[String]() ++ dispatcherNames.map(_._2) + RoutingDescription.defaultQueueName).map { (queue: String) => {
		queue -> new RunnerDispatcher(queue, this)
	}}
	dispatcherNames.foreach { case (endpoint, queue) => register(endpoint.hostname, endpoint.port) }

	def status() = dispatchers.toMap.map(entry => {entry._1.toString -> entry._2.status})

	def register(hostname: String, port: Int): RegisterOutputMessage = {
		dispatch(new RunnerEndpoint(hostname, port)).register(hostname, port)
	}

	def deregister(hostname: String, port: Int): RegisterOutputMessage = {
		dispatch(new RunnerEndpoint(hostname, port)).deregister(hostname, port)
	}

	def addRunner(runner: RunnerService) = {
		dispatch(new RunnerEndpoint(runner.name, runner.port)).addRunner(runner)
	}

	def addRun(ctx: RunContext) = {
		dispatchers(runRouter(ctx.run)).addRun(ctx)
	}

	override def stop(): Unit = {
		dispatchers foreach (_._2.stop)
	}

	override def join(): Unit = {
		dispatchers foreach (_._2.join)
	}

	private def dispatch(endpoint: RunnerEndpoint): RunnerDispatcher = {
		if (dispatcherNames.contains(endpoint))
			dispatchers(dispatcherNames(endpoint))
		else
			dispatchers(RoutingDescription.defaultQueueName)
	}
}

class RunnerDispatcher(val name: String, router: RunnerRouter) extends ServiceInterface with Log {
	private val registeredEndpoints = scala.collection.mutable.HashMap.empty[RunnerEndpoint, Long]
	private val runnerQueue = scala.collection.mutable.Queue.empty[RunnerService]
	private val runQueue = scala.collection.mutable.Queue.empty[RunContext]
	private val runsInFlight = scala.collection.mutable.HashMap.empty[Long, RunContext]
	private val executor = Executors.newCachedThreadPool
	private var flightIndex: Long = 0
	private val lock = new Object

	private val pruner = new java.util.Timer("Flight pruner", true)
	pruner.scheduleAtFixedRate(
		new java.util.TimerTask() {
			override def run(): Unit = pruneFlights
		},
		Config.get("grader.flight_pruner.interval", 60) * 1000,
		Config.get("grader.flight_pruner.interval", 60) * 1000
	)

	def status() = lock.synchronized {
		QueueStatus(
			run_queue_length = runQueue.size,
			runner_queue_length = runnerQueue.size,
			runners = registeredEndpoints.keys.map(_.hostname).toList,
			running = runsInFlight.values.map(ifr => new Running(ifr.service.name, ifr.run.id.toInt)).toList
		)
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

		debug("Runner queue register {} length {} known endpoints {}", name, runnerQueue.size, registeredEndpoints.size)

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

		debug("Runner queue deregister {} length {} known endpoints {}", name, runnerQueue.size, registeredEndpoints.size)

		new RegisterOutputMessage()
	}

	def addRun(ctx: RunContext) = {
		ctx.queued()
		lock.synchronized {
			debug("Adding run {}", ctx)
			runQueue += ctx
			dispatchLocked
		}
	}

	def addRunner(runner: RunnerService) = {
		lock.synchronized {
			addRunnerLocked(runner)
		}
	}

	private class GradeTask(
		dispatcher: RunnerDispatcher,
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
			val future = dispatcher.executor.submit(new Callable[Run]() {
					override def call(): Run = ctx.trace(EventCategory.Runner) {
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
								case proxy: omegaup.runner.RunnerProxy => dispatcher.deregister(proxy.hostname, proxy.port)
								case _ => {}
							}

							// But do re-queue the run
							dispatcher.addRun(ctx)

							// And commit suicide
							throw e
						}
						case _ => {
							ctx.run.score = 0
							ctx.run.contest_score = 0
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
						case proxy: omegaup.runner.RunnerProxy => dispatcher.deregister(proxy.hostname, proxy.port)
						case _ => {}
					}

					ctx.run
				}
			} finally {
				dispatcher.flightFinished(flightIndex)
			}

			if (ctx.run.status != Status.Ready) {
				ctx.run = try {
					driver.grade(ctx, ctx.run.copy)
				} catch {
					case e: Exception => {
						error("Error while grading {}", e)
						ctx.run.score = 0
						ctx.run.contest_score = 0
						ctx.run.status = Status.Ready
						ctx.run.veredict = Veredict.JudgeError

						ctx.run
					}
				}
			}

			Manager.updateVeredict(ctx, ctx.run)
		}
	}

	private def runLocked() = {
		val runner = runnerQueue.dequeue
		val ctx = runQueue.dequeue
		ctx.dequeued

		ctx.startFlight(runner)
		runsInFlight += flightIndex -> ctx
		executor.submit(new GradeTask(this, flightIndex, ctx, OmegaUpDriver))

		flightIndex += 1
	}

	private def pruneFlights() = lock.synchronized {
		var cutoffTime = System.currentTimeMillis - Config.get("grader.runner.timeout", 10 * 60) * 1000
		runsInFlight.foreach { case (i, ctx) => {
			if (ctx.flightTime < cutoffTime) {
				warn("Expiring stale flight {}, run {}", ctx.service, ctx.run.id)
				ctx.service match {
					case proxy: RunnerProxy => deregisterLocked(new RunnerEndpoint(proxy.hostname, proxy.port))
					case _ => {}
				}
				runsInFlight -= i
			}
		}}
	}

	private def flightFinished(flightIndex: Long) = lock.synchronized {
		if (runsInFlight.contains(flightIndex)) {
			var ctx = runsInFlight(flightIndex)
			runsInFlight -= flightIndex
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

	private def dispatchLocked() = {
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

		if (!runnerQueue.isEmpty && !runQueue.isEmpty) {
			debug("But there's enough to run something!")
			runLocked
		}
	}

	override def stop(): Unit = {
		executor.shutdown
	}

	override def join(): Unit = {
		executor.awaitTermination(Long.MaxValue, TimeUnit.NANOSECONDS)
	}
}

/* vim: set noexpandtab: */
