package omegaup.data

import java.sql._

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
	val  CSharp = Value(7, "cs")
	val  Pascal = Value(8, "p")
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
	val  PartialAccepted = Value(2, "PA")
	val  PresentationError = Value(3, "PE")
	val  WrongAnswer = Value(4, "WA")
	val  TimeLimitExceeded = Value(5, "TLE")
	val  OutputLimitExceeded = Value(6, "OLE")
	val  MemoryLimitExceeded = Value(7, "MLE")
	val  RuntimeError = Value(8, "RTE")
	val  RestrictedFunctionError = Value(9, "RFE")
	val  CompileError = Value(10, "CE")
	val  JudgeError = Value(11, "JE")
}

import Validador._
import Veredicto._
import Estado._
import Servidor._
import Lenguaje._

class Problema(
	var id: Long,
	var publico: Long = 1,
	var autor: Long = 0,
	var titulo: String = "",
	var alias: Option[String] = None,
	var validador: Validador = Validador.TokenNumeric,
	var servidor: Option[Servidor] = None,
	var id_remoto: Option[String] = None,
	var tiempo_limite: Option[Long] = Some(1),
	var memoria_limite: Option[Long] = Some(64),
	var vistas: Long = 0,
	var envios: Long = 0,
	var aceptados: Long = 0,
	var dificultad: Double = 0) {
}

class Ejecucion(
	var id: Long = 0,
	var usuario: Long = 0,
	var problema: Problema = null,
	var concurso: Option[Long] = None,
	var guid: String = "",
	var lenguaje: Lenguaje = Lenguaje.C,
	var estado: Estado = Estado.Nuevo,
	var veredicto: Veredicto = Veredicto.JudgeError,
	var tiempo: Long = 0,
	var memoria: Long = 0,
	var puntuacion: Double = 0,
	var puntuacion_concurso: Double = 0,
	var ip: String = "127.0.0.1",
	var fecha: Timestamp = new Timestamp(0)
) {
}
