<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentityRequestHistory Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsetIdentityRequestHistory}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestHistoryDAOBase {
    /**
     * Actualizar registros.
     *
     * @param ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History El objeto de tipo ProblemsetIdentityRequestHistory a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) : int {
        $sql = 'UPDATE `Problemset_Identity_Request_History` SET `identity_id` = ?, `problemset_id` = ?, `time` = ?, `accepted` = ?, `admin_id` = ? WHERE `history_id` = ?;';
        $params = [
            (int)$Problemset_Identity_Request_History->identity_id,
            (int)$Problemset_Identity_Request_History->problemset_id,
            DAO::toMySQLTimestamp($Problemset_Identity_Request_History->time),
            (int)$Problemset_Identity_Request_History->accepted,
            (int)$Problemset_Identity_Request_History->admin_id,
            (int)$Problemset_Identity_Request_History->history_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetIdentityRequestHistory} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetIdentityRequestHistory} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?ProblemsetIdentityRequestHistory Un objeto del tipo {@link ProblemsetIdentityRequestHistory}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $history_id) : ?ProblemsetIdentityRequestHistory {
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` FROM Problemset_Identity_Request_History WHERE (history_id = ?) LIMIT 1;';
        $params = [$history_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsetIdentityRequestHistory($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetIdentityRequestHistory suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History El objeto de tipo ProblemsetIdentityRequestHistory a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) : void {
        $sql = 'DELETE FROM `Problemset_Identity_Request_History` WHERE history_id = ?;';
        $params = [$Problemset_Identity_Request_History->history_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsetIdentityRequestHistory}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequestHistory}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` from Problemset_Identity_Request_History';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ProblemsetIdentityRequestHistory($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequestHistory suministrado.
     *
     * @param ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History El objeto de tipo ProblemsetIdentityRequestHistory a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) : int {
        if (is_null($Problemset_Identity_Request_History->time)) {
            $Problemset_Identity_Request_History->time = Time::get();
        }
        $sql = 'INSERT INTO Problemset_Identity_Request_History (`identity_id`, `problemset_id`, `time`, `accepted`, `admin_id`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemset_Identity_Request_History->identity_id,
            (int)$Problemset_Identity_Request_History->problemset_id,
            DAO::toMySQLTimestamp($Problemset_Identity_Request_History->time),
            (int)$Problemset_Identity_Request_History->accepted,
            (int)$Problemset_Identity_Request_History->admin_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problemset_Identity_Request_History->history_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
