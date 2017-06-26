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
abstract class AuthTokensDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Auth_Tokens`.`user_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time`';

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
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` FROM Auth_Tokens WHERE (token = ?) LIMIT 1;';
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
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` from Auth_Tokens';
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
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link AuthTokens} de la base de datos.
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
      * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Auth_Tokens, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Auth_Tokens instanceof AuthTokens)) {
            $Auth_Tokens = new AuthTokens($Auth_Tokens);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Auth_Tokens->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Auth_Tokens->user_id;
        }
        if (!is_null($Auth_Tokens->token)) {
            $clauses[] = '`token` = ?';
            $params[] = $Auth_Tokens->token;
        }
        if (!is_null($Auth_Tokens->create_time)) {
            $clauses[] = '`create_time` = ?';
            $params[] = $Auth_Tokens->create_time;
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
        $sql = 'SELECT `Auth_Tokens`.`user_id`, `Auth_Tokens`.`token`, `Auth_Tokens`.`create_time` FROM `Auth_Tokens`';
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
            $ar[] = new AuthTokens($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a actualizar.
      */
    final private static function update(AuthTokens $Auth_Tokens) {
        $sql = 'UPDATE `Auth_Tokens` SET `user_id` = ?, `create_time` = ? WHERE `token` = ?;';
        $params = [
            $Auth_Tokens->user_id,
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
        $sql = 'INSERT INTO Auth_Tokens (`user_id`, `token`, `create_time`) VALUES (?, ?, ?);';
        $params = [
            $Auth_Tokens->user_id,
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
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link AuthTokens} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link AuthTokens}.
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
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(AuthTokens $Auth_TokensA, AuthTokens $Auth_TokensB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Auth_TokensA->user_id;
        $b = $Auth_TokensB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Auth_TokensA->token;
        $b = $Auth_TokensB->token;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`token` >= ? AND `token` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`token` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Auth_TokensA->create_time;
        $b = $Auth_TokensB->create_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`create_time` >= ? AND `create_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`create_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Auth_Tokens`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new AuthTokens($row);
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
     * @return int El numero de filas afectadas.
     * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a eliminar
     */
    final public static function delete(AuthTokens $Auth_Tokens) {
        if (is_null(self::getByPK($Auth_Tokens->token))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Auth_Tokens` WHERE token = ?;';
        $params = [$Auth_Tokens->token];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
