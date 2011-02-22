package omegaup.grader

import java.io._
import java.util._
import java.util.regex.Pattern
import java.util.zip._
import scala.collection.mutable
import omegaup._
import omegaup.data._
import Veredicto._

trait Grader extends Object with Log {
	def grade(ejecucion: Ejecucion): Unit = {
		val id = ejecucion.id
		val pid = ejecucion.problema.id
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
		
		ejecucion.estado = Estado.Listo
		ejecucion.veredicto = Veredicto.Accepted
		ejecucion.tiempo = 0
		ejecucion.memoria = 0
		
		val metas = dataDirectory.listFiles.filter { _.getName.endsWith(".meta") }.map{ f => (f, MetaFile.load(f.getCanonicalPath)) }
		
		val weightsFile = new File(dataDirectory.getCanonicalPath + "/testplan")
		
		val weights:scala.collection.Map[String,Double] = if (weightsFile.exists) {
			val weights = new mutable.ListMap[String,Double]
			val fileReader = new BufferedReader(new FileReader(weightsFile))
			var line: String = null
	
			while( { line = fileReader.readLine(); line != null} ) {
				val tokens = line.split("\\s+")
			
				if(tokens.length == 2 && !tokens(0).startsWith("#")) {
					try {
						weights += (tokens(0) -> tokens(1).toDouble)
					}
				}
			}
		
			fileReader.close()
		
			weights
		} else {
			new File(Config.get("problems.root", "./problems") + "/" + pid + "/cases/")
			.listFiles
			.filter { _.getName.endsWith(".in") }
			.map {  f:File =>
				(f.getName.substring(0, f.getName.length - 3) -> 1.0)
			}
			.toMap
		}
		
		metas.foreach { case (f, meta) => {
			ejecucion.tiempo += math.round(1000 * meta("time").toDouble)
			ejecucion.memoria = math.max(ejecucion.memoria, meta("mem").toLong)
			val v = meta("status") match {
				case "XX" => Veredicto.JudgeError
				case "OK" => Veredicto.Accepted
				case "RE" => Veredicto.RuntimeError
				case "TO" => Veredicto.TimeLimitExceeded
				case "ML" => Veredicto.MemoryLimitExceeded
				case "OL" => Veredicto.OutputLimitExceeded
				case "FO" => Veredicto.RestrictedFunctionError
				case "FA" => Veredicto.RestrictedFunctionError
				case "SG" => Veredicto.RuntimeError
			}
			
			if(ejecucion.veredicto < v) ejecucion.veredicto = v
		}}
		
		if (ejecucion.veredicto == Veredicto.JudgeError) {
			ejecucion.tiempo = 0
			ejecucion.memoria = 0
			ejecucion.puntuacion = 0
		} else {
			ejecucion.puntuacion = metas
			.filter{ case (f,m) => m("status") == "OK" }
			.map { case (f,m) =>
				weights(f.getName.substring(0, f.getName.length - 5)) *
				gradeCase(
					ejecucion,
					f.getName.substring(0, f.getName.length - 5),
					new File(f.getCanonicalPath.replace(".meta", ".out")),
					new File(Config.get("problems.root", "./problems") + "/" + pid + "/cases/" + f.getName.replace(".meta", ".out"))
				)
			}
			.foldLeft(0.0)(_+_) / weights.foldLeft(0.0)(_+_._2)
			
			if(ejecucion.puntuacion == 0 && ejecucion.veredicto < Veredicto.WrongAnswer) ejecucion.veredicto = Veredicto.WrongAnswer
			else if(ejecucion.puntuacion < metas.length && ejecucion.veredicto < Veredicto.PartialAccepted) ejecucion.veredicto = Veredicto.PartialAccepted
		}
		
		Manager.updateVeredict(ejecucion)
	}
	
	def gradeCase(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File): Double
}

object LiteralGrader extends Grader {
	override def grade(ejecucion: Ejecucion): Unit = {
		debug("Grading {}", ejecucion)
		
		ejecucion.estado = Estado.Listo
		ejecucion.veredicto = Veredicto.WrongAnswer
		ejecucion.puntuacion = try {
			val inA = new BufferedReader(new FileReader(FileUtil.read(Config.get("problems.root", ".") + "/" + ejecucion.problema.id + "/output").trim))
			val inB = new BufferedReader(new FileReader(FileUtil.read(Config.get("submissions.root", "submissions") + "/" + ejecucion.guid)))
			
			var lineA: String = null
			var lineB: String = null
			
			var points:Double = 1
			
			while( inA.ready && inB.ready ) {
				lineA = inA.readLine
				lineB = inB.readLine
				
				if( lineA != null && lineB != null ) {
					if( ! lineA.trim.equals(lineB.trim) ) {				
						debug("Mismatched input")
						points = 0
					}
				} else if( lineA != null || lineB != null ) {
					debug("Unfinished input")
					points = 0
				}
			}
			
			if( inA.ready || inB.ready ) {
				debug("Unfinished input")
				points = 0
			}
			
			inA.close
			inB.close
			
			points
		} catch {
			case e: Exception => {
				ejecucion.veredicto = Veredicto.JudgeError
				error("", e)
				
				0
			}
		}
		
		if(ejecucion.puntuacion == 1) ejecucion.veredicto = Veredicto.Accepted
		
		Manager.updateVeredict(ejecucion)
	}
	
	def gradeCase(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File): Double = 0
}

trait TokenComparer extends Object with Log {
	def grade(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File, hasNext: Scanner => Boolean, next: Scanner => String, eq: (String,String) => Boolean): Double = {
		debug("Grading {}, case {}", ejecucion, caseName)
		try {
			val inA = new Scanner(ejecucionOut)
			val inB = new Scanner(problemOut)
			
			var points:Double = 1
			
			while( hasNext(inA) && hasNext(inB) ) {
				if(! eq(next(inA), next(inB)) ) {
					debug("Token mismatch {} {} {}", caseName, ejecucionOut.getCanonicalPath, problemOut.getCanonicalPath)
					points = 0
				}
			}
			
			if( hasNext(inA) || hasNext(inB) ) {
				debug("Unfinished input {} {} {}", caseName, ejecucionOut.getCanonicalPath, problemOut.getCanonicalPath)
				points = 0
			}
			
			inA.close
			inB.close
			
			debug("Grading {}, case {}. Reporting {} points", ejecucion, caseName, points)
			points
		} catch {
			case e: Exception => {
				error(e.getMessage)
				e.getStackTrace.foreach { st =>
					error(st.toString)
				}
				
				0
			}
		}
	}
}

object TokenGrader extends Grader with TokenComparer {
	def gradeCase(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File): Double = {
		grade(ejecucion, caseName, ejecucionOut, problemOut, _.hasNext, _.next, _.equals(_))
	}
}

object TokenCaselessGrader extends Grader with TokenComparer {
	def gradeCase(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File): Double = {
		grade(ejecucion, caseName, ejecucionOut, problemOut, _.hasNext, _.next, _.equalsIgnoreCase(_))
	}
}

object TokenNumericGrader extends Grader with TokenComparer {
	def gradeCase(ejecucion: Ejecucion, caseName: String, ejecucionOut: File, problemOut: File): Double = {
		val pattern = Pattern.compile("\\d+(?:\\.\\d*)?|\\.\\d+")
		
		grade(ejecucion, caseName, ejecucionOut, problemOut, _.hasNext(pattern), _.next(pattern), (a:String,b:String) => math.abs(a.toDouble - b.toDouble) <= 1e-6)
	}
}
