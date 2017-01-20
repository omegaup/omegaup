<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Clarifications Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Clarifications }.
 * @access public
 * @abstract
 *
 */
abstract class ClarificationsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`contest_id`, `Clarifications`.`public`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Clarifications} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Clarifications $Clarifications) {
        if (!is_null(self::getByPK($Clarifications->clarification_id))) {
            return ClarificationsDAOBase::update($Clarifications);
        } else {
            return ClarificationsDAOBase::create($Clarifications);
        }
    }

    /**
     * Obtener {@link Clarifications} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Clarifications} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Clarifications Un objeto del tipo {@link Clarifications}. NULL si no hay tal registro.
     */
    final public static function getByPK($clarification_id) {
        if (is_null($clarification_id)) {
            return null;
        }
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`contest_id`, `Clarifications`.`public` FROM Clarifications WHERE (clarification_id = ?) LIMIT 1;';
        $params = [$clarification_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Clarifications($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Clarifications}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Clarifications}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`contest_id`, `Clarifications`.`public` from Clarifications';
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
            $allData[] = new Clarifications($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Clarifications} de la base de datos.
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
      * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Clarifications, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Clarifications instanceof Clarifications)) {
            $Clarifications = new Clarifications($Clarifications);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Clarifications->clarification_id)) {
            $clauses[] = '`clarification_id` = ?';
            $params[] = $Clarifications->clarification_id;
        }
        if (!is_null($Clarifications->author_id)) {
            $clauses[] = '`author_id` = ?';
            $params[] = $Clarifications->author_id;
        }
        if (!is_null($Clarifications->message)) {
            $clauses[] = '`message` = ?';
            $params[] = $Clarifications->message;
        }
        if (!is_null($Clarifications->answer)) {
            $clauses[] = '`answer` = ?';
            $params[] = $Clarifications->answer;
        }
        if (!is_null($Clarifications->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Clarifications->time;
        }
        if (!is_null($Clarifications->problem_id)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = $Clarifications->problem_id;
        }
        if (!is_null($Clarifications->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Clarifications->contest_id;
        }
        if (!is_null($Clarifications->public)) {
            $clauses[] = '`public` = ?';
            $params[] = $Clarifications->public;
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
        $sql = 'SELECT `Clarifications`.`clarification_id`, `Clarifications`.`author_id`, `Clarifications`.`message`, `Clarifications`.`answer`, `Clarifications`.`time`, `Clarifications`.`problem_id`, `Clarifications`.`contest_id`, `Clarifications`.`public` FROM `Clarifications`';
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
            $ar[] = new Clarifications($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a actualizar.
      */
    final private static function update(Clarifications $Clarifications) {
        $sql = 'UPDATE `Clarifications` SET `author_id` = ?, `message` = ?, `answer` = ?, `time` = ?, `problem_id` = ?, `contest_id` = ?, `public` = ? WHERE `clarification_id` = ?;';
        $params = [
            $Clarifications->author_id,
            $Clarifications->message,
            $Clarifications->answer,
            $Clarifications->time,
            $Clarifications->problem_id,
            $Clarifications->contest_id,
            $Clarifications->public,
            $Clarifications->clarification_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Clarifications suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Clarifications dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a crear.
     */
    final private static function create(Clarifications $Clarifications) {
        if (is_null($Clarifications->time)) {
            $Clarifications->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Clarifications->public)) {
            $Clarifications->public = '0';
        }
        $sql = 'INSERT INTO Clarifications (`clarification_id`, `author_id`, `message`, `answer`, `time`, `problem_id`, `contest_id`, `public`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Clarifications->clarification_id,
            $Clarifications->author_id,
            $Clarifications->message,
            $Clarifications->answer,
            $Clarifications->time,
            $Clarifications->problem_id,
            $Clarifications->contest_id,
            $Clarifications->public,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Clarifications->clarification_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Clarifications} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Clarifications}.
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
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Clarifications $ClarificationsA, Clarifications $ClarificationsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $ClarificationsA->clarification_id;
        $b = $ClarificationsB->clarification_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`clarification_id` >= ? AND `clarification_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`clarification_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->author_id;
        $b = $ClarificationsB->author_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`author_id` >= ? AND `author_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`author_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->message;
        $b = $ClarificationsB->message;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`message` >= ? AND `message` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`message` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->answer;
        $b = $ClarificationsB->answer;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`answer` >= ? AND `answer` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`answer` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->time;
        $b = $ClarificationsB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->problem_id;
        $b = $ClarificationsB->problem_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problem_id` >= ? AND `problem_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problem_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->contest_id;
        $b = $ClarificationsB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ClarificationsA->public;
        $b = $ClarificationsB->public;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`public` >= ? AND `public` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`public` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Clarifications`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Clarifications($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Clarifications suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Clarifications [$Clarifications] El objeto de tipo Clarifications a eliminar
     */
    final public static function delete(Clarifications $Clarifications) {
        if (is_null(self::getByPK($Clarifications->clarification_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Clarifications` WHERE clarification_id = ?;';
        $params = [$Clarifications->clarification_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
