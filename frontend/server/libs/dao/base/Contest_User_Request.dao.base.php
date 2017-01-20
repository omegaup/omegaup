<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestUserRequest Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ContestUserRequest }.
 * @access public
 * @abstract
 *
 */
abstract class ContestUserRequestDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contest_User_Request`.`user_id`, `Contest_User_Request`.`contest_id`, `Contest_User_Request`.`request_time`, `Contest_User_Request`.`last_update`, `Contest_User_Request`.`accepted`, `Contest_User_Request`.`extra_note`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestUserRequest} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ContestUserRequest $Contest_User_Request) {
        if (!is_null(self::getByPK($Contest_User_Request->user_id, $Contest_User_Request->contest_id))) {
            return ContestUserRequestDAOBase::update($Contest_User_Request);
        } else {
            return ContestUserRequestDAOBase::create($Contest_User_Request);
        }
    }

    /**
     * Obtener {@link ContestUserRequest} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ContestUserRequest} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ContestUserRequest Un objeto del tipo {@link ContestUserRequest}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id, $contest_id) {
        if (is_null($user_id) || is_null($contest_id)) {
            return null;
        }
        $sql = 'SELECT `Contest_User_Request`.`user_id`, `Contest_User_Request`.`contest_id`, `Contest_User_Request`.`request_time`, `Contest_User_Request`.`last_update`, `Contest_User_Request`.`accepted`, `Contest_User_Request`.`extra_note` FROM Contest_User_Request WHERE (user_id = ? AND contest_id = ?) LIMIT 1;';
        $params = [$user_id, $contest_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ContestUserRequest($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ContestUserRequest}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestUserRequest}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contest_User_Request`.`user_id`, `Contest_User_Request`.`contest_id`, `Contest_User_Request`.`request_time`, `Contest_User_Request`.`last_update`, `Contest_User_Request`.`accepted`, `Contest_User_Request`.`extra_note` from Contest_User_Request';
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
            $allData[] = new ContestUserRequest($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestUserRequest} de la base de datos.
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
      * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contest_User_Request, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contest_User_Request instanceof ContestUserRequest)) {
            $Contest_User_Request = new ContestUserRequest($Contest_User_Request);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contest_User_Request->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Contest_User_Request->user_id;
        }
        if (!is_null($Contest_User_Request->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contest_User_Request->contest_id;
        }
        if (!is_null($Contest_User_Request->request_time)) {
            $clauses[] = '`request_time` = ?';
            $params[] = $Contest_User_Request->request_time;
        }
        if (!is_null($Contest_User_Request->last_update)) {
            $clauses[] = '`last_update` = ?';
            $params[] = $Contest_User_Request->last_update;
        }
        if (!is_null($Contest_User_Request->accepted)) {
            $clauses[] = '`accepted` = ?';
            $params[] = $Contest_User_Request->accepted;
        }
        if (!is_null($Contest_User_Request->extra_note)) {
            $clauses[] = '`extra_note` = ?';
            $params[] = $Contest_User_Request->extra_note;
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
        $sql = 'SELECT `Contest_User_Request`.`user_id`, `Contest_User_Request`.`contest_id`, `Contest_User_Request`.`request_time`, `Contest_User_Request`.`last_update`, `Contest_User_Request`.`accepted`, `Contest_User_Request`.`extra_note` FROM `Contest_User_Request`';
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
            $ar[] = new ContestUserRequest($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a actualizar.
      */
    final private static function update(ContestUserRequest $Contest_User_Request) {
        $sql = 'UPDATE `Contest_User_Request` SET `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ? WHERE `user_id` = ? AND `contest_id` = ?;';
        $params = [
            $Contest_User_Request->request_time,
            $Contest_User_Request->last_update,
            $Contest_User_Request->accepted,
            $Contest_User_Request->extra_note,
            $Contest_User_Request->user_id,$Contest_User_Request->contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestUserRequest suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ContestUserRequest dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a crear.
     */
    final private static function create(ContestUserRequest $Contest_User_Request) {
        if (is_null($Contest_User_Request->request_time)) {
            $Contest_User_Request->request_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Contest_User_Request (`user_id`, `contest_id`, `request_time`, `last_update`, `accepted`, `extra_note`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Contest_User_Request->user_id,
            $Contest_User_Request->contest_id,
            $Contest_User_Request->request_time,
            $Contest_User_Request->last_update,
            $Contest_User_Request->accepted,
            $Contest_User_Request->extra_note,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestUserRequest} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestUserRequest}.
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
     * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
     * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ContestUserRequest $Contest_User_RequestA, ContestUserRequest $Contest_User_RequestB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Contest_User_RequestA->user_id;
        $b = $Contest_User_RequestB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_User_RequestA->contest_id;
        $b = $Contest_User_RequestB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_User_RequestA->request_time;
        $b = $Contest_User_RequestB->request_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`request_time` >= ? AND `request_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`request_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_User_RequestA->last_update;
        $b = $Contest_User_RequestB->last_update;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`last_update` >= ? AND `last_update` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`last_update` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_User_RequestA->accepted;
        $b = $Contest_User_RequestB->accepted;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`accepted` >= ? AND `accepted` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`accepted` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_User_RequestA->extra_note;
        $b = $Contest_User_RequestB->extra_note;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`extra_note` >= ? AND `extra_note` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`extra_note` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contest_User_Request`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestUserRequest($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ContestUserRequest suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ContestUserRequest [$Contest_User_Request] El objeto de tipo ContestUserRequest a eliminar
     */
    final public static function delete(ContestUserRequest $Contest_User_Request) {
        if (is_null(self::getByPK($Contest_User_Request->user_id, $Contest_User_Request->contest_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Contest_User_Request` WHERE user_id = ? AND contest_id = ?;';
        $params = [$Contest_User_Request->user_id, $Contest_User_Request->contest_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
