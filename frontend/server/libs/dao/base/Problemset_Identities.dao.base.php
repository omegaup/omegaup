<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentities }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentitiesDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentities} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetIdentities $Problemset_Identities) {
        if (!is_null(self::getByPK($Problemset_Identities->identity_id, $Problemset_Identities->problemset_id))) {
            return ProblemsetIdentitiesDAOBase::update($Problemset_Identities);
        } else {
            return ProblemsetIdentitiesDAOBase::create($Problemset_Identities);
        }
    }

    /**
     * Obtener {@link ProblemsetIdentities} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetIdentities} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentities Un objeto del tipo {@link ProblemsetIdentities}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id, $problemset_id) {
        if (is_null($identity_id) || is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited` FROM Problemset_Identities WHERE (identity_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetIdentities($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetIdentities}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentities}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited` from Problemset_Identities';
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
            $allData[] = new ProblemsetIdentities($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentities} de la base de datos.
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
      * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problemset_Identities, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problemset_Identities instanceof ProblemsetIdentities)) {
            $Problemset_Identities = new ProblemsetIdentities($Problemset_Identities);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problemset_Identities->identity_id)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = $Problemset_Identities->identity_id;
        }
        if (!is_null($Problemset_Identities->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Problemset_Identities->problemset_id;
        }
        if (!is_null($Problemset_Identities->access_time)) {
            $clauses[] = '`access_time` = ?';
            $params[] = $Problemset_Identities->access_time;
        }
        if (!is_null($Problemset_Identities->score)) {
            $clauses[] = '`score` = ?';
            $params[] = $Problemset_Identities->score;
        }
        if (!is_null($Problemset_Identities->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Problemset_Identities->time;
        }
        if (!is_null($Problemset_Identities->share_user_information)) {
            $clauses[] = '`share_user_information` = ?';
            $params[] = $Problemset_Identities->share_user_information;
        }
        if (!is_null($Problemset_Identities->privacystatement_consent_id)) {
            $clauses[] = '`privacystatement_consent_id` = ?';
            $params[] = $Problemset_Identities->privacystatement_consent_id;
        }
        if (!is_null($Problemset_Identities->is_invited)) {
            $clauses[] = '`is_invited` = ?';
            $params[] = $Problemset_Identities->is_invited;
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
        $sql = 'SELECT `Problemset_Identities`.`identity_id`, `Problemset_Identities`.`problemset_id`, `Problemset_Identities`.`access_time`, `Problemset_Identities`.`score`, `Problemset_Identities`.`time`, `Problemset_Identities`.`share_user_information`, `Problemset_Identities`.`privacystatement_consent_id`, `Problemset_Identities`.`is_invited` FROM `Problemset_Identities`';
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
            $ar[] = new ProblemsetIdentities($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a actualizar.
      */
    final private static function update(ProblemsetIdentities $Problemset_Identities) {
        $sql = 'UPDATE `Problemset_Identities` SET `access_time` = ?, `score` = ?, `time` = ?, `share_user_information` = ?, `privacystatement_consent_id` = ?, `is_invited` = ? WHERE `identity_id` = ? AND `problemset_id` = ?;';
        $params = [
            $Problemset_Identities->access_time,
            $Problemset_Identities->score,
            $Problemset_Identities->time,
            $Problemset_Identities->share_user_information,
            $Problemset_Identities->privacystatement_consent_id,
            $Problemset_Identities->is_invited,
            $Problemset_Identities->identity_id,$Problemset_Identities->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentities suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetIdentities dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a crear.
     */
    final private static function create(ProblemsetIdentities $Problemset_Identities) {
        if (is_null($Problemset_Identities->score)) {
            $Problemset_Identities->score = '1';
        }
        if (is_null($Problemset_Identities->time)) {
            $Problemset_Identities->time = '1';
        }
        if (is_null($Problemset_Identities->is_invited)) {
            $Problemset_Identities->is_invited = '0';
        }
        $sql = 'INSERT INTO Problemset_Identities (`identity_id`, `problemset_id`, `access_time`, `score`, `time`, `share_user_information`, `privacystatement_consent_id`, `is_invited`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identities->identity_id,
            $Problemset_Identities->problemset_id,
            $Problemset_Identities->access_time,
            $Problemset_Identities->score,
            $Problemset_Identities->time,
            $Problemset_Identities->share_user_information,
            $Problemset_Identities->privacystatement_consent_id,
            $Problemset_Identities->is_invited,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsetIdentities} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsetIdentities}.
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
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ProblemsetIdentities $Problemset_IdentitiesA, ProblemsetIdentities $Problemset_IdentitiesB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Problemset_IdentitiesA->identity_id;
        $b = $Problemset_IdentitiesB->identity_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`identity_id` >= ? AND `identity_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`identity_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->problemset_id;
        $b = $Problemset_IdentitiesB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->access_time;
        $b = $Problemset_IdentitiesB->access_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`access_time` >= ? AND `access_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`access_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->score;
        $b = $Problemset_IdentitiesB->score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`score` >= ? AND `score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->time;
        $b = $Problemset_IdentitiesB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->share_user_information;
        $b = $Problemset_IdentitiesB->share_user_information;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`share_user_information` >= ? AND `share_user_information` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`share_user_information` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->privacystatement_consent_id;
        $b = $Problemset_IdentitiesB->privacystatement_consent_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`privacystatement_consent_id` >= ? AND `privacystatement_consent_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`privacystatement_consent_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problemset_IdentitiesA->is_invited;
        $b = $Problemset_IdentitiesB->is_invited;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`is_invited` >= ? AND `is_invited` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`is_invited` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problemset_Identities`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ProblemsetIdentities($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetIdentities suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ProblemsetIdentities [$Problemset_Identities] El objeto de tipo ProblemsetIdentities a eliminar
     */
    final public static function delete(ProblemsetIdentities $Problemset_Identities) {
        if (is_null(self::getByPK($Problemset_Identities->identity_id, $Problemset_Identities->problemset_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Problemset_Identities` WHERE identity_id = ? AND problemset_id = ?;';
        $params = [$Problemset_Identities->identity_id, $Problemset_Identities->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
