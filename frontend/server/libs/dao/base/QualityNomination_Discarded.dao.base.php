<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominationDiscarded Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominationDiscarded }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationDiscardedDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`QualityNomination_Discarded`.`user_id`, `QualityNomination_Discarded`.`problem_id`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominationDiscarded} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominationDiscarded $QualityNomination_Discarded) {
        if (!is_null(self::getByPK($QualityNomination_Discarded->user_id))) {
            return QualityNominationDiscardedDAOBase::update($QualityNomination_Discarded);
        } else {
            return QualityNominationDiscardedDAOBase::create($QualityNomination_Discarded);
        }
    }

    /**
     * Obtener {@link QualityNominationDiscarded} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominationDiscarded} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationDiscarded Un objeto del tipo {@link QualityNominationDiscarded}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id) {
        if (is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNomination_Discarded`.`user_id`, `QualityNomination_Discarded`.`problem_id` FROM QualityNomination_Discarded WHERE (user_id = ?) LIMIT 1;';
        $params = [$user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominationDiscarded($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominationDiscarded}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationDiscarded}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNomination_Discarded`.`user_id`, `QualityNomination_Discarded`.`problem_id` from QualityNomination_Discarded';
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
            $allData[] = new QualityNominationDiscarded($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominationDiscarded} de la base de datos.
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
      * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($QualityNomination_Discarded, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($QualityNomination_Discarded instanceof QualityNominationDiscarded)) {
            $QualityNomination_Discarded = new QualityNominationDiscarded($QualityNomination_Discarded);
        }

        $clauses = [];
        $params = [];
        if (!is_null($QualityNomination_Discarded->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $QualityNomination_Discarded->user_id;
        }
        if (!is_null($QualityNomination_Discarded->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $QualityNomination_Discarded->problem_id;
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
        $sql = 'SELECT `QualityNomination_Discarded`.`user_id`, `QualityNomination_Discarded`.`problem_id` FROM `QualityNomination_Discarded`';
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
            $ar[] = new QualityNominationDiscarded($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded a actualizar.
      */
    final private static function update(QualityNominationDiscarded $QualityNomination_Discarded) {
        $sql = 'UPDATE `QualityNomination_Discarded` SET `problem_id` = ? WHERE `user_id` = ?;';
        $params = [
            $QualityNomination_Discarded->problem_id,
            $QualityNomination_Discarded->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationDiscarded suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominationDiscarded dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded a crear.
     */
    final private static function create(QualityNominationDiscarded $QualityNomination_Discarded) {
        $sql = 'INSERT INTO QualityNomination_Discarded (`user_id`, `problem_id`) VALUES (?, ?);';
        $params = [
            $QualityNomination_Discarded->user_id,
            $QualityNomination_Discarded->problem_id,
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
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link QualityNominationDiscarded} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link QualityNominationDiscarded}.
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
     * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded
     * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(QualityNominationDiscarded $QualityNomination_DiscardedA, QualityNominationDiscarded $QualityNomination_DiscardedB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $QualityNomination_DiscardedA->user_id;
        $b = $QualityNomination_DiscardedB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $QualityNomination_DiscardedA->problem_id;
        $b = $QualityNomination_DiscardedB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `QualityNomination_Discarded`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new QualityNominationDiscarded($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominationDiscarded suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param QualityNominationDiscarded [$QualityNomination_Discarded] El objeto de tipo QualityNominationDiscarded a eliminar
     */
    final public static function delete(QualityNominationDiscarded $QualityNomination_Discarded) {
        if (is_null(self::getByPK($QualityNomination_Discarded->user_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `QualityNomination_Discarded` WHERE user_id = ?;';
        $params = [$QualityNomination_Discarded->user_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
