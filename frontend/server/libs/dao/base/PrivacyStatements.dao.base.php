<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** PrivacyStatements Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link PrivacyStatements }.
 * @access public
 * @abstract
 *
 */
abstract class PrivacyStatementsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link PrivacyStatements} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(PrivacyStatements $PrivacyStatements) {
        if (!is_null(self::getByPK($PrivacyStatements->privacystatement_id))) {
            return PrivacyStatementsDAOBase::update($PrivacyStatements);
        } else {
            return PrivacyStatementsDAOBase::create($PrivacyStatements);
        }
    }

    /**
     * Obtener {@link PrivacyStatements} por llave primaria.
     *
     * Este metodo cargara un objeto {@link PrivacyStatements} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link PrivacyStatements Un objeto del tipo {@link PrivacyStatements}. NULL si no hay tal registro.
     */
    final public static function getByPK($privacystatement_id) {
        if (is_null($privacystatement_id)) {
            return null;
        }
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` FROM PrivacyStatements WHERE (privacystatement_id = ?) LIMIT 1;';
        $params = [$privacystatement_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new PrivacyStatements($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link PrivacyStatements}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link PrivacyStatements}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` from PrivacyStatements';
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
            $allData[] = new PrivacyStatements($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PrivacyStatements} de la base de datos.
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
      * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($PrivacyStatements, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($PrivacyStatements instanceof PrivacyStatements)) {
            $PrivacyStatements = new PrivacyStatements($PrivacyStatements);
        }

        $clauses = [];
        $params = [];
        if (!is_null($PrivacyStatements->privacystatement_id)) {
            $clauses[] = '`privacystatement_id` = ?';
            $params[] = $PrivacyStatements->privacystatement_id;
        }
        if (!is_null($PrivacyStatements->git_object_id)) {
            $clauses[] = '`git_object_id` = ?';
            $params[] = $PrivacyStatements->git_object_id;
        }
        if (!is_null($PrivacyStatements->type)) {
            $clauses[] = '`type` = ?';
            $params[] = $PrivacyStatements->type;
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
        $sql = 'SELECT `PrivacyStatements`.`privacystatement_id`, `PrivacyStatements`.`git_object_id`, `PrivacyStatements`.`type` FROM `PrivacyStatements`';
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
            $ar[] = new PrivacyStatements($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a actualizar.
      */
    final private static function update(PrivacyStatements $PrivacyStatements) {
        $sql = 'UPDATE `PrivacyStatements` SET `git_object_id` = ?, `type` = ? WHERE `privacystatement_id` = ?;';
        $params = [
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
            $PrivacyStatements->privacystatement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto PrivacyStatements suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto PrivacyStatements dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a crear.
     */
    final private static function create(PrivacyStatements $PrivacyStatements) {
        if (is_null($PrivacyStatements->type)) {
            $PrivacyStatements->type = 'privacy_policy';
        }
        $sql = 'INSERT INTO PrivacyStatements (`privacystatement_id`, `git_object_id`, `type`) VALUES (?, ?, ?);';
        $params = [
            $PrivacyStatements->privacystatement_id,
            $PrivacyStatements->git_object_id,
            $PrivacyStatements->type,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $PrivacyStatements->privacystatement_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link PrivacyStatements} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link PrivacyStatements}.
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
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(PrivacyStatements $PrivacyStatementsA, PrivacyStatements $PrivacyStatementsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $PrivacyStatementsA->privacystatement_id;
        $b = $PrivacyStatementsB->privacystatement_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`privacystatement_id` >= ? AND `privacystatement_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`privacystatement_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $PrivacyStatementsA->git_object_id;
        $b = $PrivacyStatementsB->git_object_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`git_object_id` >= ? AND `git_object_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`git_object_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $PrivacyStatementsA->type;
        $b = $PrivacyStatementsB->type;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`type` >= ? AND `type` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`type` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `PrivacyStatements`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new PrivacyStatements($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto PrivacyStatements suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param PrivacyStatements [$PrivacyStatements] El objeto de tipo PrivacyStatements a eliminar
     */
    final public static function delete(PrivacyStatements $PrivacyStatements) {
        if (is_null(self::getByPK($PrivacyStatements->privacystatement_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `PrivacyStatements` WHERE privacystatement_id = ?;';
        $params = [$PrivacyStatements->privacystatement_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
