<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Identities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Identities }.
 * @access public
 * @abstract
 *
 */
abstract class IdentitiesDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Identities} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Identities [$Identities] El objeto de tipo Identities
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Identities $Identities) {
        if (!is_null(self::getByPK($Identities->identity_id))) {
            return IdentitiesDAOBase::update($Identities);
        } else {
            return IdentitiesDAOBase::create($Identities);
        }
    }

    /**
     * Obtener {@link Identities} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Identities} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Identities Un objeto del tipo {@link Identities}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id) {
        if (is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` FROM Identities WHERE (identity_id = ?) LIMIT 1;';
        $params = [$identity_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Identities($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Identities}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Identities}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` from Identities';
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
            $allData[] = new Identities($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Identities} de la base de datos.
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
      * @param Identities [$Identities] El objeto de tipo Identities
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Identities, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Identities instanceof Identities)) {
            $Identities = new Identities($Identities);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Identities->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Identities->identity_id;
        }
        if (!is_null($Identities->username)) {
            $clauses[] = '`username` = ?';
            $params[] = $Identities->username;
        }
        if (!is_null($Identities->password)) {
            $clauses[] = '`password` = ?';
            $params[] = $Identities->password;
        }
        if (!is_null($Identities->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $Identities->name;
        }
        if (!is_null($Identities->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Identities->user_id;
        }
        if (!is_null($Identities->language_id)) {
            $clauses[] = '`language_id` = ?';
            $params[] = $Identities->language_id;
        }
        if (!is_null($Identities->country_id)) {
            $clauses[] = '`country_id` = ?';
            $params[] = $Identities->country_id;
        }
        if (!is_null($Identities->state_id)) {
            $clauses[] = '`state_id` = ?';
            $params[] = $Identities->state_id;
        }
        if (!is_null($Identities->school_id)) {
            $clauses[] = '`school_id` = ?';
            $params[] = $Identities->school_id;
        }
        if (!is_null($Identities->gender)) {
            $clauses[] = '`gender` = ?';
            $params[] = $Identities->gender;
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
        $sql = 'SELECT `Identities`.`identity_id`, `Identities`.`username`, `Identities`.`password`, `Identities`.`name`, `Identities`.`user_id`, `Identities`.`language_id`, `Identities`.`country_id`, `Identities`.`state_id`, `Identities`.`school_id`, `Identities`.`gender` FROM `Identities`';
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
            $ar[] = new Identities($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Identities [$Identities] El objeto de tipo Identities a actualizar.
      */
    final private static function update(Identities $Identities) {
        $sql = 'UPDATE `Identities` SET `username` = ?, `password` = ?, `name` = ?, `user_id` = ?, `language_id` = ?, `country_id` = ?, `state_id` = ?, `school_id` = ?, `gender` = ? WHERE `identity_id` = ?;';
        $params = [
            $Identities->username,
            $Identities->password,
            $Identities->name,
            $Identities->user_id,
            $Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            $Identities->school_id,
            $Identities->gender,
            $Identities->identity_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Identities suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Identities dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Identities [$Identities] El objeto de tipo Identities a crear.
     */
    final private static function create(Identities $Identities) {
        $sql = 'INSERT INTO Identities (`identity_id`, `username`, `password`, `name`, `user_id`, `language_id`, `country_id`, `state_id`, `school_id`, `gender`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Identities->identity_id,
            $Identities->username,
            $Identities->password,
            $Identities->name,
            $Identities->user_id,
            $Identities->language_id,
            $Identities->country_id,
            $Identities->state_id,
            $Identities->school_id,
            $Identities->gender,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Identities->identity_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Identities} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Identities}.
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
     * @param Identities [$Identities] El objeto de tipo Identities
     * @param Identities [$Identities] El objeto de tipo Identities
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Identities $IdentitiesA, Identities $IdentitiesB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $IdentitiesA->identity_id;
        $b = $IdentitiesB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->username;
        $b = $IdentitiesB->username;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`username` >= ? AND `username` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`username` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->password;
        $b = $IdentitiesB->password;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`password` >= ? AND `password` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`password` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->name;
        $b = $IdentitiesB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->user_id;
        $b = $IdentitiesB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->language_id;
        $b = $IdentitiesB->language_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`language_id` >= ? AND `language_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`language_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->country_id;
        $b = $IdentitiesB->country_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`country_id` >= ? AND `country_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`country_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->state_id;
        $b = $IdentitiesB->state_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`state_id` >= ? AND `state_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`state_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->school_id;
        $b = $IdentitiesB->school_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`school_id` >= ? AND `school_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`school_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $IdentitiesA->gender;
        $b = $IdentitiesB->gender;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`gender` >= ? AND `gender` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`gender` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Identities`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Identities($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Identities suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Identities [$Identities] El objeto de tipo Identities a eliminar
     */
    final public static function delete(Identities $Identities) {
        if (is_null(self::getByPK($Identities->identity_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Identities` WHERE identity_id = ?;';
        $params = [$Identities->identity_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
