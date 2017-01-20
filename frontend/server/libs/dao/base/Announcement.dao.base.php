<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Announcement Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Announcement }.
 * @access public
 * @abstract
 *
 */
abstract class AnnouncementDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Announcement} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Announcement [$Announcement] El objeto de tipo Announcement
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Announcement $Announcement) {
        if (!is_null(self::getByPK($Announcement->announcement_id))) {
            return AnnouncementDAOBase::update($Announcement);
        } else {
            return AnnouncementDAOBase::create($Announcement);
        }
    }

    /**
     * Obtener {@link Announcement} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Announcement} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Announcement Un objeto del tipo {@link Announcement}. NULL si no hay tal registro.
     */
    final public static function getByPK($announcement_id) {
        if (is_null($announcement_id)) {
            return null;
        }
        $sql = 'SELECT `Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description` FROM Announcement WHERE (announcement_id = ?) LIMIT 1;';
        $params = [$announcement_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Announcement($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Announcement}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Announcement}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description` from Announcement';
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
            $allData[] = new Announcement($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Announcement} de la base de datos.
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
      * @param Announcement [$Announcement] El objeto de tipo Announcement
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Announcement, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Announcement instanceof Announcement)) {
            $Announcement = new Announcement($Announcement);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Announcement->announcement_id)) {
            $clauses[] = '`announcement_id` = ?';
            $params[] = $Announcement->announcement_id;
        }
        if (!is_null($Announcement->user_id)) {
            $clauses[] = '`user_id` = ?';
            $params[] = $Announcement->user_id;
        }
        if (!is_null($Announcement->time)) {
            $clauses[] = '`time` = ?';
            $params[] = $Announcement->time;
        }
        if (!is_null($Announcement->description)) {
            $clauses[] = '`description` = ?';
            $params[] = $Announcement->description;
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
        $sql = 'SELECT `Announcement`.`announcement_id`, `Announcement`.`user_id`, `Announcement`.`time`, `Announcement`.`description` FROM `Announcement`';
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
            $ar[] = new Announcement($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Announcement [$Announcement] El objeto de tipo Announcement a actualizar.
      */
    final private static function update(Announcement $Announcement) {
        $sql = 'UPDATE `Announcement` SET `user_id` = ?, `time` = ?, `description` = ? WHERE `announcement_id` = ?;';
        $params = [
            $Announcement->user_id,
            $Announcement->time,
            $Announcement->description,
            $Announcement->announcement_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Announcement suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Announcement dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Announcement [$Announcement] El objeto de tipo Announcement a crear.
     */
    final private static function create(Announcement $Announcement) {
        if (is_null($Announcement->time)) {
            $Announcement->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Announcement (`announcement_id`, `user_id`, `time`, `description`) VALUES (?, ?, ?, ?);';
        $params = [
            $Announcement->announcement_id,
            $Announcement->user_id,
            $Announcement->time,
            $Announcement->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Announcement->announcement_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Announcement} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Announcement}.
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
     * @param Announcement [$Announcement] El objeto de tipo Announcement
     * @param Announcement [$Announcement] El objeto de tipo Announcement
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Announcement $AnnouncementA, Announcement $AnnouncementB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $AnnouncementA->announcement_id;
        $b = $AnnouncementB->announcement_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`announcement_id` >= ? AND `announcement_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`announcement_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AnnouncementA->user_id;
        $b = $AnnouncementB->user_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`user_id` >= ? AND `user_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`user_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AnnouncementA->time;
        $b = $AnnouncementB->time;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`time` >= ? AND `time` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`time` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $AnnouncementA->description;
        $b = $AnnouncementB->description;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`description` >= ? AND `description` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`description` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Announcement`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Announcement($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Announcement suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Announcement [$Announcement] El objeto de tipo Announcement a eliminar
     */
    final public static function delete(Announcement $Announcement) {
        if (is_null(self::getByPK($Announcement->announcement_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Announcement` WHERE announcement_id = ?;';
        $params = [$Announcement->announcement_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
