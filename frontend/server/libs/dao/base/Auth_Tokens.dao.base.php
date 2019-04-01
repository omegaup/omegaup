<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** AuthTokens Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link AuthTokens }.
 * @access public
 * @abstract
 *
 */
abstract class AuthTokensDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link AuthTokens} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(AuthTokens $Auth_Tokens) {
        if (!is_null(self::getByPK($Auth_Tokens->token))) {
            return AuthTokensDAOBase::update($Auth_Tokens);
        } else {
            return AuthTokensDAOBase::create($Auth_Tokens);
        }
    }

    /**
     * Obtener {@link AuthTokens} por llave primaria.
     *
     * Este metodo cargara un objeto {@link AuthTokens} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link AuthTokens Un objeto del tipo {@link AuthTokens}. NULL si no hay tal registro.
     */
    final public static function getByPK($token) {
        if (is_null($token)) {
            return null;
        }
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` FROM Auth_Tokens WHERE (token = ?) LIMIT 1;';
        $params = [$token];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new AuthTokens($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link AuthTokens}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link AuthTokens}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`identity_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` from Auth_Tokens';
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
            $allData[] = new AuthTokens($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a actualizar.
      */
    final private static function update(AuthTokens $Auth_Tokens) {
        $sql = 'UPDATE `Auth_Tokens` SET `user_id` = ?, `identity_id` = ?, `create_time` = ? WHERE `token` = ?;';
        $params = [
            $Auth_Tokens->user_id,
            $Auth_Tokens->identity_id,
            $Auth_Tokens->create_time,
            $Auth_Tokens->token,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto AuthTokens suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto AuthTokens dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a crear.
     */
    final private static function create(AuthTokens $Auth_Tokens) {
        if (is_null($Auth_Tokens->create_time)) {
            $Auth_Tokens->create_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Auth_Tokens (`user_id`, `identity_id`, `token`, `create_time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Auth_Tokens->user_id,
            $Auth_Tokens->identity_id,
            $Auth_Tokens->token,
            $Auth_Tokens->create_time,
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
     * en el objeto AuthTokens suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a eliminar
     */
    final public static function delete(AuthTokens $Auth_Tokens) {
        $sql = 'DELETE FROM `Auth_Tokens` WHERE token = ?;';
        $params = [$Auth_Tokens->token];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
