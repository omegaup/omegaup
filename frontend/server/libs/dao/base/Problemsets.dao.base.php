<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Problemsets }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetsDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problemsets} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Problemsets $Problemsets) {
        if (!is_null(self::getByPK($Problemsets->problemset_id))) {
            return ProblemsetsDAOBase::update($Problemsets);
        } else {
            return ProblemsetsDAOBase::create($Problemsets);
        }
    }

    /**
     * Obtener {@link Problemsets} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Problemsets} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Problemsets Un objeto del tipo {@link Problemsets}. NULL si no hay tal registro.
     */
    final public static function getByPK($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` FROM Problemsets WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Problemsets($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Problemsets}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Problemsets}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` from Problemsets';
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
            $allData[] = new Problemsets($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problemsets} de la base de datos.
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
      * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Problemsets, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Problemsets instanceof Problemsets)) {
            $Problemsets = new Problemsets($Problemsets);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Problemsets->problemset_id)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = $Problemsets->problemset_id;
        }
        if (!is_null($Problemsets->acl_id)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = $Problemsets->acl_id;
        }
        if (!is_null($Problemsets->access_mode)) {
            $clauses[] = '`access_mode` = ?';
            $params[] = $Problemsets->access_mode;
        }
        if (!is_null($Problemsets->languages)) {
            $clauses[] = '`languages` = ?';
            $params[] = $Problemsets->languages;
        }
        if (!is_null($Problemsets->needs_basic_information)) {
            $clauses[] = '`needs_basic_information` = ?';
            $params[] = $Problemsets->needs_basic_information;
        }
        if (!is_null($Problemsets->requests_user_information)) {
            $clauses[] = '`requests_user_information` = ?';
            $params[] = $Problemsets->requests_user_information;
        }
        if (!is_null($Problemsets->scoreboard_url)) {
            $clauses[] = '`scoreboard_url` = ?';
            $params[] = $Problemsets->scoreboard_url;
        }
        if (!is_null($Problemsets->scoreboard_url_admin)) {
            $clauses[] = '`scoreboard_url_admin` = ?';
            $params[] = $Problemsets->scoreboard_url_admin;
        }
        if (!is_null($Problemsets->type)) {
            $clauses[] = '`type` = ?';
            $params[] = $Problemsets->type;
        }
        if (!is_null($Problemsets->contest_id)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = $Problemsets->contest_id;
        }
        if (!is_null($Problemsets->assignment_id)) {
            $clauses[] = '`assignment_id` = ?';
            $params[] = $Problemsets->assignment_id;
        }
        if (!is_null($Problemsets->interview_id)) {
            $clauses[] = '`interview_id` = ?';
            $params[] = $Problemsets->interview_id;
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
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` FROM `Problemsets`';
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
            $ar[] = new Problemsets($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a actualizar.
      */
    final private static function update(Problemsets $Problemsets) {
        $sql = 'UPDATE `Problemsets` SET `acl_id` = ?, `access_mode` = ?, `languages` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `scoreboard_url` = ?, `scoreboard_url_admin` = ?, `type` = ?, `contest_id` = ?, `assignment_id` = ?, `interview_id` = ? WHERE `problemset_id` = ?;';
        $params = [
            $Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            $Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            $Problemsets->contest_id,
            $Problemsets->assignment_id,
            $Problemsets->interview_id,
            $Problemsets->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problemsets suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Problemsets dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a crear.
     */
    final private static function create(Problemsets $Problemsets) {
        if (is_null($Problemsets->access_mode)) {
            $Problemsets->access_mode = 'public';
        }
        if (is_null($Problemsets->needs_basic_information)) {
            $Problemsets->needs_basic_information = '0';
        }
        if (is_null($Problemsets->requests_user_information)) {
            $Problemsets->requests_user_information = 'no';
        }
        if (is_null($Problemsets->type)) {
            $Problemsets->type = 'Contest';
        }
        $sql = 'INSERT INTO Problemsets (`problemset_id`, `acl_id`, `access_mode`, `languages`, `needs_basic_information`, `requests_user_information`, `scoreboard_url`, `scoreboard_url_admin`, `type`, `contest_id`, `assignment_id`, `interview_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemsets->problemset_id,
            $Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            $Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            $Problemsets->contest_id,
            $Problemsets->assignment_id,
            $Problemsets->interview_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problemsets->problemset_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Problemsets} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Problemsets}.
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
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Problemsets $ProblemsetsA, Problemsets $ProblemsetsB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $ProblemsetsA->problemset_id;
        $b = $ProblemsetsB->problemset_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`problemset_id` >= ? AND `problemset_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`problemset_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->acl_id;
        $b = $ProblemsetsB->acl_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`acl_id` >= ? AND `acl_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`acl_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->access_mode;
        $b = $ProblemsetsB->access_mode;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`access_mode` >= ? AND `access_mode` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`access_mode` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->languages;
        $b = $ProblemsetsB->languages;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`languages` >= ? AND `languages` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`languages` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->needs_basic_information;
        $b = $ProblemsetsB->needs_basic_information;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`needs_basic_information` >= ? AND `needs_basic_information` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`needs_basic_information` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->requests_user_information;
        $b = $ProblemsetsB->requests_user_information;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`requests_user_information` >= ? AND `requests_user_information` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`requests_user_information` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->scoreboard_url;
        $b = $ProblemsetsB->scoreboard_url;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scoreboard_url` >= ? AND `scoreboard_url` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scoreboard_url` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->scoreboard_url_admin;
        $b = $ProblemsetsB->scoreboard_url_admin;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`scoreboard_url_admin` >= ? AND `scoreboard_url_admin` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`scoreboard_url_admin` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->type;
        $b = $ProblemsetsB->type;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`type` >= ? AND `type` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`type` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->contest_id;
        $b = $ProblemsetsB->contest_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`contest_id` >= ? AND `contest_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`contest_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->assignment_id;
        $b = $ProblemsetsB->assignment_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`assignment_id` >= ? AND `assignment_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`assignment_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $ProblemsetsA->interview_id;
        $b = $ProblemsetsB->interview_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`interview_id` >= ? AND `interview_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`interview_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Problemsets`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Problemsets($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Problemsets suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a eliminar
     */
    final public static function delete(Problemsets $Problemsets) {
        if (is_null(self::getByPK($Problemsets->problemset_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Problemsets` WHERE problemset_id = ?;';
        $params = [$Problemsets->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
