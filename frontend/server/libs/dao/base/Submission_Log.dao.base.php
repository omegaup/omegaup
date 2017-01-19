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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link SubmissionLog }.
 * @access public
 * @abstract
 *
 */
abstract class SubmissionLogDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Submission_Log`.`contest_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`ip`, `Submission_Log`.`time`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link SubmissionLog} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(SubmissionLog $Submission_Log) {
        if (!is_null(self::getByPK($Submission_Log->run_id))) {
            return SubmissionLogDAOBase::update($Submission_Log);
        } else {
            return SubmissionLogDAOBase::create($Submission_Log);
        }
    }

    /**
     * Obtener {@link SubmissionLog} por llave primaria.
     *
     * Este metodo cargara un objeto {@link SubmissionLog} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link SubmissionLog Un objeto del tipo {@link SubmissionLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($run_id) {
        if (is_null($run_id)) {
            return null;
        }
        $sql = 'SELECT `Submission_Log`.`contest_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`ip`, `Submission_Log`.`time` FROM Submission_Log WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new SubmissionLog($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link SubmissionLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link SubmissionLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Submission_Log`.`contest_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`ip`, `Submission_Log`.`time` from Submission_Log';
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
            $allData[] = new SubmissionLog($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link SubmissionLog} de la base de datos.
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
      * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Submission_Log, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Submission_Log instanceof SubmissionLog)) {
            return self::search(new SubmissionLog($Submission_Log));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Submission_Log->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Submission_Log->contest_id;
        }
        if (!is_null($Submission_Log->run_id)) {
            $clauses[] = '`run_id` = ?';
            $params[] = $Submission_Log->run_id;
        }
        if (!is_null($Submission_Log->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Submission_Log->user_id;
        }
        if (!is_null($Submission_Log->ip)) {
            $clauses[] = '`ip` = ?';
            $params[] = $Submission_Log->ip;
        }
        if (!is_null($Submission_Log->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Submission_Log->time;
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
        $sql = 'SELECT `Submission_Log`.`contest_id`, `Submission_Log`.`run_id`, `Submission_Log`.`user_id`, `Submission_Log`.`ip`, `Submission_Log`.`time` FROM `Submission_Log`';
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
            $ar[] = new SubmissionLog($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a actualizar.
      */
    final private static function update(SubmissionLog $Submission_Log) {
        $sql = 'UPDATE `Submission_Log` SET `contest_id` = ?, `user_id` = ?, `ip` = ?, `time` = ? WHERE `run_id` = ?;';
        $params = [
            $Submission_Log->contest_id,
            $Submission_Log->user_id,
            $Submission_Log->ip,
            $Submission_Log->time,
            $Submission_Log->run_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto SubmissionLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto SubmissionLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a crear.
     */
    final private static function create(SubmissionLog $Submission_Log) {
        if (is_null($Submission_Log->time)) {
            $Submission_Log->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Submission_Log (`contest_id`, `run_id`, `user_id`, `ip`, `time`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            $Submission_Log->contest_id,
            $Submission_Log->run_id,
            $Submission_Log->user_id,
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

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link SubmissionLog} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link SubmissionLog}.
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
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(SubmissionLog $Submission_LogA, SubmissionLog $Submission_LogB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Submission_LogA->contest_id;
        $b = $Submission_LogB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Submission_LogA->run_id;
        $b = $Submission_LogB->run_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`run_id` >= ? AND `run_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`run_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Submission_LogA->user_id;
        $b = $Submission_LogB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Submission_LogA->ip;
        $b = $Submission_LogB->ip;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`ip` >= ? AND `ip` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`ip` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Submission_LogA->time;
        $b = $Submission_LogB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Submission_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new SubmissionLog($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto SubmissionLog suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param SubmissionLog [$Submission_Log] El objeto de tipo SubmissionLog a eliminar
     */
    final public static function delete(SubmissionLog $Submission_Log) {
        if (is_null(self::getByPK($Submission_Log->run_id))) {
            throw new Exception('Campo no encontrado.');
        }
        $sql = 'DELETE FROM `Submission_Log` WHERE run_id = ?;';
        $params = [$Submission_Log->run_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
