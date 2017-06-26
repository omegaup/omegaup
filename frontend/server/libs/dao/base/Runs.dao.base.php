<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Runs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }.
 * @access public
 * @abstract
 *
 */
abstract class RunsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Runs`.`run_id`, `Runs`.`user_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`test`, `Runs`.`judged_by`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Runs} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Runs [$Runs] El objeto de tipo Runs
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Runs $Runs) {
        if (!is_null(self::getByPK($Runs->run_id))) {
            return RunsDAOBase::update($Runs);
        } else {
            return RunsDAOBase::create($Runs);
        }
    }

    /**
     * Obtener {@link Runs} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Runs} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
     */
    final public static function getByPK($run_id) {
        if (is_null($run_id)) {
            return null;
        }
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`user_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`test`, `Runs`.`judged_by` FROM Runs WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Runs($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Runs}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Runs}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`user_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`test`, `Runs`.`judged_by` from Runs';
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
            $allData[] = new Runs($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos.
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
      * @param Runs [$Runs] El objeto de tipo Runs
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Runs, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Runs instanceof Runs)) {
            $Runs = new Runs($Runs);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Runs->run_id)) {
            $clauses[] = '`run_id` = ?';
            $params[] = $Runs->run_id;
        }
        if (!is_null($Runs->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Runs->user_id;
        }
        if (!is_null($Runs->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $Runs->problem_id;
        }
        if (!is_null($Runs->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Runs->problemset_id;
        }
        if (!is_null($Runs->guid)) {
            $clauses[] = '`guid` = ?';
            $params[] = $Runs->guid;
        }
        if (!is_null($Runs->language)) {
            $clauses[] = '`language` = ?';
            $params[] = $Runs->language;
        }
        if (!is_null($Runs->status)) {
            $clauses[] = '`status` = ?';
            $params[] = $Runs->status;
        }
        if (!is_null($Runs->verdict)) {
            $clauses[] = '`verdict` = ?';
            $params[] = $Runs->verdict;
        }
        if (!is_null($Runs->runtime)) {
            $clauses[] = '`runtime` = ?';
            $params[] = $Runs->runtime;
        }
        if (!is_null($Runs->penalty)) {
            $clauses[] = '`penalty` = ?';
            $params[] = $Runs->penalty;
        }
        if (!is_null($Runs->memory)) {
            $clauses[] = '`memory` = ?';
            $params[] = $Runs->memory;
        }
        if (!is_null($Runs->score)) {
            $clauses[] = '`score` = ?';
            $params[] = $Runs->score;
        }
        if (!is_null($Runs->contest_score)) {
            $clauses[] = '`contest_score` = ?';
            $params[] = $Runs->contest_score;
        }
        if (!is_null($Runs->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Runs->time;
        }
        if (!is_null($Runs->submit_delay)) {
            $clauses[] = '`submit_delay` = ?';
            $params[] = $Runs->submit_delay;
        }
        if (!is_null($Runs->test)) {
            $clauses[] = '`test` = ?';
            $params[] = $Runs->test;
        }
        if (!is_null($Runs->judged_by)) {
            $clauses[] = '`judged_by` = ?';
            $params[] = $Runs->judged_by;
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
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`user_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`test`, `Runs`.`judged_by` FROM `Runs`';
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
            $ar[] = new Runs($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Runs [$Runs] El objeto de tipo Runs a actualizar.
      */
    final private static function update(Runs $Runs) {
        $sql = 'UPDATE `Runs` SET `user_id` = ?, `problem_id` = ?, `problemset_id` = ?, `guid` = ?, `language` = ?, `status` = ?, `verdict` = ?, `runtime` = ?, `penalty` = ?, `memory` = ?, `score` = ?, `contest_score` = ?, `time` = ?, `submit_delay` = ?, `test` = ?, `judged_by` = ? WHERE `run_id` = ?;';
        $params = [
            $Runs->user_id,
            $Runs->problem_id,
            $Runs->problemset_id,
            $Runs->guid,
            $Runs->language,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->submit_delay,
            $Runs->test,
            $Runs->judged_by,
            $Runs->run_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Runs suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Runs dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Runs [$Runs] El objeto de tipo Runs a crear.
     */
    final private static function create(Runs $Runs) {
        if (is_null($Runs->status)) {
            $Runs->status = 'new';
        }
        if (is_null($Runs->runtime)) {
            $Runs->runtime = '0';
        }
        if (is_null($Runs->penalty)) {
            $Runs->penalty = '0';
        }
        if (is_null($Runs->memory)) {
            $Runs->memory = '0';
        }
        if (is_null($Runs->score)) {
            $Runs->score = '0';
        }
        if (is_null($Runs->time)) {
            $Runs->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Runs->submit_delay)) {
            $Runs->submit_delay = '0';
        }
        if (is_null($Runs->test)) {
            $Runs->test = '0';
        }
        $sql = 'INSERT INTO Runs (`run_id`, `user_id`, `problem_id`, `problemset_id`, `guid`, `language`, `status`, `verdict`, `runtime`, `penalty`, `memory`, `score`, `contest_score`, `time`, `submit_delay`, `test`, `judged_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Runs->run_id,
            $Runs->user_id,
            $Runs->problem_id,
            $Runs->problemset_id,
            $Runs->guid,
            $Runs->language,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->submit_delay,
            $Runs->test,
            $Runs->judged_by,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Runs->run_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Runs} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Runs}.
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
     * @param Runs [$Runs] El objeto de tipo Runs
     * @param Runs [$Runs] El objeto de tipo Runs
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Runs $RunsA, Runs $RunsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $RunsA->run_id;
        $b = $RunsB->run_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`run_id` >= ? AND `run_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`run_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->user_id;
        $b = $RunsB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->problem_id;
        $b = $RunsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->problemset_id;
        $b = $RunsB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->guid;
        $b = $RunsB->guid;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`guid` >= ? AND `guid` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`guid` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->language;
        $b = $RunsB->language;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`language` >= ? AND `language` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`language` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->status;
        $b = $RunsB->status;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`status` >= ? AND `status` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`status` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->verdict;
        $b = $RunsB->verdict;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`verdict` >= ? AND `verdict` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`verdict` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->runtime;
        $b = $RunsB->runtime;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`runtime` >= ? AND `runtime` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`runtime` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->penalty;
        $b = $RunsB->penalty;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`penalty` >= ? AND `penalty` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`penalty` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->memory;
        $b = $RunsB->memory;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`memory` >= ? AND `memory` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`memory` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->score;
        $b = $RunsB->score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`score` >= ? AND `score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->contest_score;
        $b = $RunsB->contest_score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_score` >= ? AND `contest_score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->time;
        $b = $RunsB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->submit_delay;
        $b = $RunsB->submit_delay;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`submit_delay` >= ? AND `submit_delay` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`submit_delay` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->test;
        $b = $RunsB->test;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`test` >= ? AND `test` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`test` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $RunsA->judged_by;
        $b = $RunsB->judged_by;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`judged_by` >= ? AND `judged_by` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`judged_by` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Runs`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Runs($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Runs suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Runs [$Runs] El objeto de tipo Runs a eliminar
     */
    final public static function delete(Runs $Runs) {
        if (is_null(self::getByPK($Runs->run_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Runs` WHERE run_id = ?;';
        $params = [$Runs->run_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
