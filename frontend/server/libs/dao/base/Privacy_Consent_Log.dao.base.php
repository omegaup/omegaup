<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** PrivacyConsentLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link PrivacyConsentLog }.
 * @access public
 * @abstract
 *
 */
abstract class PrivacyConsentLogDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Privacy_Consent_Log`.`identity_id`, `Privacy_Consent_Log`.`privacystatement_id`, `Privacy_Consent_Log`.`timestamp`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link PrivacyConsentLog} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(PrivacyConsentLog $Privacy_Consent_Log) {
        if (!is_null(self::getByPK($Privacy_Consent_Log->identity_id, $Privacy_Consent_Log->privacystatement_id))) {
            return PrivacyConsentLogDAOBase::update($Privacy_Consent_Log);
        } else {
            return PrivacyConsentLogDAOBase::create($Privacy_Consent_Log);
        }
    }

    /**
     * Obtener {@link PrivacyConsentLog} por llave primaria.
     *
     * Este metodo cargara un objeto {@link PrivacyConsentLog} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link PrivacyConsentLog Un objeto del tipo {@link PrivacyConsentLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id, $privacystatement_id) {
        if (is_null($identity_id) || is_null($privacystatement_id)) {
            return null;
        }
        $sql = 'SELECT `Privacy_Consent_Log`.`identity_id`, `Privacy_Consent_Log`.`privacystatement_id`, `Privacy_Consent_Log`.`timestamp` FROM Privacy_Consent_Log WHERE (identity_id = ? AND privacystatement_id = ?) LIMIT 1;';
        $params = [$identity_id, $privacystatement_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new PrivacyConsentLog($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link PrivacyConsentLog}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link PrivacyConsentLog}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Privacy_Consent_Log`.`identity_id`, `Privacy_Consent_Log`.`privacystatement_id`, `Privacy_Consent_Log`.`timestamp` from Privacy_Consent_Log';
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
            $allData[] = new PrivacyConsentLog($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PrivacyConsentLog} de la base de datos.
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
      * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Privacy_Consent_Log, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Privacy_Consent_Log instanceof PrivacyConsentLog)) {
            $Privacy_Consent_Log = new PrivacyConsentLog($Privacy_Consent_Log);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Privacy_Consent_Log->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Privacy_Consent_Log->identity_id;
        }
        if (!is_null($Privacy_Consent_Log->privacystatement_id)) {
            $clauses[] = '`privacystatement_id` = ?';
            $params[] = $Privacy_Consent_Log->privacystatement_id;
        }
        if (!is_null($Privacy_Consent_Log->timestamp)) {
            $clauses[] = '`timestamp` = ?';
            $params[] = $Privacy_Consent_Log->timestamp;
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
        $sql = 'SELECT `Privacy_Consent_Log`.`identity_id`, `Privacy_Consent_Log`.`privacystatement_id`, `Privacy_Consent_Log`.`timestamp` FROM `Privacy_Consent_Log`';
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
            $ar[] = new PrivacyConsentLog($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog a actualizar.
      */
    final private static function update(PrivacyConsentLog $Privacy_Consent_Log) {
        $sql = 'UPDATE `Privacy_Consent_Log` SET `timestamp` = ? WHERE `identity_id` = ? AND `privacystatement_id` = ?;';
        $params = [
            $Privacy_Consent_Log->timestamp,
            $Privacy_Consent_Log->identity_id,$Privacy_Consent_Log->privacystatement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto PrivacyConsentLog suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto PrivacyConsentLog dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog a crear.
     */
    final private static function create(PrivacyConsentLog $Privacy_Consent_Log) {
        if (is_null($Privacy_Consent_Log->timestamp)) {
            $Privacy_Consent_Log->timestamp = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Privacy_Consent_Log (`identity_id`, `privacystatement_id`, `timestamp`) VALUES (?, ?, ?);';
        $params = [
            $Privacy_Consent_Log->identity_id,
            $Privacy_Consent_Log->privacystatement_id,
            $Privacy_Consent_Log->timestamp,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PrivacyConsentLog} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link PrivacyConsentLog}.
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
     * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog
     * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(PrivacyConsentLog $Privacy_Consent_LogA, PrivacyConsentLog $Privacy_Consent_LogB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Privacy_Consent_LogA->identity_id;
        $b = $Privacy_Consent_LogB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Privacy_Consent_LogA->privacystatement_id;
        $b = $Privacy_Consent_LogB->privacystatement_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`privacystatement_id` >= ? AND `privacystatement_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`privacystatement_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Privacy_Consent_LogA->timestamp;
        $b = $Privacy_Consent_LogB->timestamp;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`timestamp` >= ? AND `timestamp` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`timestamp` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Privacy_Consent_Log`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new PrivacyConsentLog($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto PrivacyConsentLog suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param PrivacyConsentLog [$Privacy_Consent_Log] El objeto de tipo PrivacyConsentLog a eliminar
     */
    final public static function delete(PrivacyConsentLog $Privacy_Consent_Log) {
        if (is_null(self::getByPK($Privacy_Consent_Log->identity_id, $Privacy_Consent_Log->privacystatement_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Privacy_Consent_Log` WHERE identity_id = ? AND privacystatement_id = ?;';
        $params = [$Privacy_Consent_Log->identity_id, $Privacy_Consent_Log->privacystatement_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
