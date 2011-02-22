package omegaup.grader

import java.sql._
import omegaup.data._
import omegaup.Database._

import Veredicto._
import Validador._
import Servidor._
import Lenguaje._

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
		ej.id = query("SELECT LAST_INSERT_ID()") { rs => rs.getInt(1) }.get
		ej
	}
}
