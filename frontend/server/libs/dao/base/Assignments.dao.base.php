<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Assignments Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Assignments }.
 * @access public
 * @abstract
 *
 */
abstract class AssignmentsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Assignments} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Assignments [$Assignments] El objeto de tipo Assignments
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Assignments $Assignments) {
        if (!is_null(self::getByPK($Assignments->assignment_id))) {
            return AssignmentsDAOBase::update($Assignments);
        } else {
            return AssignmentsDAOBase::create($Assignments);
        }
    }

    /**
     * Obtener {@link Assignments} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Assignments} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Assignments Un objeto del tipo {@link Assignments}. NULL si no hay tal registro.
     */
    final public static function getByPK($assignment_id) {
        if (is_null($assignment_id)) {
            return null;
        }
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points` FROM Assignments WHERE (assignment_id = ?) LIMIT 1;';
        $params = [$assignment_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Assignments($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Assignments}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Assignments}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points` from Assignments';
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
            $allData[] = new Assignments($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Assignments} de la base de datos.
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
      * @param Assignments [$Assignments] El objeto de tipo Assignments
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Assignments, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Assignments instanceof Assignments)) {
            $Assignments = new Assignments($Assignments);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Assignments->assignment_id)) {
            $clauses[] = '`assignment_id` = ?';
            $params[] = $Assignments->assignment_id;
        }
        if (!is_null($Assignments->course_id)) {
            $clauses[] = '`course_id` = ?';
            $params[] = $Assignments->course_id;
        }
        if (!is_null($Assignments->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Assignments->problemset_id;
        }
        if (!is_null($Assignments->acl_id)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = $Assignments->acl_id;
        }
        if (!is_null($Assignments->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $Assignments->name;
        }
        if (!is_null($Assignments->description)) {
            $clauses[] = '`description` = ?';
            $params[] = $Assignments->description;
        }
        if (!is_null($Assignments->alias)) {
            $clauses[] = '`alias` = ?';
            $params[] = $Assignments->alias;
        }
        if (!is_null($Assignments->publish_time_delay)) {
            $clauses[] = '`publish_time_delay` = ?';
            $params[] = $Assignments->publish_time_delay;
        }
        if (!is_null($Assignments->assignment_type)) {
            $clauses[] = '`assignment_type` = ?';
            $params[] = $Assignments->assignment_type;
        }
        if (!is_null($Assignments->start_time)) {
            $clauses[] = '`start_time` = ?';
            $params[] = $Assignments->start_time;
        }
        if (!is_null($Assignments->finish_time)) {
            $clauses[] = '`finish_time` = ?';
            $params[] = $Assignments->finish_time;
        }
        if (!is_null($Assignments->max_points)) {
            $clauses[] = '`max_points` = ?';
            $params[] = $Assignments->max_points;
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
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points` FROM `Assignments`';
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
            $ar[] = new Assignments($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Assignments [$Assignments] El objeto de tipo Assignments a actualizar.
      */
    final private static function update(Assignments $Assignments) {
        $sql = 'UPDATE `Assignments` SET `course_id` = ?, `problemset_id` = ?, `acl_id` = ?, `name` = ?, `description` = ?, `alias` = ?, `publish_time_delay` = ?, `assignment_type` = ?, `start_time` = ?, `finish_time` = ?, `max_points` = ? WHERE `assignment_id` = ?;';
        $params = [
            $Assignments->course_id,
            $Assignments->problemset_id,
            $Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            $Assignments->publish_time_delay,
            $Assignments->assignment_type,
            $Assignments->start_time,
            $Assignments->finish_time,
            $Assignments->max_points,
            $Assignments->assignment_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Assignments suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Assignments dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Assignments [$Assignments] El objeto de tipo Assignments a crear.
     */
    final private static function create(Assignments $Assignments) {
        if (is_null($Assignments->start_time)) {
            $Assignments->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Assignments->finish_time)) {
            $Assignments->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Assignments->max_points)) {
            $Assignments->max_points = '0';
        }
        $sql = 'INSERT INTO Assignments (`assignment_id`, `course_id`, `problemset_id`, `acl_id`, `name`, `description`, `alias`, `publish_time_delay`, `assignment_type`, `start_time`, `finish_time`, `max_points`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Assignments->assignment_id,
            $Assignments->course_id,
            $Assignments->problemset_id,
            $Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            $Assignments->publish_time_delay,
            $Assignments->assignment_type,
            $Assignments->start_time,
            $Assignments->finish_time,
            $Assignments->max_points,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Assignments->assignment_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Assignments} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Assignments}.
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
     * @param Assignments [$Assignments] El objeto de tipo Assignments
     * @param Assignments [$Assignments] El objeto de tipo Assignments
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Assignments $AssignmentsA, Assignments $AssignmentsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $AssignmentsA->assignment_id;
        $b = $AssignmentsB->assignment_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`assignment_id` >= ? AND `assignment_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`assignment_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->course_id;
        $b = $AssignmentsB->course_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`course_id` >= ? AND `course_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`course_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->problemset_id;
        $b = $AssignmentsB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->acl_id;
        $b = $AssignmentsB->acl_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`acl_id` >= ? AND `acl_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->name;
        $b = $AssignmentsB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->description;
        $b = $AssignmentsB->description;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`description` >= ? AND `description` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`description` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->alias;
        $b = $AssignmentsB->alias;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`alias` >= ? AND `alias` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`alias` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->publish_time_delay;
        $b = $AssignmentsB->publish_time_delay;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`publish_time_delay` >= ? AND `publish_time_delay` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`publish_time_delay` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->assignment_type;
        $b = $AssignmentsB->assignment_type;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`assignment_type` >= ? AND `assignment_type` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`assignment_type` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->start_time;
        $b = $AssignmentsB->start_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`start_time` >= ? AND `start_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`start_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->finish_time;
        $b = $AssignmentsB->finish_time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`finish_time` >= ? AND `finish_time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`finish_time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AssignmentsA->max_points;
        $b = $AssignmentsB->max_points;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`max_points` >= ? AND `max_points` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`max_points` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Assignments`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Assignments($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Assignments suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Assignments [$Assignments] El objeto de tipo Assignments a eliminar
     */
    final public static function delete(Assignments $Assignments) {
        if (is_null(self::getByPK($Assignments->assignment_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Assignments` WHERE assignment_id = ?;';
        $params = [$Assignments->assignment_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
