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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequestHistory}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (is_null(self::getByPK($Problemset_Identity_Request_History->history_id))) {
            return ProblemsetIdentityRequestHistoryDAOBase::create($Problemset_Identity_Request_History);
        }
        return ProblemsetIdentityRequestHistoryDAOBase::update($Problemset_Identity_Request_History);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a actualizar.
     */
    final public static function update(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        $sql = 'UPDATE `Problemset_Identity_Request_History` SET `identity_id` = ?, `problemset_id` = ?, `time` = ?, `accepted` = ?, `admin_id` = ? WHERE `history_id` = ?;';
        $params = [
            is_null($Problemset_Identity_Request_History->identity_id) ? null : (int)$Problemset_Identity_Request_History->identity_id,
            is_null($Problemset_Identity_Request_History->problemset_id) ? null : (int)$Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            is_null($Problemset_Identity_Request_History->accepted) ? null : (int)$Problemset_Identity_Request_History->accepted,
            is_null($Problemset_Identity_Request_History->admin_id) ? null : (int)$Problemset_Identity_Request_History->admin_id,
            is_null($Problemset_Identity_Request_History->history_id) ? null : (int)$Problemset_Identity_Request_History->history_id,
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
     * @static
     * @return @link ProblemsetIdentityRequestHistory Un objeto del tipo {@link ProblemsetIdentityRequestHistory}. NULL si no hay tal registro.
     */
    final public static function getByPK($history_id) {
        if (is_null($history_id)) {
            return null;
        }
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a eliminar
     */
    final public static function delete(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequestHistory}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a crear.
     */
    final public static function create(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (is_null($Problemset_Identity_Request_History->time)) {
            $Problemset_Identity_Request_History->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Identity_Request_History (`identity_id`, `problemset_id`, `time`, `accepted`, `admin_id`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Problemset_Identity_Request_History->identity_id) ? null : (int)$Problemset_Identity_Request_History->identity_id,
            is_null($Problemset_Identity_Request_History->problemset_id) ? null : (int)$Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            is_null($Problemset_Identity_Request_History->accepted) ? null : (int)$Problemset_Identity_Request_History->accepted,
            is_null($Problemset_Identity_Request_History->admin_id) ? null : (int)$Problemset_Identity_Request_History->admin_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problemset_Identity_Request_History->history_id = $conn->Insert_ID();

        return $ar;
    }
}
