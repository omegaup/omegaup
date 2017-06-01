<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominationComments Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominationComments }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationCommentsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominationComments} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominationComments $QualityNomination_Comments) {
        if (!is_null(self::getByPK($QualityNomination_Comments->qualitynomination_comment_id))) {
            return QualityNominationCommentsDAOBase::update($QualityNomination_Comments);
        } else {
            return QualityNominationCommentsDAOBase::create($QualityNomination_Comments);
        }
    }

    /**
     * Obtener {@link QualityNominationComments} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominationComments} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationComments Un objeto del tipo {@link QualityNominationComments}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_comment_id) {
        if (is_null($qualitynomination_comment_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` FROM QualityNomination_Comments WHERE (qualitynomination_comment_id = ?) LIMIT 1;';
        $params = [$qualitynomination_comment_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominationComments($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominationComments}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationComments}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` from QualityNomination_Comments';
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
            $allData[] = new QualityNominationComments($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominationComments} de la base de datos.
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
      * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($QualityNomination_Comments, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($QualityNomination_Comments instanceof QualityNominationComments)) {
            $QualityNomination_Comments = new QualityNominationComments($QualityNomination_Comments);
        }

        $clauses = [];
        $params = [];
        if (!is_null($QualityNomination_Comments->qualitynomination_comment_id)) {
            $clauses[] = '`qualitynomination_comment_id` = ?';
            $params[] = $QualityNomination_Comments->qualitynomination_comment_id;
        }
        if (!is_null($QualityNomination_Comments->qualitynomination_id)) {
            $clauses[] = '`qualitynomination_id` = ?';
            $params[] = $QualityNomination_Comments->qualitynomination_id;
        }
        if (!is_null($QualityNomination_Comments->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $QualityNomination_Comments->user_id;
        }
        if (!is_null($QualityNomination_Comments->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $QualityNomination_Comments->time;
        }
        if (!is_null($QualityNomination_Comments->vote)) {
            $clauses[] = '`vote` = ?';
            $params[] = $QualityNomination_Comments->vote;
        }
        if (!is_null($QualityNomination_Comments->contents)) {
            $clauses[] = '`contents` = ?';
            $params[] = $QualityNomination_Comments->contents;
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
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` FROM `QualityNomination_Comments`';
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
            $ar[] = new QualityNominationComments($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a actualizar.
      */
    final private static function update(QualityNominationComments $QualityNomination_Comments) {
        $sql = 'UPDATE `QualityNomination_Comments` SET `qualitynomination_id` = ?, `user_id` = ?, `time` = ?, `vote` = ?, `contents` = ? WHERE `qualitynomination_comment_id` = ?;';
        $params = [
            $QualityNomination_Comments->qualitynomination_id,
            $QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            $QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
            $QualityNomination_Comments->qualitynomination_comment_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationComments suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominationComments dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a crear.
     */
    final private static function create(QualityNominationComments $QualityNomination_Comments) {
        if (is_null($QualityNomination_Comments->time)) {
            $QualityNomination_Comments->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO QualityNomination_Comments (`qualitynomination_comment_id`, `qualitynomination_id`, `user_id`, `time`, `vote`, `contents`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $QualityNomination_Comments->qualitynomination_comment_id,
            $QualityNomination_Comments->qualitynomination_id,
            $QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            $QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $QualityNomination_Comments->qualitynomination_comment_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominationComments} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link QualityNominationComments}.
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
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(QualityNominationComments $QualityNomination_CommentsA, QualityNominationComments $QualityNomination_CommentsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $QualityNomination_CommentsA->qualitynomination_comment_id;
        $b = $QualityNomination_CommentsB->qualitynomination_comment_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`qualitynomination_comment_id` >= ? AND `qualitynomination_comment_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`qualitynomination_comment_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_CommentsA->qualitynomination_id;
        $b = $QualityNomination_CommentsB->qualitynomination_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`qualitynomination_id` >= ? AND `qualitynomination_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`qualitynomination_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_CommentsA->user_id;
        $b = $QualityNomination_CommentsB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_CommentsA->time;
        $b = $QualityNomination_CommentsB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_CommentsA->vote;
        $b = $QualityNomination_CommentsB->vote;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`vote` >= ? AND `vote` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`vote` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_CommentsA->contents;
        $b = $QualityNomination_CommentsB->contents;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contents` >= ? AND `contents` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contents` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `QualityNomination_Comments`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new QualityNominationComments($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominationComments suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a eliminar
     */
    final public static function delete(QualityNominationComments $QualityNomination_Comments) {
        if (is_null(self::getByPK($QualityNomination_Comments->qualitynomination_comment_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `QualityNomination_Comments` WHERE qualitynomination_comment_id = ?;';
        $params = [$QualityNomination_Comments->qualitynomination_comment_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
