<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestAccessLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ContestAccessLog }.
 * @access public
 * @abstract
 *
 */
abstract class ContestAccessLogDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contest_Access_Log`.`contest_id`, `Contest_Access_Log`.`user_id`, `Contest_Access_Log`.`ip`, `Contest_Access_Log`.`time`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestAccessLog} pasado en la base de datos.
     * save() siempre creara una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ContestAccessLog $Contest_Access_Log) {
        return ContestAccessLogDAOBase::create($Contest_Access_Log);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ContestAccessLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestAccessLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contest_Access_Log`.`contest_id`, `Contest_Access_Log`.`user_id`, `Contest_Access_Log`.`ip`, `Contest_Access_Log`.`time` from Contest_Access_Log';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysql_real_escape_string($orden) . '` ' . mysql_real_escape_string($tipo_de_orden);
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        global $conn;
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new ContestAccessLog($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestAccessLog} de la base de datos.
      * Consiste en buscar todos los objetos que coinciden con las variables permanentes instanciadas de objeto pasado como argumento.
      * Aquellas variables que tienen valores NULL seran excluidos en busca de criterios.
      *
      * <code>
      *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito igual a 20000
      *   $cliente = new Cliente();
      *   $cliente->setLimiteCredito('20000');
      *   $resultados = ClienteDAO::search($cliente);
      *
      *   foreach ($resultados as $c){
      *       echo $c->nombre . '<br>';
      *   }
      * </code>
      * @static
      * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contest_Access_Log, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contest_Access_Log instanceof ContestAccessLog)) {
            return self::search(new ContestAccessLog($Contest_Access_Log));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contest_Access_Log->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contest_Access_Log->contest_id;
        }
        if (!is_null($Contest_Access_Log->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Contest_Access_Log->user_id;
        }
        if (!is_null($Contest_Access_Log->ip)) {
            $clauses[] = '`ip` = ?';
            $params[] = $Contest_Access_Log->ip;
        }
        if (!is_null($Contest_Access_Log->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Contest_Access_Log->time;
        }
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysql_real_escape_string($value);
                $clauses[] = "`{$column}` LIKE '%{$value}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `Contest_Access_Log`.`contest_id`, `Contest_Access_Log`.`user_id`, `Contest_Access_Log`.`ip`, `Contest_Access_Log`.`time` FROM `Contest_Access_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . mysql_real_escape_string($orderBy) . '` ' . mysql_real_escape_string($orden);
        }
        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT '. (int)$offset . ', ' . (int)$rowcount;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestAccessLog($row);
        }
        return $ar;
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestAccessLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ContestAccessLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog a crear.
     */
    final private static function create(ContestAccessLog $Contest_Access_Log) {
        if (is_null($Contest_Access_Log->time)) {
            $Contest_Access_Log->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Contest_Access_Log (`contest_id`, `user_id`, `ip`, `time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Contest_Access_Log->contest_id,
            $Contest_Access_Log->user_id,
            $Contest_Access_Log->ip,
            $Contest_Access_Log->time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestAccessLog} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestAccessLog}.
     *
     * Aquellas variables que tienen valores NULL seran excluidos en la busqueda (los valores 0 y false no son tomados como NULL) .
     * No es necesario ordenar los objetos criterio, asi como tambien es posible mezclar atributos.
     * Si algun atributo solo esta especificado en solo uno de los objetos de criterio se buscara que los resultados conicidan exactamente en ese campo.
     *
     * <code>
     *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito
     *   // mayor a 2000 y menor a 5000. Y que tengan un descuento del 50%.
     *   $cr1 = new Cliente();
     *   $cr1->limite_credito = "2000";
     *   $cr1->descuento = "50";
     *
     *   $cr2 = new Cliente();
     *   $cr2->limite_credito = "5000";
     *   $resultados = ClienteDAO::byRange($cr1, $cr2);
     *
     *   foreach($resultados as $c ){
     *       echo $c->nombre . "<br>";
     *   }
     * </code>
     * @static
     * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
     * @param ContestAccessLog [$Contest_Access_Log] El objeto de tipo ContestAccessLog
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ContestAccessLog $Contest_Access_LogA, ContestAccessLog $Contest_Access_LogB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Contest_Access_LogA->contest_id;
        $b = $Contest_Access_LogB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_Access_LogA->user_id;
        $b = $Contest_Access_LogB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_Access_LogA->ip;
        $b = $Contest_Access_LogB->ip;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`ip` >= ? AND `ip` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`ip` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_Access_LogA->time;
        $b = $Contest_Access_LogB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contest_Access_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestAccessLog($row);
        }
        return $ar;
    }
}
