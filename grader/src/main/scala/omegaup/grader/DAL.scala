package omegaup.grader

import java.sql._
import omegaup.Database._

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
	val  WrongAnswer = Value(2, "WA")
	val  PresentationError = Value(3, "PE")
	val  RuntimeError = Value(4, "RTE")
	val  MemoryLimitExceeded = Value(5, "MLE")
	val  OutputLimitExceeded = Value(6, "OLE")
	val  TimeLimitExceeded = Value(7, "TLE")
	val  RestrictedFunctionError = Value(8, "RFE")
	val  CompileError = Value(9, "CE")
	val  JudgeError = Value(10, "JE")
}

import Validador._
import Veredicto._
import Estado._
import Servidor._
import Lenguaje._

class Problema(
	val id: Long,
	val publico: Long = 1,
	val autor: Long = 0,
	val titulo: String = "",
	val alias: Option[String] = None,
	val validador: Validador = Validador.TokenNumeric,
	val servidor: Option[Servidor] = None,
	val id_remoto: Option[String] = None,
	val tiempo_limite: Option[Long] = Some(1),
	val memoria_limite: Option[Long] = Some(64),
	val vistas: Long = 0,
	val envios: Long = 0,
	val aceptados: Long = 0,
	val dificultad: Double = 0) {
}

class Ejecucion(
	val id: Long = 0,
	val usuario: Long = 0,
	val problema: Problema = null,
	val concurso: Option[Long] = None,
	val guid: String = "",
	val lenguaje: Lenguaje = Lenguaje.C,
	val estado: Estado = Estado.Nuevo,
	val veredicto: Veredicto = Veredicto.JudgeError,
	val tiempo: Long = 0,
	val memoria: Long = 0,
	val puntuacion: Double = 0,
	val puntuacion_concurso: Double = 0,
	val ip: String = "127.0.0.1",
	val fecha: Timestamp = new Timestamp(0)
) {
}

object GraderData {
	def ejecucion(id: Long)(implicit connection: Connection): Option[Ejecucion] =
		query("SELECT * FROM Ejecuciones AS e, Problemas AS p WHERE p.problemaID = e.problemaID AND e.ejecucionID = " + id) { rs =>
			new Ejecucion(
				id = rs.getLong("ejecucionID"),
				concurso = rs.getLong("concursoID") match {
					case 0 => if(rs.wasNull) None else Some(0)
					case x => Some(x)
				},
				guid = rs.getString("guid"),
				lenguaje = Lenguaje.withName(rs.getString("lenguaje")),
				estado = Estado.withName(rs.getString("estado")),
				veredicto = Veredicto.withName(rs.getString("veredicto")),
				problema = new Problema(
					 id = rs.getLong("p.problemaID"),
					 validador = Validador.withName(rs.getString("validador")),
					 servidor = rs.getString("servidor") match {
					 	case null => None
					 	case x: String => Some(Servidor.withName(x))
					 },
					 id_remoto = rs.getString("id_remoto") match {
					 	case null => None
					 	case x: String => Some(x)
					 },
					 tiempo_limite = rs.getString("tiempo") match {
					 	case null => None
					 	case x: String => Some(x.toLong)
					 },
					 memoria_limite = rs.getString("memoria") match {
					 	case null => None
					 	case x: String => Some(x.toLong)
					 }
				)
			)
		}
		
	def update(ejecucion: Ejecucion)(implicit connection: Connection): Ejecucion = {
		execute(
			"UPDATE Ejecuciones SET estado = '" + ejecucion.estado + "'" +
			", veredicto = '" + ejecucion.veredicto + "'" +
			", tiempo = " + ejecucion.tiempo +
			", memoria = " + ejecucion.memoria +
			", puntuacion = " + ejecucion.puntuacion +
			", puntuacion_concurso = " + ejecucion.puntuacion_concurso + " " +
			"WHERE ejecucionID = " + ejecucion.id
		)
		ejecucion
	}
		
	def insert(ej: Ejecucion)(implicit connection: Connection): Ejecucion = {
		execute(
			"INSERT INTO Ejecuciones (usuarioID, problemaID, guid, lenguaje, veredicto, ip) VALUES(" +
				ej.usuario + ", " +
				ej.problema.id + ", " +
				"'" + ej.guid + "', " + 
				"'" + ej.lenguaje + "', " + 
				"'" + ej.veredicto + "', " + 
				"'" + ej.ip + "'" + 
			")"
		)
		ejecucion(query("SELECT LAST_INSERT_ID()") { rs => rs.getInt(1) }.get).get
	}
}
