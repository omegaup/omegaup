<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRank Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link UserRank }.
 * @access public
 * @abstract
 *
 */
abstract class UserRankDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UserRank} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param UserRank [$User_Rank] El objeto de tipo UserRank
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(UserRank $User_Rank) {
        if (!is_null(self::getByPK($User_Rank->user_id))) {
            return UserRankDAOBase::update($User_Rank);
        } else {
            return UserRankDAOBase::create($User_Rank);
        }
    }

    /**
     * Obtener {@link UserRank} por llave primaria.
     *
     * Este metodo cargara un objeto {@link UserRank} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link UserRank Un objeto del tipo {@link UserRank}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id` FROM User_Rank WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new UserRank($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link UserRank}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UserRank}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id` from User_Rank';
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
            $allData[] = new UserRank($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UserRank} de la base de datos.
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
      * @param UserRank [$User_Rank] El objeto de tipo UserRank
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($User_Rank, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($User_Rank instanceof UserRank)) {
            $User_Rank = new UserRank($User_Rank);
        }

        $clauses = [];
        $params = [];
        if (!is_null($User_Rank->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $User_Rank->user_id;
        }
        if (!is_null($User_Rank->rank)) {
            $clauses[] = '`rank` = ?';
            $params[] = $User_Rank->rank;
        }
        if (!is_null($User_Rank->problems_solved_count)) {
            $clauses[] = '`problems_solved_count` = ?';
            $params[] = $User_Rank->problems_solved_count;
        }
        if (!is_null($User_Rank->score)) {
            $clauses[] = '`score` = ?';
            $params[] = $User_Rank->score;
        }
        if (!is_null($User_Rank->username)) {
            $clauses[] = '`username` = ?';
            $params[] = $User_Rank->username;
        }
        if (!is_null($User_Rank->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $User_Rank->name;
        }
        if (!is_null($User_Rank->country_id)) {
            $clauses[] = '`country_id` = ?';
            $params[] = $User_Rank->country_id;
        }
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysql_real_escape_string($value);
                $clauses[] = "`{$column}` LIKE '%{$escapedValue}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id` FROM `User_Rank`';
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
            $ar[] = new UserRank($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param UserRank [$User_Rank] El objeto de tipo UserRank a actualizar.
      */
    final private static function update(UserRank $User_Rank) {
        $sql = 'UPDATE `User_Rank` SET `rank` = ?, `problems_solved_count` = ?, `score` = ?, `username` = ?, `name` = ?, `country_id` = ? WHERE `user_id` = ?;';
        $params = [
            $User_Rank->rank,
            $User_Rank->problems_solved_count,
            $User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRank suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto UserRank dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param UserRank [$User_Rank] El objeto de tipo UserRank a crear.
     */
    final private static function create(UserRank $User_Rank) {
        if (is_null($User_Rank->problems_solved_count)) {
            $User_Rank->problems_solved_count = 0;
        }
        if (is_null($User_Rank->score)) {
            $User_Rank->score = 0;
        }
        $sql = 'INSERT INTO User_Rank (`user_id`, `rank`, `problems_solved_count`, `score`, `username`, `name`, `country_id`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $User_Rank->user_id,
            $User_Rank->rank,
            $User_Rank->problems_solved_count,
            $User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UserRank} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link UserRank}.
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
     * @param UserRank [$User_Rank] El objeto de tipo UserRank
     * @param UserRank [$User_Rank] El objeto de tipo UserRank
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(UserRank $User_RankA, UserRank $User_RankB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $User_RankA->user_id;
        $b = $User_RankB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->rank;
        $b = $User_RankB->rank;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`rank` >= ? AND `rank` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`rank` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->problems_solved_count;
        $b = $User_RankB->problems_solved_count;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problems_solved_count` >= ? AND `problems_solved_count` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problems_solved_count` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->score;
        $b = $User_RankB->score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`score` >= ? AND `score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->username;
        $b = $User_RankB->username;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`username` >= ? AND `username` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`username` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->name;
        $b = $User_RankB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_RankA->country_id;
        $b = $User_RankB->country_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`country_id` >= ? AND `country_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`country_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `User_Rank`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new UserRank($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto UserRank suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param UserRank [$User_Rank] El objeto de tipo UserRank a eliminar
     */
    final public static function delete(UserRank $User_Rank) {
        if (is_null(self::getByPK($User_Rank->user_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `User_Rank` WHERE user_id = ?;';
        $params = [$User_Rank->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
