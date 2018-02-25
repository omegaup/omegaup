<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRankCutoffs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link UserRankCutoffs }.
 * @access public
 * @abstract
 *
 */
abstract class UserRankCutoffsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`User_Rank_Cutoffs`.`score`, `User_Rank_Cutoffs`.`percentile`, `User_Rank_Cutoffs`.`classname`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UserRankCutoffs} pasado en la base de datos.
     * save() siempre creara una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(UserRankCutoffs $User_Rank_Cutoffs) {
        return UserRankCutoffsDAOBase::create($User_Rank_Cutoffs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link UserRankCutoffs}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UserRankCutoffs}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `User_Rank_Cutoffs`.`score`, `User_Rank_Cutoffs`.`percentile`, `User_Rank_Cutoffs`.`classname` from User_Rank_Cutoffs';
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
            $allData[] = new UserRankCutoffs($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UserRankCutoffs} de la base de datos.
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
      * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($User_Rank_Cutoffs, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($User_Rank_Cutoffs instanceof UserRankCutoffs)) {
            $User_Rank_Cutoffs = new UserRankCutoffs($User_Rank_Cutoffs);
        }

        $clauses = [];
        $params = [];
        if (!is_null($User_Rank_Cutoffs->score)) {
            $clauses[] = '`score` = ?';
            $params[] = $User_Rank_Cutoffs->score;
        }
        if (!is_null($User_Rank_Cutoffs->percentile)) {
            $clauses[] = '`percentile` = ?';
            $params[] = $User_Rank_Cutoffs->percentile;
        }
        if (!is_null($User_Rank_Cutoffs->classname)) {
            $clauses[] = '`classname` = ?';
            $params[] = $User_Rank_Cutoffs->classname;
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
        $sql = 'SELECT `User_Rank_Cutoffs`.`score`, `User_Rank_Cutoffs`.`percentile`, `User_Rank_Cutoffs`.`classname` FROM `User_Rank_Cutoffs`';
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
            $ar[] = new UserRankCutoffs($row);
        }
        return $ar;
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRankCutoffs suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto UserRankCutoffs dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs a crear.
     */
    final private static function create(UserRankCutoffs $User_Rank_Cutoffs) {
        $sql = 'INSERT INTO User_Rank_Cutoffs (`score`, `percentile`, `classname`) VALUES (?, ?, ?);';
        $params = [
            $User_Rank_Cutoffs->score,
            $User_Rank_Cutoffs->percentile,
            $User_Rank_Cutoffs->classname,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link UserRankCutoffs} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link UserRankCutoffs}.
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
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(UserRankCutoffs $User_Rank_CutoffsA, UserRankCutoffs $User_Rank_CutoffsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $User_Rank_CutoffsA->score;
        $b = $User_Rank_CutoffsB->score;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`score` >= ? AND `score` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`score` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_Rank_CutoffsA->percentile;
        $b = $User_Rank_CutoffsB->percentile;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`percentile` >= ? AND `percentile` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`percentile` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $User_Rank_CutoffsA->classname;
        $b = $User_Rank_CutoffsB->classname;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`classname` >= ? AND `classname` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`classname` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `User_Rank_Cutoffs`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new UserRankCutoffs($row);
        }
        return $ar;
    }
}
