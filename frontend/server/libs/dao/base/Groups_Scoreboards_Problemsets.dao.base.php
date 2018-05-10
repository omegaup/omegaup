<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsScoreboardsProblemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsScoreboardsProblemsets }.
 * @access public
 * @abstract
 *
 */
abstract class GroupsScoreboardsProblemsetsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsScoreboardsProblemsets} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) {
        if (!is_null(self::getByPK($Groups_Scoreboards_Problemsets->group_scoreboard_id, $Groups_Scoreboards_Problemsets->problemset_id))) {
            return GroupsScoreboardsProblemsetsDAOBase::update($Groups_Scoreboards_Problemsets);
        } else {
            return GroupsScoreboardsProblemsetsDAOBase::create($Groups_Scoreboards_Problemsets);
        }
    }

    /**
     * Obtener {@link GroupsScoreboardsProblemsets} por llave primaria.
     *
     * Este metodo cargara un objeto {@link GroupsScoreboardsProblemsets} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link GroupsScoreboardsProblemsets Un objeto del tipo {@link GroupsScoreboardsProblemsets}. NULL si no hay tal registro.
     */
    final public static function getByPK($group_scoreboard_id, $problemset_id) {
        if (is_null($group_scoreboard_id) || is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight` FROM Groups_Scoreboards_Problemsets WHERE (group_scoreboard_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$group_scoreboard_id, $problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new GroupsScoreboardsProblemsets($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link GroupsScoreboardsProblemsets}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsScoreboardsProblemsets}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight` from Groups_Scoreboards_Problemsets';
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
            $allData[] = new GroupsScoreboardsProblemsets($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboardsProblemsets} de la base de datos.
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
      * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Groups_Scoreboards_Problemsets, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Groups_Scoreboards_Problemsets instanceof GroupsScoreboardsProblemsets)) {
            $Groups_Scoreboards_Problemsets = new GroupsScoreboardsProblemsets($Groups_Scoreboards_Problemsets);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Groups_Scoreboards_Problemsets->group_scoreboard_id)) {
            $clauses[] = '`group_scoreboard_id` = ?';
            $params[] = $Groups_Scoreboards_Problemsets->group_scoreboard_id;
        }
        if (!is_null($Groups_Scoreboards_Problemsets->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Groups_Scoreboards_Problemsets->problemset_id;
        }
        if (!is_null($Groups_Scoreboards_Problemsets->only_ac)) {
            $clauses[] = '`only_ac` = ?';
            $params[] = $Groups_Scoreboards_Problemsets->only_ac;
        }
        if (!is_null($Groups_Scoreboards_Problemsets->weight)) {
            $clauses[] = '`weight` = ?';
            $params[] = $Groups_Scoreboards_Problemsets->weight;
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
        $sql = 'SELECT `Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight` FROM `Groups_Scoreboards_Problemsets`';
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
            $ar[] = new GroupsScoreboardsProblemsets($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a actualizar.
      */
    final private static function update(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) {
        $sql = 'UPDATE `Groups_Scoreboards_Problemsets` SET `only_ac` = ?, `weight` = ? WHERE `group_scoreboard_id` = ? AND `problemset_id` = ?;';
        $params = [
            $Groups_Scoreboards_Problemsets->only_ac,
            $Groups_Scoreboards_Problemsets->weight,
            $Groups_Scoreboards_Problemsets->group_scoreboard_id,$Groups_Scoreboards_Problemsets->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsScoreboardsProblemsets suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto GroupsScoreboardsProblemsets dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a crear.
     */
    final private static function create(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) {
        if (is_null($Groups_Scoreboards_Problemsets->only_ac)) {
            $Groups_Scoreboards_Problemsets->only_ac = '0';
        }
        if (is_null($Groups_Scoreboards_Problemsets->weight)) {
            $Groups_Scoreboards_Problemsets->weight = '1';
        }
        $sql = 'INSERT INTO Groups_Scoreboards_Problemsets (`group_scoreboard_id`, `problemset_id`, `only_ac`, `weight`) VALUES (?, ?, ?, ?);';
        $params = [
            $Groups_Scoreboards_Problemsets->group_scoreboard_id,
            $Groups_Scoreboards_Problemsets->problemset_id,
            $Groups_Scoreboards_Problemsets->only_ac,
            $Groups_Scoreboards_Problemsets->weight,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboardsProblemsets} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link GroupsScoreboardsProblemsets}.
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
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(GroupsScoreboardsProblemsets $Groups_Scoreboards_ProblemsetsA, GroupsScoreboardsProblemsets $Groups_Scoreboards_ProblemsetsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Groups_Scoreboards_ProblemsetsA->group_scoreboard_id;
        $b = $Groups_Scoreboards_ProblemsetsB->group_scoreboard_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`group_scoreboard_id` >= ? AND `group_scoreboard_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`group_scoreboard_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_Scoreboards_ProblemsetsA->problemset_id;
        $b = $Groups_Scoreboards_ProblemsetsB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_Scoreboards_ProblemsetsA->only_ac;
        $b = $Groups_Scoreboards_ProblemsetsB->only_ac;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`only_ac` >= ? AND `only_ac` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`only_ac` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_Scoreboards_ProblemsetsA->weight;
        $b = $Groups_Scoreboards_ProblemsetsB->weight;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`weight` >= ? AND `weight` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`weight` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Groups_Scoreboards_Problemsets`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new GroupsScoreboardsProblemsets($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto GroupsScoreboardsProblemsets suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a eliminar
     */
    final public static function delete(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) {
        if (is_null(self::getByPK($Groups_Scoreboards_Problemsets->group_scoreboard_id, $Groups_Scoreboards_Problemsets->problemset_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Groups_Scoreboards_Problemsets` WHERE group_scoreboard_id = ? AND problemset_id = ?;';
        $params = [$Groups_Scoreboards_Problemsets->group_scoreboard_id, $Groups_Scoreboards_Problemsets->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
