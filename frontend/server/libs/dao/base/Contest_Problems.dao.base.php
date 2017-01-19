<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestProblems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ContestProblems }.
 * @access public
 * @abstract
 *
 */
abstract class ContestProblemsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Contest_Problems`.`contest_id`, `Contest_Problems`.`problem_id`, `Contest_Problems`.`points`, `Contest_Problems`.`order`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestProblems} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ContestProblems $Contest_Problems) {
        if (!is_null(self::getByPK($Contest_Problems->contest_id, $Contest_Problems->problem_id))) {
            return ContestProblemsDAOBase::update($Contest_Problems);
        } else {
            return ContestProblemsDAOBase::create($Contest_Problems);
        }
    }

    /**
     * Obtener {@link ContestProblems} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ContestProblems} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ContestProblems Un objeto del tipo {@link ContestProblems}. NULL si no hay tal registro.
     */
    final public static function getByPK($contest_id, $problem_id) {
        if (is_null($contest_id) || is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Contest_Problems`.`contest_id`, `Contest_Problems`.`problem_id`, `Contest_Problems`.`points`, `Contest_Problems`.`order` FROM Contest_Problems WHERE (contest_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$contest_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ContestProblems($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ContestProblems}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestProblems}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contest_Problems`.`contest_id`, `Contest_Problems`.`problem_id`, `Contest_Problems`.`points`, `Contest_Problems`.`order` from Contest_Problems';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysql_real_escape_string($orden) . '` ' . mysql_real_escape_string($tipo_de_orden);
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        global $conn;
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new ContestProblems($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestProblems} de la base de datos.
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
      * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Contest_Problems, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Contest_Problems instanceof ContestProblems)) {
            $Contest_Problems = new ContestProblems($Contest_Problems);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Contest_Problems->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Contest_Problems->contest_id;
        }
        if (!is_null($Contest_Problems->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $Contest_Problems->problem_id;
        }
        if (!is_null($Contest_Problems->points)) {
            $clauses[] = '`points` = ?';
            $params[] = $Contest_Problems->points;
        }
        if (!is_null($Contest_Problems->order)) {
            $clauses[] = '`order` = ?';
            $params[] = $Contest_Problems->order;
        }
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysql_real_escape_string($value);
                $clauses[] = "`{$column}` LIKE '%{$escapedValue}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `Contest_Problems`.`contest_id`, `Contest_Problems`.`problem_id`, `Contest_Problems`.`points`, `Contest_Problems`.`order` FROM `Contest_Problems`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . mysql_real_escape_string($orderBy) . '` ' . mysql_real_escape_string($orden);
        }
        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT '. (int)$offset . ', ' . (int)$rowcount;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestProblems($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems a actualizar.
      */
    final private static function update(ContestProblems $Contest_Problems) {
        $sql = 'UPDATE `Contest_Problems` SET `points` = ?, `order` = ? WHERE `contest_id` = ? AND `problem_id` = ?;';
        $params = [
            $Contest_Problems->points,
            $Contest_Problems->order,
            $Contest_Problems->contest_id,$Contest_Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestProblems suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ContestProblems dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems a crear.
     */
    final private static function create(ContestProblems $Contest_Problems) {
        if (is_null($Contest_Problems->points)) {
            $Contest_Problems->points = '1';
        }
        if (is_null($Contest_Problems->order)) {
            $Contest_Problems->order =  '1';
        }
        $sql = 'INSERT INTO Contest_Problems (`contest_id`, `problem_id`, `points`, `order`) VALUES (?, ?, ?, ?);';
        $params = [
            $Contest_Problems->contest_id,
            $Contest_Problems->problem_id,
            $Contest_Problems->points,
            $Contest_Problems->order,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ContestProblems} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ContestProblems}.
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
     * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems
     * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ContestProblems $Contest_ProblemsA, ContestProblems $Contest_ProblemsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Contest_ProblemsA->contest_id;
        $b = $Contest_ProblemsB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_ProblemsA->problem_id;
        $b = $Contest_ProblemsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_ProblemsA->points;
        $b = $Contest_ProblemsB->points;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`points` >= ? AND `points` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`points` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Contest_ProblemsA->order;
        $b = $Contest_ProblemsB->order;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`order` >= ? AND `order` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`order` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Contest_Problems`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ContestProblems($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ContestProblems suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ContestProblems [$Contest_Problems] El objeto de tipo ContestProblems a eliminar
     */
    final public static function delete(ContestProblems $Contest_Problems) {
        if (is_null(self::getByPK($Contest_Problems->contest_id, $Contest_Problems->problem_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Contest_Problems` WHERE contest_id = ? AND problem_id = ?;';
        $params = [$Contest_Problems->contest_id, $Contest_Problems->problem_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
