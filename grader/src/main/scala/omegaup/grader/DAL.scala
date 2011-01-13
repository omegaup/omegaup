package omegaup.grader

import java.sql._
import org.squeryl._
import org.squeryl.dsl._
import org.squeryl.annotations._
import org.squeryl.PrimitiveTypeMode._

object Validador extends Enumeration {
	type Validador = Value
	val  Remoto = Value(1, "remoto")
	val  Literal = Value(2, "literal")
	val  Token = Value(3, "token")
	val  TokenCaseless = Value(4, "token-caseless")
	val  TokenNumeric = Value(5, "token-numeric")
}

object Servidor extends Enumeration {
	type Servidor = Value
	val  UVa = Value(1, "uva")
	val  LiveArchive = Value(2, "livearchive")
	val  PKU = Value(3, "pku")
	val  TJU = Value(4, "tju")
	val  SPOJ = Value(5, "spoj")
}

object Lenguaje extends Enumeration {
	type Lenguaje = Value
	val  C = Value(1, "c")
	val  Cpp = Value(2, "cpp")
	val  Java = Value(3, "java")
	val  Python = Value(4, "py")
	val  Ruby = Value(5, "rb")
	val  Perl = Value(6, "pl")
	val  CSharp = Value(6, "cs")
}

object Estado extends Enumeration {
	type Estado = Value
	val  Nuevo = Value(1, "nuevo")
	val  Espera = Value(2, "espera")
	val  Compilando = Value(3, "compilando")
	val  Ejecutando = Value(4, "ejecutando")
	val  Listo = Value(5, "listo")
}

object Veredicto extends Enumeration {
	type Veredicto = Value
	val  Accepted = Value(1, "AC")
	val  WrongAnswer = Value(2, "WA")
	val  PresentationError = Value(3, "PE")
	val  RuntimeError = Value(4, "RTE")
	val  MemoryLimitExceeded = Value(5, "MLE")
	val  TimeLimitExceeded = Value(5, "TLE")
	val  RestrictedFunctionError = Value(5, "RFE")
	val  JudgeError = Value(5, "JE")
}

import Validador._
import Veredicto._
import Estado._
import Servidor._
import Lenguaje._

object GraderData extends Schema {
	val problemas = table[Problema]("Problemas")
	val ejecuciones = table[Ejecucion]("Ejecuciones")
	
	val problemasEjecuciones =
		oneToManyRelation(problemas, ejecuciones).
		via((p,e) => p.id === e.problemaID)
}

class Problema(
	@Column("problemaID")
	val id: Int,
	val publico: Int,
	val autor: Int,
	val titulo: String,
	val alias: Option[String],
	val validador: Validador,
	val servidor: Option[Servidor],
	val id_remoto: Option[String],
	val tiempo_limite: Option[Int],
	val memoria_limite: Option[Int],
	val vistas: Int,
	val envios: Int,
	val aceptados: Int,
	val dificultad: Double) extends KeyedEntity[Int] {
	
	def this() = this(0, 0, 0, "", None, Validador.TokenNumeric, None, None, Some(3000), Some(64), 0, 0, 0, 0);
	
	lazy val ejecuciones: OneToMany[Ejecucion] = GraderData.problemasEjecuciones.left(this)
}

class Ejecucion(
	@Column("ejecucionID")
	val id: Int,
	@Column("usuarioID")
	val usuario: Int,
	val problemaID: Int,
	@Column("concursoID")
	val concurso: Option[Int],
	val guid: String,
	val lenguaje: Lenguaje,
	val estado: Estado,
	val veredicto: Veredicto,
	val tiempo: Int,
	val memoria: Int,
	val puntuacion: Double,
	val ip: String,
	val fecha: Timestamp) extends KeyedEntity[Int] {
	
	lazy val problema: ManyToOne[Problema] = GraderData.problemasEjecuciones.right(this)
}
