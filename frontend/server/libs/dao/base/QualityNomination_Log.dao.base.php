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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominationLog }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationLogDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominationLog} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominationLog [$QualityNomination_Log] El objeto de tipo QualityNominationLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominationLog $QualityNomination_Log) {
        if (!is_null(self::getByPK($QualityNomination_Log->qualitynomination_log_id))) {
            return QualityNominationLogDAOBase::update($QualityNomination_Log);
        } else {
            return QualityNominationLogDAOBase::create($QualityNomination_Log);
        }
    }

    /**
     * Obtener {@link QualityNominationLog} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominationLog} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationLog Un objeto del tipo {@link QualityNominationLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_log_id) {
        if (is_null($qualitynomination_log_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNomination_Log`.`qualitynomination_log_id`, `QualityNomination_Log`.`qualitynomination_id`, `QualityNomination_Log`.`time`, `QualityNomination_Log`.`user_id`, `QualityNomination_Log`.`from_status`, `QualityNomination_Log`.`to_status`, `QualityNomination_Log`.`rationale` FROM QualityNomination_Log WHERE (qualitynomination_log_id = ?) LIMIT 1;';
        $params = [$qualitynomination_log_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominationLog($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominationLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNomination_Log`.`qualitynomination_log_id`, `QualityNomination_Log`.`qualitynomination_id`, `QualityNomination_Log`.`time`, `QualityNomination_Log`.`user_id`, `QualityNomination_Log`.`from_status`, `QualityNomination_Log`.`to_status`, `QualityNomination_Log`.`rationale` from QualityNomination_Log';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new QualityNominationLog($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominationLog [$QualityNomination_Log] El objeto de tipo QualityNominationLog a actualizar.
      */
    final private static function update(QualityNominationLog $QualityNomination_Log) {
        $sql = 'UPDATE `QualityNomination_Log` SET `qualitynomination_id` = ?, `time` = ?, `user_id` = ?, `from_status` = ?, `to_status` = ?, `rationale` = ? WHERE `qualitynomination_log_id` = ?;';
        $params = [
            $QualityNomination_Log->qualitynomination_id,
            $QualityNomination_Log->time,
            $QualityNomination_Log->user_id,
            $QualityNomination_Log->from_status,
            $QualityNomination_Log->to_status,
            $QualityNomination_Log->rationale,
            $QualityNomination_Log->qualitynomination_log_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominationLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominationLog [$QualityNomination_Log] El objeto de tipo QualityNominationLog a crear.
     */
    final private static function create(QualityNominationLog $QualityNomination_Log) {
        if (is_null($QualityNomination_Log->time)) {
            $QualityNomination_Log->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($QualityNomination_Log->from_status)) {
            $QualityNomination_Log->from_status = 'open';
        }
        if (is_null($QualityNomination_Log->to_status)) {
            $QualityNomination_Log->to_status = 'open';
        }
        $sql = 'INSERT INTO QualityNomination_Log (`qualitynomination_log_id`, `qualitynomination_id`, `time`, `user_id`, `from_status`, `to_status`, `rationale`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $QualityNomination_Log->qualitynomination_log_id,
            $QualityNomination_Log->qualitynomination_id,
            $QualityNomination_Log->time,
            $QualityNomination_Log->user_id,
            $QualityNomination_Log->from_status,
            $QualityNomination_Log->to_status,
            $QualityNomination_Log->rationale,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $QualityNomination_Log->qualitynomination_log_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominationLog suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param QualityNominationLog [$QualityNomination_Log] El objeto de tipo QualityNominationLog a eliminar
     */
    final public static function delete(QualityNominationLog $QualityNomination_Log) {
        $sql = 'DELETE FROM `QualityNomination_Log` WHERE qualitynomination_log_id = ?;';
        $params = [$QualityNomination_Log->qualitynomination_log_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
