<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsIdentities }.
 * @access public
 * @abstract
 *
 */
abstract class GroupsIdentitiesDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsIdentities} pasado en la base de datos.
     * save() siempre creara una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(GroupsIdentities $Groups_Identities) {
        return GroupsIdentitiesDAOBase::create($Groups_Identities);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link GroupsIdentities}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsIdentities}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information` from Groups_Identities';
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
            $allData[] = new GroupsIdentities($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsIdentities} de la base de datos.
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
      * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Groups_Identities, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Groups_Identities instanceof GroupsIdentities)) {
            $Groups_Identities = new GroupsIdentities($Groups_Identities);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Groups_Identities->group_id)) {
            $clauses[] = '`group_id` = ?';
            $params[] = $Groups_Identities->group_id;
        }
        if (!is_null($Groups_Identities->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Groups_Identities->identity_id;
        }
        if (!is_null($Groups_Identities->share_user_information)) {
            $clauses[] = '`share_user_information` = ?';
            $params[] = $Groups_Identities->share_user_information;
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
        $sql = 'SELECT `Groups_Identities`.`group_id`, `Groups_Identities`.`identity_id`, `Groups_Identities`.`share_user_information` FROM `Groups_Identities`';
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
            $ar[] = new GroupsIdentities($row);
        }
        return $ar;
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsIdentities suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto GroupsIdentities dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities a crear.
     */
    final private static function create(GroupsIdentities $Groups_Identities) {
        $sql = 'INSERT INTO Groups_Identities (`group_id`, `identity_id`, `share_user_information`) VALUES (?, ?, ?);';
        $params = [
            $Groups_Identities->group_id,
            $Groups_Identities->identity_id,
            $Groups_Identities->share_user_information,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsIdentities} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link GroupsIdentities}.
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
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities
     * @param GroupsIdentities [$Groups_Identities] El objeto de tipo GroupsIdentities
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(GroupsIdentities $Groups_IdentitiesA, GroupsIdentities $Groups_IdentitiesB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Groups_IdentitiesA->group_id;
        $b = $Groups_IdentitiesB->group_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`group_id` >= ? AND `group_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`group_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_IdentitiesA->identity_id;
        $b = $Groups_IdentitiesB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_IdentitiesA->share_user_information;
        $b = $Groups_IdentitiesB->share_user_information;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`share_user_information` >= ? AND `share_user_information` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`share_user_information` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Groups_Identities`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new GroupsIdentities($row);
        }
        return $ar;
    }
}
