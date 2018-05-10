<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentityRequestHistory Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequestHistory }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestHistoryDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequestHistory} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (!is_null(self::getByPK($Problemset_Identity_Request_History->history_id))) {
            return ProblemsetIdentityRequestHistoryDAOBase::update($Problemset_Identity_Request_History);
        } else {
            return ProblemsetIdentityRequestHistoryDAOBase::create($Problemset_Identity_Request_History);
        }
    }

    /**
     * Obtener {@link ProblemsetIdentityRequestHistory} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetIdentityRequestHistory} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentityRequestHistory Un objeto del tipo {@link ProblemsetIdentityRequestHistory}. NULL si no hay tal registro.
     */
    final public static function getByPK($history_id) {
        if (is_null($history_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` FROM Problemset_Identity_Request_History WHERE (history_id = ?) LIMIT 1;';
        $params = [$history_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetIdentityRequestHistory($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetIdentityRequestHistory}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequestHistory}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` from Problemset_Identity_Request_History';
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
            $allData[] = new ProblemsetIdentityRequestHistory($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentityRequestHistory} de la base de datos.
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
      * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problemset_Identity_Request_History, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problemset_Identity_Request_History instanceof ProblemsetIdentityRequestHistory)) {
            $Problemset_Identity_Request_History = new ProblemsetIdentityRequestHistory($Problemset_Identity_Request_History);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problemset_Identity_Request_History->history_id)) {
            $clauses[] = '`history_id` = ?';
            $params[] = $Problemset_Identity_Request_History->history_id;
        }
        if (!is_null($Problemset_Identity_Request_History->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Problemset_Identity_Request_History->identity_id;
        }
        if (!is_null($Problemset_Identity_Request_History->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Problemset_Identity_Request_History->problemset_id;
        }
        if (!is_null($Problemset_Identity_Request_History->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Problemset_Identity_Request_History->time;
        }
        if (!is_null($Problemset_Identity_Request_History->accepted)) {
            $clauses[] = '`accepted` = ?';
            $params[] = $Problemset_Identity_Request_History->accepted;
        }
        if (!is_null($Problemset_Identity_Request_History->admin_id)) {
            $clauses[] = '`admin_id` = ?';
            $params[] = $Problemset_Identity_Request_History->admin_id;
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
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` FROM `Problemset_Identity_Request_History`';
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
            $ar[] = new ProblemsetIdentityRequestHistory($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a actualizar.
      */
    final private static function update(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        $sql = 'UPDATE `Problemset_Identity_Request_History` SET `identity_id` = ?, `problemset_id` = ?, `time` = ?, `accepted` = ?, `admin_id` = ? WHERE `history_id` = ?;';
        $params = [
            $Problemset_Identity_Request_History->identity_id,
            $Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            $Problemset_Identity_Request_History->accepted,
            $Problemset_Identity_Request_History->admin_id,
            $Problemset_Identity_Request_History->history_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequestHistory suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetIdentityRequestHistory dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a crear.
     */
    final private static function create(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (is_null($Problemset_Identity_Request_History->time)) {
            $Problemset_Identity_Request_History->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Identity_Request_History (`history_id`, `identity_id`, `problemset_id`, `time`, `accepted`, `admin_id`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identity_Request_History->history_id,
            $Problemset_Identity_Request_History->identity_id,
            $Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            $Problemset_Identity_Request_History->accepted,
            $Problemset_Identity_Request_History->admin_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problemset_Identity_Request_History->history_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentityRequestHistory} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsetIdentityRequestHistory}.
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
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_HistoryA, ProblemsetIdentityRequestHistory $Problemset_Identity_Request_HistoryB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Problemset_Identity_Request_HistoryA->history_id;
        $b = $Problemset_Identity_Request_HistoryB->history_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`history_id` >= ? AND `history_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`history_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_Request_HistoryA->identity_id;
        $b = $Problemset_Identity_Request_HistoryB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_Request_HistoryA->problemset_id;
        $b = $Problemset_Identity_Request_HistoryB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_Request_HistoryA->time;
        $b = $Problemset_Identity_Request_HistoryB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_Request_HistoryA->accepted;
        $b = $Problemset_Identity_Request_HistoryB->accepted;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`accepted` >= ? AND `accepted` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`accepted` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_Identity_Request_HistoryA->admin_id;
        $b = $Problemset_Identity_Request_HistoryB->admin_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`admin_id` >= ? AND `admin_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`admin_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problemset_Identity_Request_History`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ProblemsetIdentityRequestHistory($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetIdentityRequestHistory suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a eliminar
     */
    final public static function delete(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (is_null(self::getByPK($Problemset_Identity_Request_History->history_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Problemset_Identity_Request_History` WHERE history_id = ?;';
        $params = [$Problemset_Identity_Request_History->history_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
