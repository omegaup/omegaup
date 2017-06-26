<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsScoreboards Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link GroupsScoreboards }.
 * @access public
 * @abstract
 *
 */
abstract class GroupsScoreboardsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsScoreboards} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(GroupsScoreboards $Groups_Scoreboards) {
        if (!is_null(self::getByPK($Groups_Scoreboards->group_scoreboard_id))) {
            return GroupsScoreboardsDAOBase::update($Groups_Scoreboards);
        } else {
            return GroupsScoreboardsDAOBase::create($Groups_Scoreboards);
        }
    }

    /**
     * Obtener {@link GroupsScoreboards} por llave primaria.
     *
     * Este metodo cargara un objeto {@link GroupsScoreboards} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link GroupsScoreboards Un objeto del tipo {@link GroupsScoreboards}. NULL si no hay tal registro.
     */
    final public static function getByPK($group_scoreboard_id) {
        if (is_null($group_scoreboard_id)) {
            return null;
        }
        $sql = 'SELECT `Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description` FROM Groups_Scoreboards WHERE (group_scoreboard_id = ?) LIMIT 1;';
        $params = [$group_scoreboard_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new GroupsScoreboards($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link GroupsScoreboards}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsScoreboards}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description` from Groups_Scoreboards';
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
            $allData[] = new GroupsScoreboards($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboards} de la base de datos.
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
      * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Groups_Scoreboards, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Groups_Scoreboards instanceof GroupsScoreboards)) {
            $Groups_Scoreboards = new GroupsScoreboards($Groups_Scoreboards);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Groups_Scoreboards->group_scoreboard_id)) {
            $clauses[] = '`group_scoreboard_id` = ?';
            $params[] = $Groups_Scoreboards->group_scoreboard_id;
        }
        if (!is_null($Groups_Scoreboards->group_id)) {
            $clauses[] = '`group_id` = ?';
            $params[] = $Groups_Scoreboards->group_id;
        }
        if (!is_null($Groups_Scoreboards->create_time)) {
            $clauses[] = '`create_time` = ?';
            $params[] = $Groups_Scoreboards->create_time;
        }
        if (!is_null($Groups_Scoreboards->alias)) {
            $clauses[] = '`alias` = ?';
            $params[] = $Groups_Scoreboards->alias;
        }
        if (!is_null($Groups_Scoreboards->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $Groups_Scoreboards->name;
        }
        if (!is_null($Groups_Scoreboards->description)) {
            $clauses[] = '`description` = ?';
            $params[] = $Groups_Scoreboards->description;
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
        $sql = 'SELECT `Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description` FROM `Groups_Scoreboards`';
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
            $ar[] = new GroupsScoreboards($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a actualizar.
      */
    final private static function update(GroupsScoreboards $Groups_Scoreboards) {
        $sql = 'UPDATE `Groups_Scoreboards` SET `group_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE `group_scoreboard_id` = ?;';
        $params = [
            $Groups_Scoreboards->group_id,
            $Groups_Scoreboards->create_time,
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
            $Groups_Scoreboards->group_scoreboard_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsScoreboards suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto GroupsScoreboards dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a crear.
     */
    final private static function create(GroupsScoreboards $Groups_Scoreboards) {
        if (is_null($Groups_Scoreboards->create_time)) {
            $Groups_Scoreboards->create_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Groups_Scoreboards (`group_scoreboard_id`, `group_id`, `create_time`, `alias`, `name`, `description`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Groups_Scoreboards->group_scoreboard_id,
            $Groups_Scoreboards->group_id,
            $Groups_Scoreboards->create_time,
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Groups_Scoreboards->group_scoreboard_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link GroupsScoreboards} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link GroupsScoreboards}.
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
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(GroupsScoreboards $Groups_ScoreboardsA, GroupsScoreboards $Groups_ScoreboardsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Groups_ScoreboardsA->group_scoreboard_id;
        $b = $Groups_ScoreboardsB->group_scoreboard_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`group_scoreboard_id` >= ? AND `group_scoreboard_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`group_scoreboard_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_ScoreboardsA->group_id;
        $b = $Groups_ScoreboardsB->group_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`group_id` >= ? AND `group_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`group_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_ScoreboardsA->create_time;
        $b = $Groups_ScoreboardsB->create_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`create_time` >= ? AND `create_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`create_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_ScoreboardsA->alias;
        $b = $Groups_ScoreboardsB->alias;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`alias` >= ? AND `alias` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`alias` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_ScoreboardsA->name;
        $b = $Groups_ScoreboardsB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Groups_ScoreboardsA->description;
        $b = $Groups_ScoreboardsB->description;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`description` >= ? AND `description` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`description` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Groups_Scoreboards`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new GroupsScoreboards($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto GroupsScoreboards suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a eliminar
     */
    final public static function delete(GroupsScoreboards $Groups_Scoreboards) {
        if (is_null(self::getByPK($Groups_Scoreboards->group_scoreboard_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Groups_Scoreboards` WHERE group_scoreboard_id = ?;';
        $params = [$Groups_Scoreboards->group_scoreboard_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
