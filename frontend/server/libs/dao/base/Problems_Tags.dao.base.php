<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsTags Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsTags }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsTagsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problems_Tags`.`problem_id`, `Problems_Tags`.`tag_id`, `Problems_Tags`.`public`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsTags} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsTags $Problems_Tags) {
        if (!is_null(self::getByPK($Problems_Tags->problem_id, $Problems_Tags->tag_id))) {
            return ProblemsTagsDAOBase::update($Problems_Tags);
        } else {
            return ProblemsTagsDAOBase::create($Problems_Tags);
        }
    }

    /**
     * Obtener {@link ProblemsTags} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsTags} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsTags Un objeto del tipo {@link ProblemsTags}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id, $tag_id) {
        if (is_null($problem_id) || is_null($tag_id)) {
            return null;
        }
        $sql = 'SELECT `Problems_Tags`.`problem_id`, `Problems_Tags`.`tag_id`, `Problems_Tags`.`public` FROM Problems_Tags WHERE (problem_id = ? AND tag_id = ?) LIMIT 1;';
        $params = [$problem_id, $tag_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsTags($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsTags}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsTags}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problems_Tags`.`problem_id`, `Problems_Tags`.`tag_id`, `Problems_Tags`.`public` from Problems_Tags';
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
            $allData[] = new ProblemsTags($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsTags} de la base de datos.
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
      * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problems_Tags, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problems_Tags instanceof ProblemsTags)) {
            return self::search(new ProblemsTags($Problems_Tags));
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problems_Tags->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $Problems_Tags->problem_id;
        }
        if (!is_null($Problems_Tags->tag_id)) {
            $clauses[] = '`tag_id` = ?';
            $params[] = $Problems_Tags->tag_id;
        }
        if (!is_null($Problems_Tags->public)) {
            $clauses[] = '`public` = ?';
            $params[] = $Problems_Tags->public;
        }
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysql_real_escape_string($value);
                $clauses[] = "`{$column}` LIKE '%{$value}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT `Problems_Tags`.`problem_id`, `Problems_Tags`.`tag_id`, `Problems_Tags`.`public` FROM `Problems_Tags`';
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
            $ar[] = new ProblemsTags($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags a actualizar.
      */
    final private static function update(ProblemsTags $Problems_Tags) {
        $sql = 'UPDATE `Problems_Tags` SET `public` = ? WHERE `problem_id` = ? AND `tag_id` = ?;';
        $params = [
            $Problems_Tags->public,
            $Problems_Tags->problem_id,$Problems_Tags->tag_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsTags suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsTags dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags a crear.
     */
    final private static function create(ProblemsTags $Problems_Tags) {
        if (is_null($Problems_Tags->public)) {
            $Problems_Tags->public = 0;
        }
        $sql = 'INSERT INTO Problems_Tags (`problem_id`, `tag_id`, `public`) VALUES (?, ?, ?);';
        $params = [
            $Problems_Tags->problem_id,
            $Problems_Tags->tag_id,
            $Problems_Tags->public,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link ProblemsTags} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link ProblemsTags}.
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
     * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags
     * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(ProblemsTags $Problems_TagsA, ProblemsTags $Problems_TagsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $Problems_TagsA->problem_id;
        $b = $Problems_TagsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problems_TagsA->tag_id;
        $b = $Problems_TagsB->tag_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`tag_id` >= ? AND `tag_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`tag_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $Problems_TagsA->public;
        $b = $Problems_TagsB->public;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`public` >= ? AND `public` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`public` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problems_Tags`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new ProblemsTags($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsTags suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param ProblemsTags [$Problems_Tags] El objeto de tipo ProblemsTags a eliminar
     */
    final public static function delete(ProblemsTags $Problems_Tags) {
        if (is_null(self::getByPK($Problems_Tags->problem_id, $Problems_Tags->tag_id))) {
            throw new Exception('Campo no encontrado.');
        }
        $sql = 'DELETE FROM `Problems_Tags` WHERE problem_id = ? AND tag_id = ?;';
        $params = [$Problems_Tags->problem_id, $Problems_Tags->tag_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
