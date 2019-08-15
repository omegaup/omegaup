<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominationLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link QualityNominationLog}.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationLogDAOBase {
    /**
     * Actualizar registros.
     *
     * @param QualityNominationLog $QualityNomination_Log El objeto de tipo QualityNominationLog a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(QualityNominationLog $QualityNomination_Log) : int {
        $sql = 'UPDATE `QualityNomination_Log` SET `qualitynomination_id` = ?, `time` = ?, `user_id` = ?, `from_status` = ?, `to_status` = ?, `rationale` = ? WHERE `qualitynomination_log_id` = ?;';
        $params = [
            is_null($QualityNomination_Log->qualitynomination_id) ? null : (int)$QualityNomination_Log->qualitynomination_id,
            DAO::toMySQLTimestamp($QualityNomination_Log->time),
            is_null($QualityNomination_Log->user_id) ? null : (int)$QualityNomination_Log->user_id,
            $QualityNomination_Log->from_status,
            $QualityNomination_Log->to_status,
            $QualityNomination_Log->rationale,
            (int)$QualityNomination_Log->qualitynomination_log_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link QualityNominationLog} por llave primaria.
     *
     * Este metodo cargará un objeto {@link QualityNominationLog} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?QualityNominationLog Un objeto del tipo {@link QualityNominationLog}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $qualitynomination_log_id) : ?QualityNominationLog {
        $sql = 'SELECT `QualityNomination_Log`.`qualitynomination_log_id`, `QualityNomination_Log`.`qualitynomination_id`, `QualityNomination_Log`.`time`, `QualityNomination_Log`.`user_id`, `QualityNomination_Log`.`from_status`, `QualityNomination_Log`.`to_status`, `QualityNomination_Log`.`rationale` FROM QualityNomination_Log WHERE (qualitynomination_log_id = ?) LIMIT 1;';
        $params = [$qualitynomination_log_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new QualityNominationLog($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto QualityNominationLog suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param QualityNominationLog $QualityNomination_Log El objeto de tipo QualityNominationLog a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(QualityNominationLog $QualityNomination_Log) : void {
        $sql = 'DELETE FROM `QualityNomination_Log` WHERE qualitynomination_log_id = ?;';
        $params = [$QualityNomination_Log->qualitynomination_log_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link QualityNominationLog}.
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
     * @return QualityNominationLog[] Un arreglo que contiene objetos del tipo {@link QualityNominationLog}.
     *
     * @psalm-return array<int, QualityNominationLog>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `QualityNomination_Log`.`qualitynomination_log_id`, `QualityNomination_Log`.`qualitynomination_id`, `QualityNomination_Log`.`time`, `QualityNomination_Log`.`user_id`, `QualityNomination_Log`.`from_status`, `QualityNomination_Log`.`to_status`, `QualityNomination_Log`.`rationale` from QualityNomination_Log';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new QualityNominationLog($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationLog suministrado.
     *
     * @param QualityNominationLog $QualityNomination_Log El objeto de tipo QualityNominationLog a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(QualityNominationLog $QualityNomination_Log) : int {
        $sql = 'INSERT INTO QualityNomination_Log (`qualitynomination_id`, `time`, `user_id`, `from_status`, `to_status`, `rationale`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($QualityNomination_Log->qualitynomination_id) ? null : (int)$QualityNomination_Log->qualitynomination_id,
            DAO::toMySQLTimestamp($QualityNomination_Log->time),
            is_null($QualityNomination_Log->user_id) ? null : (int)$QualityNomination_Log->user_id,
            $QualityNomination_Log->from_status,
            $QualityNomination_Log->to_status,
            $QualityNomination_Log->rationale,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $QualityNomination_Log->qualitynomination_log_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
