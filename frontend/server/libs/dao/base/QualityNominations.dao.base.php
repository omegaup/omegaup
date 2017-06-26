<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominations Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominations }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominations} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominations $QualityNominations) {
        if (!is_null(self::getByPK($QualityNominations->qualitynomination_id))) {
            return QualityNominationsDAOBase::update($QualityNominations);
        } else {
            return QualityNominationsDAOBase::create($QualityNominations);
        }
    }

    /**
     * Obtener {@link QualityNominations} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominations} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominations Un objeto del tipo {@link QualityNominations}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_id) {
        if (is_null($qualitynomination_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` FROM QualityNominations WHERE (qualitynomination_id = ?) LIMIT 1;';
        $params = [$qualitynomination_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominations($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominations}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominations}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` from QualityNominations';
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
            $allData[] = new QualityNominations($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominations} de la base de datos.
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
      * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($QualityNominations, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($QualityNominations instanceof QualityNominations)) {
            $QualityNominations = new QualityNominations($QualityNominations);
        }

        $clauses = [];
        $params = [];
        if (!is_null($QualityNominations->qualitynomination_id)) {
            $clauses[] = '`qualitynomination_id` = ?';
            $params[] = $QualityNominations->qualitynomination_id;
        }
        if (!is_null($QualityNominations->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $QualityNominations->user_id;
        }
        if (!is_null($QualityNominations->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $QualityNominations->problem_id;
        }
        if (!is_null($QualityNominations->nomination)) {
            $clauses[] = '`nomination` = ?';
            $params[] = $QualityNominations->nomination;
        }
        if (!is_null($QualityNominations->contents)) {
            $clauses[] = '`contents` = ?';
            $params[] = $QualityNominations->contents;
        }
        if (!is_null($QualityNominations->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $QualityNominations->time;
        }
        if (!is_null($QualityNominations->status)) {
            $clauses[] = '`status` = ?';
            $params[] = $QualityNominations->status;
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
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` FROM `QualityNominations`';
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
            $ar[] = new QualityNominations($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a actualizar.
      */
    final private static function update(QualityNominations $QualityNominations) {
        $sql = 'UPDATE `QualityNominations` SET `user_id` = ?, `problem_id` = ?, `nomination` = ?, `contents` = ?, `time` = ?, `status` = ? WHERE `qualitynomination_id` = ?;';
        $params = [
            $QualityNominations->user_id,
            $QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            $QualityNominations->time,
            $QualityNominations->status,
            $QualityNominations->qualitynomination_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominations suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominations dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a crear.
     */
    final private static function create(QualityNominations $QualityNominations) {
        if (is_null($QualityNominations->nomination)) {
            $QualityNominations->nomination = 'suggestion';
        }
        if (is_null($QualityNominations->time)) {
            $QualityNominations->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($QualityNominations->status)) {
            $QualityNominations->status = 'open';
        }
        $sql = 'INSERT INTO QualityNominations (`qualitynomination_id`, `user_id`, `problem_id`, `nomination`, `contents`, `time`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $QualityNominations->qualitynomination_id,
            $QualityNominations->user_id,
            $QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            $QualityNominations->time,
            $QualityNominations->status,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $QualityNominations->qualitynomination_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominations} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link QualityNominations}.
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
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(QualityNominations $QualityNominationsA, QualityNominations $QualityNominationsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $QualityNominationsA->qualitynomination_id;
        $b = $QualityNominationsB->qualitynomination_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`qualitynomination_id` >= ? AND `qualitynomination_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`qualitynomination_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->user_id;
        $b = $QualityNominationsB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->problem_id;
        $b = $QualityNominationsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->nomination;
        $b = $QualityNominationsB->nomination;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`nomination` >= ? AND `nomination` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`nomination` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->contents;
        $b = $QualityNominationsB->contents;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contents` >= ? AND `contents` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contents` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->time;
        $b = $QualityNominationsB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNominationsA->status;
        $b = $QualityNominationsB->status;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`status` >= ? AND `status` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`status` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `QualityNominations`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new QualityNominations($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominations suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a eliminar
     */
    final public static function delete(QualityNominations $QualityNominations) {
        if (is_null(self::getByPK($QualityNominations->qualitynomination_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `QualityNominations` WHERE qualitynomination_id = ?;';
        $params = [$QualityNominations->qualitynomination_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
