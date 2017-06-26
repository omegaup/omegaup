<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsUsers Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsUsers }.
 * @access public
 * @abstract
 *
 */
abstract class GroupsUsersDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Groups_Users`.`group_id`, `Groups_Users`.`user_id`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsUsers} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(GroupsUsers $Groups_Users) {
        if (!is_null(self::getByPK($Groups_Users->group_id, $Groups_Users->user_id))) {
            return GroupsUsersDAOBase::update($Groups_Users);
        } else {
            return GroupsUsersDAOBase::create($Groups_Users);
        }
    }

    /**
     * Obtener {@link GroupsUsers} por llave primaria.
     *
     * Este metodo cargara un objeto {@link GroupsUsers} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link GroupsUsers Un objeto del tipo {@link GroupsUsers}. NULL si no hay tal registro.
     */
    final public static function getByPK($group_id, $user_id) {
        if (is_null($group_id) || is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `Groups_Users`.`group_id`, `Groups_Users`.`user_id` FROM Groups_Users WHERE (group_id = ? AND user_id = ?) LIMIT 1;';
        $params = [$group_id, $user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new GroupsUsers($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link GroupsUsers}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsUsers}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Groups_Users`.`group_id`, `Groups_Users`.`user_id` from Groups_Users';
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
            $allData[] = new GroupsUsers($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsUsers} de la base de datos.
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
      * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Groups_Users, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Groups_Users instanceof GroupsUsers)) {
            $Groups_Users = new GroupsUsers($Groups_Users);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Groups_Users->group_id)) {
            $clauses[] = '`group_id` = ?';
            $params[] = $Groups_Users->group_id;
        }
        if (!is_null($Groups_Users->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Groups_Users->user_id;
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
        $sql = 'SELECT `Groups_Users`.`group_id`, `Groups_Users`.`user_id` FROM `Groups_Users`';
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
            $ar[] = new GroupsUsers($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers a actualizar.
      */
    final private static function update(GroupsUsers $Groups_Users) {
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsUsers suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto GroupsUsers dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers a crear.
     */
    final private static function create(GroupsUsers $Groups_Users) {
        $sql = 'INSERT INTO Groups_Users (`group_id`, `user_id`) VALUES (?, ?);';
        $params = [
            $Groups_Users->group_id,
            $Groups_Users->user_id,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsUsers} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link GroupsUsers}.
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
     * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers
     * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(GroupsUsers $Groups_UsersA, GroupsUsers $Groups_UsersB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Groups_UsersA->group_id;
        $b = $Groups_UsersB->group_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`group_id` >= ? AND `group_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`group_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_UsersA->user_id;
        $b = $Groups_UsersB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Groups_Users`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new GroupsUsers($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto GroupsUsers suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param GroupsUsers [$Groups_Users] El objeto de tipo GroupsUsers a eliminar
     */
    final public static function delete(GroupsUsers $Groups_Users) {
        if (is_null(self::getByPK($Groups_Users->group_id, $Groups_Users->user_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Groups_Users` WHERE group_id = ? AND user_id = ?;';
        $params = [$Groups_Users->group_id, $Groups_Users->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
