<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Contests Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }.
 * @access public
 * @abstract
 *
 */
abstract class ContestsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contests`.`contest_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`public`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`scoreboard_url`, `Contests`.`scoreboard_url_admin`, `Contests`.`urgent`, `Contests`.`contestant_must_register`, `Contests`.`languages`, `Contests`.`recommended`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Contests} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Contests [$Contests] El objeto de tipo Contests
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Contests $Contests) {
        if (!is_null(self::getByPK($Contests->contest_id))) {
            return ContestsDAOBase::update($Contests);
        } else {
            return ContestsDAOBase::create($Contests);
        }
    }

    /**
     * Obtener {@link Contests} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Contests} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Contests Un objeto del tipo {@link Contests}. NULL si no hay tal registro.
     */
    final public static function getByPK($contest_id) {
        if (is_null($contest_id)) {
            return null;
        }
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`public`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`scoreboard_url`, `Contests`.`scoreboard_url_admin`, `Contests`.`urgent`, `Contests`.`contestant_must_register`, `Contests`.`languages`, `Contests`.`recommended` FROM Contests WHERE (contest_id = ?) LIMIT 1;';
        $params = [$contest_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Contests($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Contests}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Contests}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`public`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`scoreboard_url`, `Contests`.`scoreboard_url_admin`, `Contests`.`urgent`, `Contests`.`contestant_must_register`, `Contests`.`languages`, `Contests`.`recommended` from Contests';
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
            $allData[] = new Contests($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos.
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
      * @param Contests [$Contests] El objeto de tipo Contests
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contests, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contests instanceof Contests)) {
            return self::search(new Contests($Contests));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contests->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contests->contest_id;
        }
        if (!is_null($Contests->acl_id)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = $Contests->acl_id;
        }
        if (!is_null($Contests->title)) {
            $clauses[] = '`title` = ?';
            $params[] = $Contests->title;
        }
        if (!is_null($Contests->description)) {
            $clauses[] = '`description` = ?';
            $params[] = $Contests->description;
        }
        if (!is_null($Contests->start_time)) {
            $clauses[] = '`start_time` = ?';
            $params[] = $Contests->start_time;
        }
        if (!is_null($Contests->finish_time)) {
            $clauses[] = '`finish_time` = ?';
            $params[] = $Contests->finish_time;
        }
        if (!is_null($Contests->window_length)) {
            $clauses[] = '`window_length` = ?';
            $params[] = $Contests->window_length;
        }
        if (!is_null($Contests->rerun_id)) {
            $clauses[] = '`rerun_id` = ?';
            $params[] = $Contests->rerun_id;
        }
        if (!is_null($Contests->public)) {
            $clauses[] = '`public` = ?';
            $params[] = $Contests->public;
        }
        if (!is_null($Contests->alias)) {
            $clauses[] = '`alias` = ?';
            $params[] = $Contests->alias;
        }
        if (!is_null($Contests->scoreboard)) {
            $clauses[] = '`scoreboard` = ?';
            $params[] = $Contests->scoreboard;
        }
        if (!is_null($Contests->points_decay_factor)) {
            $clauses[] = '`points_decay_factor` = ?';
            $params[] = $Contests->points_decay_factor;
        }
        if (!is_null($Contests->partial_score)) {
            $clauses[] = '`partial_score` = ?';
            $params[] = $Contests->partial_score;
        }
        if (!is_null($Contests->submissions_gap)) {
            $clauses[] = '`submissions_gap` = ?';
            $params[] = $Contests->submissions_gap;
        }
        if (!is_null($Contests->feedback)) {
            $clauses[] = '`feedback` = ?';
            $params[] = $Contests->feedback;
        }
        if (!is_null($Contests->penalty)) {
            $clauses[] = '`penalty` = ?';
            $params[] = $Contests->penalty;
        }
        if (!is_null($Contests->penalty_type)) {
            $clauses[] = '`penalty_type` = ?';
            $params[] = $Contests->penalty_type;
        }
        if (!is_null($Contests->penalty_calc_policy)) {
            $clauses[] = '`penalty_calc_policy` = ?';
            $params[] = $Contests->penalty_calc_policy;
        }
        if (!is_null($Contests->show_scoreboard_after)) {
            $clauses[] = '`show_scoreboard_after` = ?';
            $params[] = $Contests->show_scoreboard_after;
        }
        if (!is_null($Contests->scoreboard_url)) {
            $clauses[] = '`scoreboard_url` = ?';
            $params[] = $Contests->scoreboard_url;
        }
        if (!is_null($Contests->scoreboard_url_admin)) {
            $clauses[] = '`scoreboard_url_admin` = ?';
            $params[] = $Contests->scoreboard_url_admin;
        }
        if (!is_null($Contests->urgent)) {
            $clauses[] = '`urgent` = ?';
            $params[] = $Contests->urgent;
        }
        if (!is_null($Contests->contestant_must_register)) {
            $clauses[] = '`contestant_must_register` = ?';
            $params[] = $Contests->contestant_must_register;
        }
        if (!is_null($Contests->languages)) {
            $clauses[] = '`languages` = ?';
            $params[] = $Contests->languages;
        }
        if (!is_null($Contests->recommended)) {
            $clauses[] = '`recommended` = ?';
            $params[] = $Contests->recommended;
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
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`public`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`scoreboard_url`, `Contests`.`scoreboard_url_admin`, `Contests`.`urgent`, `Contests`.`contestant_must_register`, `Contests`.`languages`, `Contests`.`recommended` FROM `Contests`';
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
            $ar[] = new Contests($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Contests [$Contests] El objeto de tipo Contests a actualizar.
      */
    final private static function update(Contests $Contests) {
        $sql = 'UPDATE `Contests` SET `acl_id` = ?, `title` = ?, `description` = ?, `start_time` = ?, `finish_time` = ?, `window_length` = ?, `rerun_id` = ?, `public` = ?, `alias` = ?, `scoreboard` = ?, `points_decay_factor` = ?, `partial_score` = ?, `submissions_gap` = ?, `feedback` = ?, `penalty` = ?, `penalty_type` = ?, `penalty_calc_policy` = ?, `show_scoreboard_after` = ?, `scoreboard_url` = ?, `scoreboard_url_admin` = ?, `urgent` = ?, `contestant_must_register` = ?, `languages` = ?, `recommended` = ? WHERE `contest_id` = ?;';
        $params = [
            $Contests->acl_id,
            $Contests->title,
            $Contests->description,
            $Contests->start_time,
            $Contests->finish_time,
            $Contests->window_length,
            $Contests->rerun_id,
            $Contests->public,
            $Contests->alias,
            $Contests->scoreboard,
            $Contests->points_decay_factor,
            $Contests->partial_score,
            $Contests->submissions_gap,
            $Contests->feedback,
            $Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            $Contests->show_scoreboard_after,
            $Contests->scoreboard_url,
            $Contests->scoreboard_url_admin,
            $Contests->urgent,
            $Contests->contestant_must_register,
            $Contests->languages,
            $Contests->recommended,
            $Contests->contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Contests suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Contests dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Contests [$Contests] El objeto de tipo Contests a crear.
     */
    final private static function create(Contests $Contests) {
        if (is_null($Contests->start_time)) {
            $Contests->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Contests->finish_time)) {
            $Contests->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Contests->public)) {
            $Contests->public = '1';
        }
        if (is_null($Contests->scoreboard)) {
            $Contests->scoreboard = '1';
        }
        if (is_null($Contests->points_decay_factor)) {
            $Contests->points_decay_factor = '0';
        }
        if (is_null($Contests->partial_score)) {
            $Contests->partial_score = '1';
        }
        if (is_null($Contests->submissions_gap)) {
            $Contests->submissions_gap = '1';
        }
        if (is_null($Contests->penalty)) {
            $Contests->penalty = '1';
        }
        if (is_null($Contests->show_scoreboard_after)) {
            $Contests->show_scoreboard_after =  '1';
        }
        if (is_null($Contests->urgent)) {
            $Contests->urgent = 0;
        }
        if (is_null($Contests->contestant_must_register)) {
            $Contests->contestant_must_register = '0';
        }
        if (is_null($Contests->recommended)) {
            $Contests->recommended =  '0';
        }
        $sql = 'INSERT INTO Contests (`contest_id`, `acl_id`, `title`, `description`, `start_time`, `finish_time`, `window_length`, `rerun_id`, `public`, `alias`, `scoreboard`, `points_decay_factor`, `partial_score`, `submissions_gap`, `feedback`, `penalty`, `penalty_type`, `penalty_calc_policy`, `show_scoreboard_after`, `scoreboard_url`, `scoreboard_url_admin`, `urgent`, `contestant_must_register`, `languages`, `recommended`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Contests->contest_id,
            $Contests->acl_id,
            $Contests->title,
            $Contests->description,
            $Contests->start_time,
            $Contests->finish_time,
            $Contests->window_length,
            $Contests->rerun_id,
            $Contests->public,
            $Contests->alias,
            $Contests->scoreboard,
            $Contests->points_decay_factor,
            $Contests->partial_score,
            $Contests->submissions_gap,
            $Contests->feedback,
            $Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            $Contests->show_scoreboard_after,
            $Contests->scoreboard_url,
            $Contests->scoreboard_url_admin,
            $Contests->urgent,
            $Contests->contestant_must_register,
            $Contests->languages,
            $Contests->recommended,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Contests->contest_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Contests} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Contests}.
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
     * @param Contests [$Contests] El objeto de tipo Contests
     * @param Contests [$Contests] El objeto de tipo Contests
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Contests $ContestsA, Contests $ContestsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $ContestsA->contest_id;
        $b = $ContestsB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->acl_id;
        $b = $ContestsB->acl_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`acl_id` >= ? AND `acl_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->title;
        $b = $ContestsB->title;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`title` >= ? AND `title` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`title` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->description;
        $b = $ContestsB->description;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`description` >= ? AND `description` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`description` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->start_time;
        $b = $ContestsB->start_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`start_time` >= ? AND `start_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`start_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->finish_time;
        $b = $ContestsB->finish_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`finish_time` >= ? AND `finish_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`finish_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->window_length;
        $b = $ContestsB->window_length;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`window_length` >= ? AND `window_length` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`window_length` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->rerun_id;
        $b = $ContestsB->rerun_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`rerun_id` >= ? AND `rerun_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`rerun_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->public;
        $b = $ContestsB->public;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`public` >= ? AND `public` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`public` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->alias;
        $b = $ContestsB->alias;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`alias` >= ? AND `alias` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`alias` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->scoreboard;
        $b = $ContestsB->scoreboard;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scoreboard` >= ? AND `scoreboard` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scoreboard` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->points_decay_factor;
        $b = $ContestsB->points_decay_factor;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`points_decay_factor` >= ? AND `points_decay_factor` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`points_decay_factor` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->partial_score;
        $b = $ContestsB->partial_score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`partial_score` >= ? AND `partial_score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`partial_score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->submissions_gap;
        $b = $ContestsB->submissions_gap;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`submissions_gap` >= ? AND `submissions_gap` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`submissions_gap` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->feedback;
        $b = $ContestsB->feedback;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`feedback` >= ? AND `feedback` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`feedback` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->penalty;
        $b = $ContestsB->penalty;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`penalty` >= ? AND `penalty` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`penalty` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->penalty_type;
        $b = $ContestsB->penalty_type;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`penalty_type` >= ? AND `penalty_type` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`penalty_type` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->penalty_calc_policy;
        $b = $ContestsB->penalty_calc_policy;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`penalty_calc_policy` >= ? AND `penalty_calc_policy` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`penalty_calc_policy` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->show_scoreboard_after;
        $b = $ContestsB->show_scoreboard_after;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`show_scoreboard_after` >= ? AND `show_scoreboard_after` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`show_scoreboard_after` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->scoreboard_url;
        $b = $ContestsB->scoreboard_url;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scoreboard_url` >= ? AND `scoreboard_url` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scoreboard_url` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->scoreboard_url_admin;
        $b = $ContestsB->scoreboard_url_admin;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scoreboard_url_admin` >= ? AND `scoreboard_url_admin` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scoreboard_url_admin` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->urgent;
        $b = $ContestsB->urgent;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`urgent` >= ? AND `urgent` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`urgent` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->contestant_must_register;
        $b = $ContestsB->contestant_must_register;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contestant_must_register` >= ? AND `contestant_must_register` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contestant_must_register` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->languages;
        $b = $ContestsB->languages;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`languages` >= ? AND `languages` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`languages` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ContestsA->recommended;
        $b = $ContestsB->recommended;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`recommended` >= ? AND `recommended` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`recommended` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contests`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Contests($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Contests suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Contests [$Contests] El objeto de tipo Contests a eliminar
     */
    final public static function delete(Contests $Contests) {
        if (is_null(self::getByPK($Contests->contest_id))) {
            throw new Exception('Campo no encontrado.');
        }
        $sql = 'DELETE FROM `Contests` WHERE contest_id = ?;';
        $params = [$Contests->contest_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
