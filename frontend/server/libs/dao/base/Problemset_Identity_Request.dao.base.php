<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentityRequest Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequest }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequest} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        if (!is_null(self::getByPK($Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id))) {
            return ProblemsetIdentityRequestDAOBase::update($Problemset_Identity_Request);
        } else {
            return ProblemsetIdentityRequestDAOBase::create($Problemset_Identity_Request);
        }
    }

    /**
     * Obtener {@link ProblemsetIdentityRequest} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetIdentityRequest} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentityRequest Un objeto del tipo {@link ProblemsetIdentityRequest}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id, $problemset_id) {
        if (is_null($identity_id) || is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` FROM Problemset_Identity_Request WHERE (identity_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetIdentityRequest($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetIdentityRequest}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequest}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` from Problemset_Identity_Request';
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
            $allData[] = new ProblemsetIdentityRequest($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentityRequest} de la base de datos.
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
      * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problemset_Identity_Request, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problemset_Identity_Request instanceof ProblemsetIdentityRequest)) {
            $Problemset_Identity_Request = new ProblemsetIdentityRequest($Problemset_Identity_Request);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problemset_Identity_Request->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Problemset_Identity_Request->identity_id;
        }
        if (!is_null($Problemset_Identity_Request->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Problemset_Identity_Request->problemset_id;
        }
        if (!is_null($Problemset_Identity_Request->request_time)) {
            $clauses[] = '`request_time` = ?';
            $params[] = $Problemset_Identity_Request->request_time;
        }
        if (!is_null($Problemset_Identity_Request->last_update)) {
            $clauses[] = '`last_update` = ?';
            $params[] = $Problemset_Identity_Request->last_update;
        }
        if (!is_null($Problemset_Identity_Request->accepted)) {
            $clauses[] = '`accepted` = ?';
            $params[] = $Problemset_Identity_Request->accepted;
        }
        if (!is_null($Problemset_Identity_Request->extra_note)) {
            $clauses[] = '`extra_note` = ?';
            $params[] = $Problemset_Identity_Request->extra_note;
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
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` FROM `Problemset_Identity_Request`';
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
            $ar[] = new ProblemsetIdentityRequest($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a actualizar.
      */
    final private static function update(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        $sql = 'UPDATE `Problemset_Identity_Request` SET `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ? WHERE `identity_id` = ? AND `problemset_id` = ?;';
        $params = [
            $Problemset_Identity_Request->request_time,
            $Problemset_Identity_Request->last_update,
            $Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
            $Problemset_Identity_Request->identity_id,
            $Problemset_Identity_Request->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequest suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetIdentityRequest dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a crear.
     */
    final private static function create(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        if (is_null($Problemset_Identity_Request->request_time)) {
            $Problemset_Identity_Request->request_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Identity_Request (`identity_id`, `problemset_id`, `request_time`, `last_update`, `accepted`, `extra_note`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identity_Request->identity_id,
            $Problemset_Identity_Request->problemset_id,
            $Problemset_Identity_Request->request_time,
            $Problemset_Identity_Request->last_update,
            $Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentityRequest} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsetIdentityRequest}.
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
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ProblemsetIdentityRequest $Problemset_Identity_RequestA, ProblemsetIdentityRequest $Problemset_Identity_RequestB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Problemset_Identity_RequestA->identity_id;
        $b = $Problemset_Identity_RequestB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_RequestA->problemset_id;
        $b = $Problemset_Identity_RequestB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_RequestA->request_time;
        $b = $Problemset_Identity_RequestB->request_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`request_time` >= ? AND `request_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`request_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_RequestA->last_update;
        $b = $Problemset_Identity_RequestB->last_update;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`last_update` >= ? AND `last_update` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`last_update` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_RequestA->accepted;
        $b = $Problemset_Identity_RequestB->accepted;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`accepted` >= ? AND `accepted` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`accepted` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_RequestA->extra_note;
        $b = $Problemset_Identity_RequestB->extra_note;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`extra_note` >= ? AND `extra_note` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`extra_note` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problemset_Identity_Request`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ProblemsetIdentityRequest($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetIdentityRequest suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a eliminar
     */
    final public static function delete(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        if (is_null(self::getByPK($Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Problemset_Identity_Request` WHERE identity_id = ? AND problemset_id = ?;';
        $params = [$Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
