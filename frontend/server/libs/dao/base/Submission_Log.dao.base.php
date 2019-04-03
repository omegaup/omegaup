<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** SubmissionLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link SubmissionLog}.
 * @access public
 * @abstract
 *
 */
abstract class SubmissionLogDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link SubmissionLog}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(SubmissionLog $Submission_Log) {
        if (is_null(self::getByPK($Submission_Log->run_id))) {
            return SubmissionLogDAOBase::create($Submission_Log);
        }
        return SubmissionLogDAOBase::update($Submission_Log);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a actualizar.
     */
    final public static function update(SubmissionLog $Submission_Log) {
        $sql = 'UPDATE `Submission_Log` SET `problemset_id` = ?, `user_id` = ?, `identity_id` = ?, `ip` = ?, `time` = ? WHERE `run_id` = ?;';
        $params = [
            $Submission_Log->problemset_id,
            $Submission_Log->user_id,
            $Submission_Log->identity_id,
            $Submission_Log->ip,
            $Submission_Log->time,
            $Submission_Log->run_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link SubmissionLog} por llave primaria.
     *
     * Este metodo cargará un objeto {@link SubmissionLog} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link SubmissionLog Un objeto del tipo {@link SubmissionLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($run_id) {
        if (is_null($run_id)) {
            return null;
        }
        $sql = 'SELECT `Submission_Log`.`problemset_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`identity_id`, `Submission_Log`.`ip`, `Submission_Log`.`time` FROM Submission_Log WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new SubmissionLog($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto SubmissionLog suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a eliminar
     */
    final public static function delete(SubmissionLog $Submission_Log) {
        $sql = 'DELETE FROM `Submission_Log` WHERE run_id = ?;';
        $params = [$Submission_Log->run_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link SubmissionLog}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link SubmissionLog}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Submission_Log`.`problemset_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`identity_id`, `Submission_Log`.`ip`, `Submission_Log`.`time` from Submission_Log';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new SubmissionLog($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto SubmissionLog suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a crear.
     */
    final public static function create(SubmissionLog $Submission_Log) {
        if (is_null($Submission_Log->time)) {
            $Submission_Log->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Submission_Log (`problemset_id`, `run_id`, `user_id`, `identity_id`, `ip`, `time`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Submission_Log->problemset_id,
            $Submission_Log->run_id,
            $Submission_Log->user_id,
            $Submission_Log->identity_id,
            $Submission_Log->ip,
            $Submission_Log->time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
