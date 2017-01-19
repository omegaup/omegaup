<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`public`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`stack_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problems} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problems [$Problems] El objeto de tipo Problems
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Problems $Problems) {
        if (!is_null(self::getByPK($Problems->problem_id))) {
            return ProblemsDAOBase::update($Problems);
        } else {
            return ProblemsDAOBase::create($Problems);
        }
    }

    /**
     * Obtener {@link Problems} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Problems} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Problems Un objeto del tipo {@link Problems}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id) {
        if (is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`public`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`stack_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications` FROM Problems WHERE (problem_id = ?) LIMIT 1;';
        $params = [$problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Problems($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Problems}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Problems}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`public`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`stack_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications` from Problems';
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
            $allData[] = new Problems($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos.
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
      * @param Problems [$Problems] El objeto de tipo Problems
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problems, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problems instanceof Problems)) {
            return self::search(new Problems($Problems));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problems->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $Problems->problem_id;
        }
        if (!is_null($Problems->acl_id)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = $Problems->acl_id;
        }
        if (!is_null($Problems->public)) {
            $clauses[] = '`public` = ?';
            $params[] = $Problems->public;
        }
        if (!is_null($Problems->title)) {
            $clauses[] = '`title` = ?';
            $params[] = $Problems->title;
        }
        if (!is_null($Problems->alias)) {
            $clauses[] = '`alias` = ?';
            $params[] = $Problems->alias;
        }
        if (!is_null($Problems->validator)) {
            $clauses[] = '`validator` = ?';
            $params[] = $Problems->validator;
        }
        if (!is_null($Problems->languages)) {
            $clauses[] = '`languages` = ?';
            $params[] = $Problems->languages;
        }
        if (!is_null($Problems->server)) {
            $clauses[] = '`server` = ?';
            $params[] = $Problems->server;
        }
        if (!is_null($Problems->remote_id)) {
            $clauses[] = '`remote_id` = ?';
            $params[] = $Problems->remote_id;
        }
        if (!is_null($Problems->time_limit)) {
            $clauses[] = '`time_limit` = ?';
            $params[] = $Problems->time_limit;
        }
        if (!is_null($Problems->validator_time_limit)) {
            $clauses[] = '`validator_time_limit` = ?';
            $params[] = $Problems->validator_time_limit;
        }
        if (!is_null($Problems->overall_wall_time_limit)) {
            $clauses[] = '`overall_wall_time_limit` = ?';
            $params[] = $Problems->overall_wall_time_limit;
        }
        if (!is_null($Problems->extra_wall_time)) {
            $clauses[] = '`extra_wall_time` = ?';
            $params[] = $Problems->extra_wall_time;
        }
        if (!is_null($Problems->memory_limit)) {
            $clauses[] = '`memory_limit` = ?';
            $params[] = $Problems->memory_limit;
        }
        if (!is_null($Problems->output_limit)) {
            $clauses[] = '`output_limit` = ?';
            $params[] = $Problems->output_limit;
        }
        if (!is_null($Problems->stack_limit)) {
            $clauses[] = '`stack_limit` = ?';
            $params[] = $Problems->stack_limit;
        }
        if (!is_null($Problems->visits)) {
            $clauses[] = '`visits` = ?';
            $params[] = $Problems->visits;
        }
        if (!is_null($Problems->submissions)) {
            $clauses[] = '`submissions` = ?';
            $params[] = $Problems->submissions;
        }
        if (!is_null($Problems->accepted)) {
            $clauses[] = '`accepted` = ?';
            $params[] = $Problems->accepted;
        }
        if (!is_null($Problems->difficulty)) {
            $clauses[] = '`difficulty` = ?';
            $params[] = $Problems->difficulty;
        }
        if (!is_null($Problems->creation_date)) {
            $clauses[] = '`creation_date` = ?';
            $params[] = $Problems->creation_date;
        }
        if (!is_null($Problems->source)) {
            $clauses[] = '`source` = ?';
            $params[] = $Problems->source;
        }
        if (!is_null($Problems->order)) {
            $clauses[] = '`order` = ?';
            $params[] = $Problems->order;
        }
        if (!is_null($Problems->tolerance)) {
            $clauses[] = '`tolerance` = ?';
            $params[] = $Problems->tolerance;
        }
        if (!is_null($Problems->slow)) {
            $clauses[] = '`slow` = ?';
            $params[] = $Problems->slow;
        }
        if (!is_null($Problems->deprecated)) {
            $clauses[] = '`deprecated` = ?';
            $params[] = $Problems->deprecated;
        }
        if (!is_null($Problems->email_clarifications)) {
            $clauses[] = '`email_clarifications` = ?';
            $params[] = $Problems->email_clarifications;
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
        $sql = 'SELECT `Problems`.`problem_id`, `Problems`.`acl_id`, `Problems`.`public`, `Problems`.`title`, `Problems`.`alias`, `Problems`.`validator`, `Problems`.`languages`, `Problems`.`server`, `Problems`.`remote_id`, `Problems`.`time_limit`, `Problems`.`validator_time_limit`, `Problems`.`overall_wall_time_limit`, `Problems`.`extra_wall_time`, `Problems`.`memory_limit`, `Problems`.`output_limit`, `Problems`.`stack_limit`, `Problems`.`visits`, `Problems`.`submissions`, `Problems`.`accepted`, `Problems`.`difficulty`, `Problems`.`creation_date`, `Problems`.`source`, `Problems`.`order`, `Problems`.`tolerance`, `Problems`.`slow`, `Problems`.`deprecated`, `Problems`.`email_clarifications` FROM `Problems`';
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
            $ar[] = new Problems($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Problems [$Problems] El objeto de tipo Problems a actualizar.
      */
    final private static function update(Problems $Problems) {
        $sql = 'UPDATE `Problems` SET `acl_id` = ?, `public` = ?, `title` = ?, `alias` = ?, `validator` = ?, `languages` = ?, `server` = ?, `remote_id` = ?, `time_limit` = ?, `validator_time_limit` = ?, `overall_wall_time_limit` = ?, `extra_wall_time` = ?, `memory_limit` = ?, `output_limit` = ?, `stack_limit` = ?, `visits` = ?, `submissions` = ?, `accepted` = ?, `difficulty` = ?, `creation_date` = ?, `source` = ?, `order` = ?, `tolerance` = ?, `slow` = ?, `deprecated` = ?, `email_clarifications` = ? WHERE `problem_id` = ?;';
        $params = [
            $Problems->acl_id,
            $Problems->public,
            $Problems->title,
            $Problems->alias,
            $Problems->validator,
            $Problems->languages,
            $Problems->server,
            $Problems->remote_id,
            $Problems->time_limit,
            $Problems->validator_time_limit,
            $Problems->overall_wall_time_limit,
            $Problems->extra_wall_time,
            $Problems->memory_limit,
            $Problems->output_limit,
            $Problems->stack_limit,
            $Problems->visits,
            $Problems->submissions,
            $Problems->accepted,
            $Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            $Problems->tolerance,
            $Problems->slow,
            $Problems->deprecated,
            $Problems->email_clarifications,
            $Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problems suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Problems dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Problems [$Problems] El objeto de tipo Problems a crear.
     */
    final private static function create(Problems $Problems) {
        if (is_null($Problems->public)) {
            $Problems->public = '1';
        }
        if (is_null($Problems->validator)) {
            $Problems->validator = 'token-numeric';
        }
        if (is_null($Problems->languages)) {
            $Problems->languages = 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11';
        }
        if (is_null($Problems->time_limit)) {
            $Problems->time_limit = '3000';
        }
        if (is_null($Problems->validator_time_limit)) {
            $Problems->validator_time_limit = '3000';
        }
        if (is_null($Problems->overall_wall_time_limit)) {
            $Problems->overall_wall_time_limit = '60000';
        }
        if (is_null($Problems->extra_wall_time)) {
            $Problems->extra_wall_time = '0';
        }
        if (is_null($Problems->memory_limit)) {
            $Problems->memory_limit = '64';
        }
        if (is_null($Problems->output_limit)) {
            $Problems->output_limit = '10240';
        }
        if (is_null($Problems->stack_limit)) {
            $Problems->stack_limit = '10485760';
        }
        if (is_null($Problems->visits)) {
            $Problems->visits = '0';
        }
        if (is_null($Problems->submissions)) {
            $Problems->submissions = '0';
        }
        if (is_null($Problems->accepted)) {
            $Problems->accepted = '0';
        }
        if (is_null($Problems->difficulty)) {
            $Problems->difficulty = '0';
        }
        if (is_null($Problems->creation_date)) {
            $Problems->creation_date = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Problems->order)) {
            $Problems->order = 'normal';
        }
        if (is_null($Problems->tolerance)) {
            $Problems->tolerance = 1e-9;
        }
        if (is_null($Problems->slow)) {
            $Problems->slow = 0;
        }
        if (is_null($Problems->deprecated)) {
            $Problems->deprecated = 0;
        }
        if (is_null($Problems->email_clarifications)) {
            $Problems->email_clarifications = 0;
        }
        $sql = 'INSERT INTO Problems (`problem_id`, `acl_id`, `public`, `title`, `alias`, `validator`, `languages`, `server`, `remote_id`, `time_limit`, `validator_time_limit`, `overall_wall_time_limit`, `extra_wall_time`, `memory_limit`, `output_limit`, `stack_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`, `tolerance`, `slow`, `deprecated`, `email_clarifications`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Problems->problem_id,
            $Problems->acl_id,
            $Problems->public,
            $Problems->title,
            $Problems->alias,
            $Problems->validator,
            $Problems->languages,
            $Problems->server,
            $Problems->remote_id,
            $Problems->time_limit,
            $Problems->validator_time_limit,
            $Problems->overall_wall_time_limit,
            $Problems->extra_wall_time,
            $Problems->memory_limit,
            $Problems->output_limit,
            $Problems->stack_limit,
            $Problems->visits,
            $Problems->submissions,
            $Problems->accepted,
            $Problems->difficulty,
            $Problems->creation_date,
            $Problems->source,
            $Problems->order,
            $Problems->tolerance,
            $Problems->slow,
            $Problems->deprecated,
            $Problems->email_clarifications,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problems->problem_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problems} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Problems}.
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
     * @param Problems [$Problems] El objeto de tipo Problems
     * @param Problems [$Problems] El objeto de tipo Problems
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Problems $ProblemsA, Problems $ProblemsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $ProblemsA->problem_id;
        $b = $ProblemsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->acl_id;
        $b = $ProblemsB->acl_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`acl_id` >= ? AND `acl_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->public;
        $b = $ProblemsB->public;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`public` >= ? AND `public` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`public` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->title;
        $b = $ProblemsB->title;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`title` >= ? AND `title` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`title` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->alias;
        $b = $ProblemsB->alias;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`alias` >= ? AND `alias` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`alias` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->validator;
        $b = $ProblemsB->validator;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`validator` >= ? AND `validator` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`validator` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->languages;
        $b = $ProblemsB->languages;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`languages` >= ? AND `languages` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`languages` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->server;
        $b = $ProblemsB->server;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`server` >= ? AND `server` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`server` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->remote_id;
        $b = $ProblemsB->remote_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`remote_id` >= ? AND `remote_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`remote_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->time_limit;
        $b = $ProblemsB->time_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time_limit` >= ? AND `time_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->validator_time_limit;
        $b = $ProblemsB->validator_time_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`validator_time_limit` >= ? AND `validator_time_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`validator_time_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->overall_wall_time_limit;
        $b = $ProblemsB->overall_wall_time_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`overall_wall_time_limit` >= ? AND `overall_wall_time_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`overall_wall_time_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->extra_wall_time;
        $b = $ProblemsB->extra_wall_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`extra_wall_time` >= ? AND `extra_wall_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`extra_wall_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->memory_limit;
        $b = $ProblemsB->memory_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`memory_limit` >= ? AND `memory_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`memory_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->output_limit;
        $b = $ProblemsB->output_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`output_limit` >= ? AND `output_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`output_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->stack_limit;
        $b = $ProblemsB->stack_limit;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`stack_limit` >= ? AND `stack_limit` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`stack_limit` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->visits;
        $b = $ProblemsB->visits;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`visits` >= ? AND `visits` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`visits` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->submissions;
        $b = $ProblemsB->submissions;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`submissions` >= ? AND `submissions` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`submissions` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->accepted;
        $b = $ProblemsB->accepted;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`accepted` >= ? AND `accepted` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`accepted` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->difficulty;
        $b = $ProblemsB->difficulty;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`difficulty` >= ? AND `difficulty` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`difficulty` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->creation_date;
        $b = $ProblemsB->creation_date;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`creation_date` >= ? AND `creation_date` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`creation_date` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->source;
        $b = $ProblemsB->source;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`source` >= ? AND `source` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`source` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->order;
        $b = $ProblemsB->order;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`order` >= ? AND `order` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`order` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->tolerance;
        $b = $ProblemsB->tolerance;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`tolerance` >= ? AND `tolerance` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`tolerance` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->slow;
        $b = $ProblemsB->slow;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`slow` >= ? AND `slow` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`slow` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->deprecated;
        $b = $ProblemsB->deprecated;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`deprecated` >= ? AND `deprecated` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`deprecated` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsA->email_clarifications;
        $b = $ProblemsB->email_clarifications;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`email_clarifications` >= ? AND `email_clarifications` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`email_clarifications` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problems`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Problems($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Problems suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Problems [$Problems] El objeto de tipo Problems a eliminar
     */
    final public static function delete(Problems $Problems) {
        if (is_null(self::getByPK($Problems->problem_id))) {
            throw new Exception('Campo no encontrado.');
        }
        $sql = 'DELETE FROM `Problems` WHERE problem_id = ?;';
        $params = [$Problems->problem_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
