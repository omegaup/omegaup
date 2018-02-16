<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ContestLog }.
 * @access public
 * @abstract
 *
 */
abstract class ContestLogDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_visibility`, `Contest_Log`.`to_visibility`, `Contest_Log`.`time`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestLog} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ContestLog $Contest_Log) {
        if (!is_null(self::getByPK($Contest_Log->public_contest_id))) {
            return ContestLogDAOBase::update($Contest_Log);
        } else {
            return ContestLogDAOBase::create($Contest_Log);
        }
    }

    /**
     * Obtener {@link ContestLog} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ContestLog} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ContestLog Un objeto del tipo {@link ContestLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($public_contest_id) {
        if (is_null($public_contest_id)) {
            return null;
        }
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_visibility`, `Contest_Log`.`to_visibility`, `Contest_Log`.`time` FROM Contest_Log WHERE (public_contest_id = ?) LIMIT 1;';
        $params = [$public_contest_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ContestLog($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ContestLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_visibility`, `Contest_Log`.`to_visibility`, `Contest_Log`.`time` from Contest_Log';
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
            $allData[] = new ContestLog($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestLog} de la base de datos.
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
      * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contest_Log, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contest_Log instanceof ContestLog)) {
            $Contest_Log = new ContestLog($Contest_Log);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contest_Log->public_contest_id)) {
            $clauses[] = '`public_contest_id` = ?';
            $params[] = $Contest_Log->public_contest_id;
        }
        if (!is_null($Contest_Log->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contest_Log->contest_id;
        }
        if (!is_null($Contest_Log->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Contest_Log->user_id;
        }
        if (!is_null($Contest_Log->from_visibility)) {
            $clauses[] = '`from_visibility` = ?';
            $params[] = $Contest_Log->from_visibility;
        }
        if (!is_null($Contest_Log->to_visibility)) {
            $clauses[] = '`to_visibility` = ?';
            $params[] = $Contest_Log->to_visibility;
        }
        if (!is_null($Contest_Log->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Contest_Log->time;
        }
        global $conn;
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysqli_real_escape_string($conn->_connectionID, $value);
                $clauses[] = "`{$column}` LIKE '%{$escapedValue}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_visibility`, `Contest_Log`.`to_visibility`, `Contest_Log`.`time` FROM `Contest_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orderBy) . '` ' . ($orden == 'DESC' ? 'DESC' : 'ASC');
        }
        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT '. (int)$offset . ', ' . (int)$rowcount;
        }
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestLog($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a actualizar.
      */
    final private static function update(ContestLog $Contest_Log) {
        $sql = 'UPDATE `Contest_Log` SET `contest_id` = ?, `user_id` = ?, `from_visibility` = ?, `to_visibility` = ?, `time` = ? WHERE `public_contest_id` = ?;';
        $params = [
            $Contest_Log->contest_id,
            $Contest_Log->user_id,
            $Contest_Log->from_visibility,
            $Contest_Log->to_visibility,
            $Contest_Log->time,
            $Contest_Log->public_contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ContestLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a crear.
     */
    final private static function create(ContestLog $Contest_Log) {
        if (is_null($Contest_Log->from_visibility)) {
            $Contest_Log->from_visibility = '0';
        }
        if (is_null($Contest_Log->to_visibility)) {
            $Contest_Log->to_visibility = '1';
        }
        if (is_null($Contest_Log->time)) {
            $Contest_Log->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Contest_Log (`public_contest_id`, `contest_id`, `user_id`, `from_visibility`, `to_visibility`, `time`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Contest_Log->public_contest_id,
            $Contest_Log->contest_id,
            $Contest_Log->user_id,
            $Contest_Log->from_visibility,
            $Contest_Log->to_visibility,
            $Contest_Log->time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Contest_Log->public_contest_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestLog} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestLog}.
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
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ContestLog $Contest_LogA, ContestLog $Contest_LogB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Contest_LogA->public_contest_id;
        $b = $Contest_LogB->public_contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`public_contest_id` >= ? AND `public_contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`public_contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_LogA->contest_id;
        $b = $Contest_LogB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_LogA->user_id;
        $b = $Contest_LogB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_LogA->from_visibility;
        $b = $Contest_LogB->from_visibility;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`from_visibility` >= ? AND `from_visibility` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`from_visibility` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_LogA->to_visibility;
        $b = $Contest_LogB->to_visibility;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`to_visibility` >= ? AND `to_visibility` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`to_visibility` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_LogA->time;
        $b = $Contest_LogB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contest_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestLog($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ContestLog suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a eliminar
     */
    final public static function delete(ContestLog $Contest_Log) {
        if (is_null(self::getByPK($Contest_Log->public_contest_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Contest_Log` WHERE public_contest_id = ?;';
        $params = [$Contest_Log->public_contest_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
