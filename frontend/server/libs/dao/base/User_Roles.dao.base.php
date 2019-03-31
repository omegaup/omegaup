<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRoles Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link UserRoles }.
 * @access public
 * @abstract
 *
 */
abstract class UserRolesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UserRoles} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param UserRoles [$User_Roles] El objeto de tipo UserRoles
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(UserRoles $User_Roles) {
        if (!is_null(self::getByPK($User_Roles->user_id, $User_Roles->role_id, $User_Roles->acl_id))) {
            return UserRolesDAOBase::update($User_Roles);
        } else {
            return UserRolesDAOBase::create($User_Roles);
        }
    }

    /**
     * Obtener {@link UserRoles} por llave primaria.
     *
     * Este metodo cargara un objeto {@link UserRoles} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link UserRoles Un objeto del tipo {@link UserRoles}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id, $role_id, $acl_id) {
        if (is_null($user_id) || is_null($role_id) || is_null($acl_id)) {
            return null;
        }
        $sql = 'SELECT `User_Roles`.`user_id`, `User_Roles`.`role_id`, `User_Roles`.`acl_id` FROM User_Roles WHERE (user_id = ? AND role_id = ? AND acl_id = ?) LIMIT 1;';
        $params = [$user_id, $role_id, $acl_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new UserRoles($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link UserRoles}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UserRoles}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `User_Roles`.`user_id`, `User_Roles`.`role_id`, `User_Roles`.`acl_id` from User_Roles';
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
            $allData[] = new UserRoles($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param UserRoles [$User_Roles] El objeto de tipo UserRoles a actualizar.
      */
    final private static function update(UserRoles $User_Roles) {
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRoles suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto UserRoles dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param UserRoles [$User_Roles] El objeto de tipo UserRoles a crear.
     */
    final private static function create(UserRoles $User_Roles) {
        $sql = 'INSERT INTO User_Roles (`user_id`, `role_id`, `acl_id`) VALUES (?, ?, ?);';
        $params = [
            $User_Roles->user_id,
            $User_Roles->role_id,
            $User_Roles->acl_id,
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
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto UserRoles suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param UserRoles [$User_Roles] El objeto de tipo UserRoles a eliminar
     */
    final public static function delete(UserRoles $User_Roles) {
        $sql = 'DELETE FROM `User_Roles` WHERE user_id = ? AND role_id = ? AND acl_id = ?;';
        $params = [$User_Roles->user_id, $User_Roles->role_id, $User_Roles->acl_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
