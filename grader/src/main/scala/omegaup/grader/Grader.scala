package omegaup.grader

import java.io._
import java.util._
import java.util.regex.Pattern
import java.util.zip._
import scala.collection.mutable
import scala.collection.immutable.Map
import omegaup._
import omegaup.data._
import Veredict._

trait Grader extends Object with Log {
	def grade(run: Run): Unit = {
		val id = run.id
		val alias = run.problem.alias
		val zip = new File(Config.get("grader.root", ".") + "/" + id + ".zip")
		val dataDirectory = new File(zip.getParentFile.getCanonicalPath + "/" + id)
		dataDirectory.mkdirs()
		
		val input = new ZipInputStream(new FileInputStream(zip.getCanonicalPath))
		var entry: ZipEntry = input.getNextEntry
		val buffer = Array.ofDim[Byte](1024)
		var read: Int = 0
		
		while(entry != null) {
			val outFile = new File(entry.getName())
			val output = new FileOutputStream(dataDirectory.getCanonicalPath + "/" + outFile.getName)

			while( { read = input.read(buffer); read > 0 } ) {
				output.write(buffer, 0, read)
			}

			output.close
			input.closeEntry
			entry = input.getNextEntry
		}
		
		input.close
		
		zip.delete
		
		run.status = Status.Ready
		run.veredict = Veredict.Accepted
		run.runtime = 0
		run.memory = 0
		
                val metas = dataDirectory.listFiles
                  .filter { _.getName.endsWith(".meta") }
                  .map{ f => f.getName.substring(0, f.getName.length - 5)->(f, MetaFile.load(f.getCanonicalPath)) }
                  .toMap
		
		val weightsFile = new File(Config.get("problems.root", "./problems") + "/" + alias + "/testplan")

                trace("Finding Weights file in {}", weightsFile.getCanonicalPath)
		
		val weights:scala.collection.Map[String,scala.collection.Map[String,Double]] = if (weightsFile.exists) {
			val weights = new mutable.ListMap[String,mutable.ListMap[String,Double]]
			val fileReader = new BufferedReader(new FileReader(weightsFile))
			var line: String = null
	
			while( { line = fileReader.readLine(); line != null} ) {
				val tokens = line.split("\\s+")
			
				if(tokens.length == 2 && !tokens(0).startsWith("#")) {
                                        val idx = tokens(0).indexOf(".")

                                        val group = if (idx != -1) {
                                          	tokens(0).substring(0, idx)
                                        } else {
                                          	tokens(0)
                                        }

                                        if (!weights.contains(group)) {
                                                weights += (group -> new mutable.ListMap[String,Double])
                                        }

					try {
						weights(group) += (tokens(0) -> tokens(1).toDouble)
					}
				}
			}
		
			fileReader.close()
		
			weights
		} else {
			val weights = new mutable.ListMap[String,mutable.ListMap[String,Double]]

			val inputs = new File(Config.get("problems.root", "./problems") + "/" + alias + "/cases/")
			.listFiles
			.filter { _.getName.endsWith(".in") }

			for (f <- inputs) {
                                val caseName = f.getName.substring(0, f.getName.length - 3)

                                val idx = caseName.indexOf(".")

                                val group = if (idx != -1) {
                                	caseName.substring(0, idx)
                                } else {
                                        caseName
                                }

                                if (!weights.contains(group)) {
                                        weights += (group -> new mutable.ListMap[String,Double])
                                }

				try {
					weights(group) += (caseName -> 1.0)
				}
			}

			weights
		}

		metas.values.foreach { case (f, meta) => {
			run.runtime += math.round(1000 * meta("time").toDouble)
			run.memory = math.max(run.memory, meta("mem").toLong)
			val v = meta("status") match {
				case "XX" => Veredict.JudgeError
				case "OK" => Veredict.Accepted
				case "RE" => Veredict.RuntimeError
				case "TO" => Veredict.TimeLimitExceeded
				case "ML" => Veredict.MemoryLimitExceeded
				case "OL" => Veredict.OutputLimitExceeded
				case "FO" => Veredict.RestrictedFunctionError
				case "FA" => Veredict.RestrictedFunctionError
				case "SG" => Veredict.RuntimeError
				case _    => Veredict.JudgeError
			}
			
			if(run.veredict < v) run.veredict = v
		}}
		
		if (run.veredict == Veredict.JudgeError) {
			run.runtime = 0
			run.memory = 0
			run.score = 0
		} else {
			run.score = weights
                        .map { case (group, data) => 
                          {
                            val scores = data
                            .map { case (name, weight) =>
                              if (metas.contains(name) && metas(name)._2("status") == "OK") {
                                val f = metas(name)._1

                                gradeCase(
                                  run,
                                  name,
			          new File(f.getCanonicalPath.replace(".meta", ".out")),
				  new File(Config.get("problems.root", "./problems") + "/" + alias + "/cases/" + f.getName.replace(".meta", ".out"))
                                ) * weight
                              } else {
                                0.0
                              }
                            }
                            
                            if (scores.forall(_ > 0)) {
                              scores.foldLeft(0.0)(_+_)
                            } else {
                              0.0
                            }
                          }
                        }
			.foldLeft(0.0)(_+_) / weights.foldLeft(0.0)(_+_._2.foldLeft(0.0)(_+_._2)) * (run.contest match {
				case None => 1.0
				case Some(contest) => {
					if (contest.points_decay_factor <= 0.0 || run.submit_delay == 0.0) {
						1.0
					} else {
						var TT = (contest.finish_time.getTime() - contest.start_time.getTime()) / 60000.
						var PT = run.submit_delay / 60.0

                                                if (contest.points_decay_factor >= 1.0) {
                                                  contest.points_decay_factor = 1.0
                                                }
						
						(1 - contest.points_decay_factor) + contest.points_decay_factor * TT*TT / (10 * PT*PT + TT*TT)
					}
				}
			})

			run.score = scala.math.round(run.score * 1024) / 1024.0
			
			if(run.score == 0 && run.veredict < Veredict.WrongAnswer) run.veredict = Veredict.WrongAnswer
			else if(run.score < (1-1e-9) && run.veredict < Veredict.PartialAccepted) run.veredict = Veredict.PartialAccepted
		}
		
		run.problem.points match {
			case None => {}
			case Some(factor) => run.contest_score = run.score * factor
		}
		
		Manager.updateVeredict(run)
	}
	
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double
}

object CustomGrader extends Grader {
	override def grade(run: Run): Unit = {
		val id = run.id
		val alias = run.problem.alias
		val zip = new File(Config.get("grader.root", ".") + "/" + id + ".zip")
		val dataDirectory = new File(zip.getParentFile.getCanonicalPath + "/" + id)
		dataDirectory.mkdirs()
		
		val input = new ZipInputStream(new FileInputStream(zip.getCanonicalPath))
		var entry: ZipEntry = input.getNextEntry
		val buffer = Array.ofDim[Byte](1024)
		var read: Int = 0
		
		while(entry != null) {
			val outFile = new File(entry.getName())
			val output = new FileOutputStream(dataDirectory.getCanonicalPath + "/" + outFile.getName)

			while( { read = input.read(buffer); read > 0 } ) {
				output.write(buffer, 0, read)
			}

			output.close
			input.closeEntry
			entry = input.getNextEntry
		}
		
		input.close
		
		zip.delete
		
		run.status = Status.Ready
		run.veredict = Veredict.Accepted
		run.runtime = 0
		run.memory = 0
		
                val metas = dataDirectory.listFiles
                  .filter { _.getName.endsWith(".meta") }
                  .map{ f => f.getName.substring(0, f.getName.length - 5)->(f, MetaFile.load(f.getCanonicalPath)) }
                  .toMap
		
		val weightsFile = new File(Config.get("problems.root", "./problems") + "/" + alias + "/testplan")

                trace("Finding Weights file in {}", weightsFile.getCanonicalPath)
		
		val weights:scala.collection.Map[String,scala.collection.Map[String,Double]] = if (weightsFile.exists) {
			val weights = new mutable.ListMap[String,mutable.ListMap[String,Double]]
			val fileReader = new BufferedReader(new FileReader(weightsFile))
			var line: String = null
	
			while( { line = fileReader.readLine(); line != null} ) {
				val tokens = line.split("\\s+")
			
				if(tokens.length == 2 && !tokens(0).startsWith("#")) {
                                        var group:String = null

                                        val idx = tokens(0).indexOf(".")

                                        if (idx != -1) {
                                          group = tokens(0).substring(0, idx)
                                        } else {
                                          group = tokens(0)
                                        }

                                        if (!weights.contains(group)) {
                                                weights += (group -> new mutable.ListMap[String,Double])
                                        }

					try {
						weights(group) += (tokens(0) -> tokens(1).toDouble)
					}
				}
			}
		
			fileReader.close()
		
			weights
		} else {
			new File(Config.get("problems.root", "./problems") + "/" + alias + "/cases/")
			.listFiles
			.filter { _.getName.endsWith(".in") }
			.map {  f:File =>
                                val caseName = f.getName.substring(0, f.getName.length - 3)

                                (caseName -> Map(caseName -> 1.0))
			}
			.toMap
		}

		metas.values.foreach { case (f, meta) => {
			run.runtime += math.round(1000 * meta("time").toDouble)
			run.memory = math.max(run.memory, meta("mem").toLong)
			val v = meta("status") match {
				case "XX" => Veredict.JudgeError
				case "JE" => Veredict.JudgeError
				case "OK" => Veredict.Accepted
				case "RE" => Veredict.RuntimeError
				case "TO" => Veredict.TimeLimitExceeded
				case "ML" => Veredict.MemoryLimitExceeded
				case "OL" => Veredict.OutputLimitExceeded
				case "FO" => Veredict.RestrictedFunctionError
				case "FA" => Veredict.RestrictedFunctionError
				case "SG" => Veredict.RuntimeError
				case _    => Veredict.JudgeError
			}
			
			if(run.veredict < v) run.veredict = v
		}}
		
		if (run.veredict == Veredict.JudgeError) {
			run.runtime = 0
			run.memory = 0
			run.score = 0
		} else {
			run.score = weights
                        .map { case (group, data) => 
                          {
                            val scores = data
                            .map { case (name, weight) =>
                              if (metas.contains(name) && metas(name)._2("status") == "OK") {
                                val f = metas(name)._1

                                if (metas(name)._2.contains("score")) {
                                	metas(name)._2("score").toDouble
                                } else {
                                	0.0
                                } * weight
                              } else {
                                0.0
                              }
                            }
                            
                            if (scores.forall(_ > 0)) {
                              scores.foldLeft(0.0)(_+_)
                            } else {
                              0.0
                            }
                          }
                        }
			.foldLeft(0.0)(_+_) / weights.foldLeft(0.0)(_+_._2.foldLeft(0.0)(_+_._2)) * (run.contest match {
				case None => 1.0
				case Some(contest) => {
					if (contest.points_decay_factor <= 0.0 || run.submit_delay == 0.0) {
						1.0
					} else {
						var TT = (contest.finish_time.getTime() - contest.start_time.getTime()) / 60000.
						var PT = run.submit_delay / 60.0

                                                if (contest.points_decay_factor >= 1.0) {
                                                  contest.points_decay_factor = 1.0
                                                }
						
						(1 - contest.points_decay_factor) + contest.points_decay_factor * TT*TT / (10 * PT*PT + TT*TT)
					}
				}
			})

			run.score = scala.math.round(run.score * 1024) / 1024.0
			
			if(run.score == 0 && run.veredict < Veredict.WrongAnswer) run.veredict = Veredict.WrongAnswer
			else if(run.score < (1-1e-9) && run.veredict < Veredict.PartialAccepted) run.veredict = Veredict.PartialAccepted
		}
		
		run.problem.points match {
			case None => {}
			case Some(factor) => run.contest_score = run.score * factor
		}
		
		Manager.updateVeredict(run)
	}
	
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double = 0
}

object LiteralGrader extends Grader {
	override def grade(run: Run): Unit = {
		debug("Grading {}", run)
		
		run.status = Status.Ready
		run.veredict = Veredict.WrongAnswer
		run.score = try {
			val inA = new BufferedReader(new FileReader(FileUtil.read(Config.get("problems.root", "problems") + "/" + run.problem.alias + "/output").trim))
			val inB = new BufferedReader(new FileReader(FileUtil.read(Config.get("submissions.root", "submissions") + "/" + run.guid)))
			
			var lineA: String = null
			var lineB: String = null
			
			var points:Double = 1
			
			while (inA.ready && inB.ready) {
				lineA = inA.readLine
				lineB = inB.readLine
				
				if (lineA != null && lineB != null) {
					if (! lineA.trim.equals(lineB.trim)) {				
						debug("Mismatched input")
						points = 0
					}
				} else if ( lineA != null || lineB != null) {
					debug("Unfinished input")
					points = 0
				}
			}
			
			if (inA.ready || inB.ready) {
				debug("Unfinished input")
				points = 0
			}
			
			inA.close
			inB.close
			
			points
		} catch {
			case e: Exception => {
				run.veredict = Veredict.JudgeError
				error("", e)
				
				0
			}
		}
		
		if (run.score == 1) run.veredict = Veredict.Accepted
		
		Manager.updateVeredict(run)
	}
	
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double = 0
}

trait Tokenizer {
	def hasNext(): Boolean
	def next(): String
	def close(): Unit
	def path(): String
}

class ScannerTokenizer(file: File) extends Tokenizer {
	val reader = new BufferedReader(new FileReader(file))
	var nextToken: StringBuilder = null
	var eof: Boolean = false
	var pos: Int = 0
	def hasNext(): Boolean = {
		var char: Int = 0
		while (!eof && {char = reader.read; pos += 1; char != -1}) {
			if (!Character.isWhitespace(char)) {
				nextToken = new StringBuilder
				nextToken.append(char.asInstanceOf[Char])
				while ({char = reader.read; pos += 1; char != -1 && !Character.isWhitespace(char)}) {
					nextToken.append(char.asInstanceOf[Char])
				}
				if (char == -1) {
					eof = true;
				}
				return true;
			}
		}
		eof = true
		return false;
	}
	def next(): String = nextToken.toString
	def close(): Unit = reader.close
	def path(): String = file.getCanonicalPath
}

class NumericTokenizer(file: File) extends Tokenizer {
	val reader = new BufferedReader(new FileReader(file))
	var nextToken: StringBuilder = null
	var eof: Boolean = false
	def hasNext(): Boolean = {
		var char: Int = 0
		while (!eof && {char = reader.read; char != -1}) {
			if (Character.isDigit(char) || char == '.') {
				nextToken = new StringBuilder
				nextToken.append(char.asInstanceOf[Char])
				while (nextToken.length < 1000 && {char = reader.read; Character.isDigit(char) || char == '.'}) {
					nextToken.append(char.asInstanceOf[Char])
				}
				if (char == -1) {
					eof = true;
				}
				return true;
			}
		}
		eof = true
		return false;
	}
	def next(): String = nextToken.toString
	def close(): Unit = reader.close
	def path(): String = file.getCanonicalPath
}

trait TokenComparer extends Object with Log {
	def gradeCase(run: Run, caseName: String, inA: Tokenizer, inB: Tokenizer, eq: (String,String) => Boolean): Double = {
		debug("Grading {}, case {}", run, caseName)

		try {
			var points:Double = 1

			while (points > 0 && inA.hasNext && inB.hasNext) {
				if (!eq(inA.next, inB.next)) {
					debug("Token mismatch {} {} {}", caseName, inA.path, inB.path)
					points = 0
				}
			}
			
			if (inA.hasNext || inB.hasNext) {
				debug("Unfinished input {} {} {}", caseName, inA.path, inB.path)
				points = 0
			}
			
			debug("Grading {}, case {}. Reporting {} points", run, caseName, points)
			points
		} catch {
			case e => {
				info("Error grading: {}", e)
				error("Stack trace {}", e.getStackTrace)
				error("Error grading: {}", e)
				
				0
			}
		} finally {
			debug("Finished grading {}, case {}", run, caseName)
			inA.close
			inB.close
		}
	}
}

object TokenGrader extends Grader with TokenComparer {
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double = {
		gradeCase(run, caseName, new ScannerTokenizer(runOut), new ScannerTokenizer(problemOut), _.equals(_))
	}
}

object TokenCaselessGrader extends Grader with TokenComparer {
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double = {
		gradeCase(run, caseName, new ScannerTokenizer(runOut), new ScannerTokenizer(problemOut), _.equalsIgnoreCase(_))
	}
}

object TokenNumericGrader extends Grader with TokenComparer {
	def gradeCase(run: Run, caseName: String, runOut: File, problemOut: File): Double = {
		gradeCase(run, caseName, new NumericTokenizer(runOut), new NumericTokenizer(problemOut), (a:String, b:String) => math.abs(a.toDouble - b.toDouble) <= 1e-6)
	}
}
