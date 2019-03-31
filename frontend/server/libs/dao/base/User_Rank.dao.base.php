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
abstract class UserRankDAOBase {
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
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id`, `User_Rank`.`state_id`, `User_Rank`.`school_id` FROM User_Rank WHERE (user_id = ?) LIMIT 1;';
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
        $sql = 'SELECT `User_Rank`.`user_id`, `User_Rank`.`rank`, `User_Rank`.`problems_solved_count`, `User_Rank`.`score`, `User_Rank`.`username`, `User_Rank`.`name`, `User_Rank`.`country_id`, `User_Rank`.`state_id`, `User_Rank`.`school_id` from User_Rank';
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
            $allData[] = new UserRank($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param UserRank [$User_Rank] El objeto de tipo UserRank a actualizar.
      */
    final private static function update(UserRank $User_Rank) {
        $sql = 'UPDATE `User_Rank` SET `rank` = ?, `problems_solved_count` = ?, `score` = ?, `username` = ?, `name` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ? WHERE `user_id` = ?;';
        $params = [
            $User_Rank->rank,
            $User_Rank->problems_solved_count,
            $User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            $User_Rank->school_id,
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
            $User_Rank->problems_solved_count = '0';
        }
        if (is_null($User_Rank->score)) {
            $User_Rank->score = '0';
        }
        $sql = 'INSERT INTO User_Rank (`user_id`, `rank`, `problems_solved_count`, `score`, `username`, `name`, `country_id`, `state_id`, `school_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $User_Rank->user_id,
            $User_Rank->rank,
            $User_Rank->problems_solved_count,
            $User_Rank->score,
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            $User_Rank->school_id,
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
     * en el objeto UserRank suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param UserRank [$User_Rank] El objeto de tipo UserRank a eliminar
     */
    final public static function delete(UserRank $User_Rank) {
        $sql = 'DELETE FROM `User_Rank` WHERE user_id = ?;';
        $params = [$User_Rank->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
