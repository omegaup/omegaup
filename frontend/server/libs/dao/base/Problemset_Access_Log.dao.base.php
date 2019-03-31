<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetAccessLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetAccessLog }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetAccessLogDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetAccessLog} pasado en la base de datos.
     * save() siempre creara una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetAccessLog [$Problemset_Access_Log] El objeto de tipo ProblemsetAccessLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetAccessLog $Problemset_Access_Log) {
        return ProblemsetAccessLogDAOBase::create($Problemset_Access_Log);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetAccessLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetAccessLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Access_Log`.`problemset_id`, `Problemset_Access_Log`.`identity_id`, `Problemset_Access_Log`.`ip`, `Problemset_Access_Log`.`time` from Problemset_Access_Log';
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
            $allData[] = new ProblemsetAccessLog($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetAccessLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetAccessLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetAccessLog [$Problemset_Access_Log] El objeto de tipo ProblemsetAccessLog a crear.
     */
    final private static function create(ProblemsetAccessLog $Problemset_Access_Log) {
        if (is_null($Problemset_Access_Log->time)) {
            $Problemset_Access_Log->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Access_Log (`problemset_id`, `identity_id`, `ip`, `time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Problemset_Access_Log->problemset_id,
            $Problemset_Access_Log->identity_id,
            $Problemset_Access_Log->ip,
            $Problemset_Access_Log->time,
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
