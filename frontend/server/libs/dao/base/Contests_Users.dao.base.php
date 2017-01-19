<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestsUsers Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ContestsUsers }.
 * @access public
 * @abstract
 *
 */
abstract class ContestsUsersDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contests_Users`.`user_id`, `Contests_Users`.`contest_id`, `Contests_Users`.`access_time`, `Contests_Users`.`score`, `Contests_Users`.`time`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestsUsers} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ContestsUsers $Contests_Users) {
        if (!is_null(self::getByPK($Contests_Users->user_id, $Contests_Users->contest_id))) {
            return ContestsUsersDAOBase::update($Contests_Users);
        } else {
            return ContestsUsersDAOBase::create($Contests_Users);
        }
    }

    /**
     * Obtener {@link ContestsUsers} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ContestsUsers} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ContestsUsers Un objeto del tipo {@link ContestsUsers}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id, $contest_id) {
        if (is_null($user_id) || is_null($contest_id)) {
            return null;
        }
        $sql = 'SELECT `Contests_Users`.`user_id`, `Contests_Users`.`contest_id`, `Contests_Users`.`access_time`, `Contests_Users`.`score`, `Contests_Users`.`time` FROM Contests_Users WHERE (user_id = ? AND contest_id = ?) LIMIT 1;';
        $params = [$user_id, $contest_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ContestsUsers($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ContestsUsers}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestsUsers}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contests_Users`.`user_id`, `Contests_Users`.`contest_id`, `Contests_Users`.`access_time`, `Contests_Users`.`score`, `Contests_Users`.`time` from Contests_Users';
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
            $allData[] = new ContestsUsers($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestsUsers} de la base de datos.
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
      * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contests_Users, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contests_Users instanceof ContestsUsers)) {
            return self::search(new ContestsUsers($Contests_Users));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contests_Users->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Contests_Users->user_id;
        }
        if (!is_null($Contests_Users->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contests_Users->contest_id;
        }
        if (!is_null($Contests_Users->access_time)) {
            $clauses[] = '`access_time` = ?';
            $params[] = $Contests_Users->access_time;
        }
        if (!is_null($Contests_Users->score)) {
            $clauses[] = '`score` = ?';
            $params[] = $Contests_Users->score;
        }
        if (!is_null($Contests_Users->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Contests_Users->time;
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
        $sql = 'SELECT `Contests_Users`.`user_id`, `Contests_Users`.`contest_id`, `Contests_Users`.`access_time`, `Contests_Users`.`score`, `Contests_Users`.`time` FROM `Contests_Users`';
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
            $ar[] = new ContestsUsers($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a actualizar.
      */
    final private static function update(ContestsUsers $Contests_Users) {
        $sql = 'UPDATE `Contests_Users` SET `access_time` = ?, `score` = ?, `time` = ? WHERE `user_id` = ? AND `contest_id` = ?;';
        $params = [
            $Contests_Users->access_time,
            $Contests_Users->score,
            $Contests_Users->time,
            $Contests_Users->user_id,$Contests_Users->contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestsUsers suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ContestsUsers dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a crear.
     */
    final private static function create(ContestsUsers $Contests_Users) {
        if (is_null($Contests_Users->access_time)) {
            $Contests_Users->access_time = '0000-00-00 00:00:00';
        }
        if (is_null($Contests_Users->score)) {
            $Contests_Users->score = '1';
        }
        if (is_null($Contests_Users->time)) {
            $Contests_Users->time = '1';
        }
        $sql = 'INSERT INTO Contests_Users (`user_id`, `contest_id`, `access_time`, `score`, `time`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            $Contests_Users->user_id,
            $Contests_Users->contest_id,
            $Contests_Users->access_time,
            $Contests_Users->score,
            $Contests_Users->time,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestsUsers} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestsUsers}.
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
     * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
     * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ContestsUsers $Contests_UsersA, ContestsUsers $Contests_UsersB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Contests_UsersA->user_id;
        $b = $Contests_UsersB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contests_UsersA->contest_id;
        $b = $Contests_UsersB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contests_UsersA->access_time;
        $b = $Contests_UsersB->access_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`access_time` >= ? AND `access_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`access_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contests_UsersA->score;
        $b = $Contests_UsersB->score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`score` >= ? AND `score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contests_UsersA->time;
        $b = $Contests_UsersB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contests_Users`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestsUsers($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ContestsUsers suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ContestsUsers [$Contests_Users] El objeto de tipo ContestsUsers a eliminar
     */
    final public static function delete(ContestsUsers $Contests_Users) {
        if (is_null(self::getByPK($Contests_Users->user_id, $Contests_Users->contest_id))) {
            throw new Exception('Campo no encontrado.');
        }
        $sql = 'DELETE FROM `Contests_Users` WHERE user_id = ? AND contest_id = ?;';
        $params = [$Contests_Users->user_id, $Contests_Users->contest_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
