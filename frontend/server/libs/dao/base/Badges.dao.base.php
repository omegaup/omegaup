<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Badges Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Badges }.
 * @access public
 * @abstract
 *
 */
abstract class BadgesDAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '`Badges`.`badge_id`, `Badges`.`name`, `Badges`.`image_url`, `Badges`.`description`, `Badges`.`hint`';

    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Badges} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Badges [$Badges] El objeto de tipo Badges
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Badges $Badges) {
        if (!is_null(self::getByPK($Badges->badge_id))) {
            return BadgesDAOBase::update($Badges);
        } else {
            return BadgesDAOBase::create($Badges);
        }
    }

    /**
     * Obtener {@link Badges} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Badges} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Badges Un objeto del tipo {@link Badges}. NULL si no hay tal registro.
     */
    final public static function getByPK($badge_id) {
        if (is_null($badge_id)) {
            return null;
        }
        $sql = 'SELECT `Badges`.`badge_id`, `Badges`.`name`, `Badges`.`image_url`, `Badges`.`description`, `Badges`.`hint` FROM Badges WHERE (badge_id = ?) LIMIT 1;';
        $params = [$badge_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Badges($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Badges}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Badges}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Badges`.`badge_id`, `Badges`.`name`, `Badges`.`image_url`, `Badges`.`description`, `Badges`.`hint` from Badges';
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
            $allData[] = new Badges($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Badges} de la base de datos.
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
      * @param Badges [$Badges] El objeto de tipo Badges
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search($Badges, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!($Badges instanceof Badges)) {
            $Badges = new Badges($Badges);
        }

        $clauses = [];
        $params = [];
        if (!is_null($Badges->badge_id)) {
            $clauses[] = '`badge_id` = ?';
            $params[] = $Badges->badge_id;
        }
        if (!is_null($Badges->name)) {
            $clauses[] = '`name` = ?';
            $params[] = $Badges->name;
        }
        if (!is_null($Badges->image_url)) {
            $clauses[] = '`image_url` = ?';
            $params[] = $Badges->image_url;
        }
        if (!is_null($Badges->description)) {
            $clauses[] = '`description` = ?';
            $params[] = $Badges->description;
        }
        if (!is_null($Badges->hint)) {
            $clauses[] = '`hint` = ?';
            $params[] = $Badges->hint;
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
        $sql = 'SELECT `Badges`.`badge_id`, `Badges`.`name`, `Badges`.`image_url`, `Badges`.`description`, `Badges`.`hint` FROM `Badges`';
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
            $ar[] = new Badges($row);
        }
        return $ar;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Badges [$Badges] El objeto de tipo Badges a actualizar.
      */
    final private static function update(Badges $Badges) {
        $sql = 'UPDATE `Badges` SET `name` = ?, `image_url` = ?, `description` = ?, `hint` = ? WHERE `badge_id` = ?;';
        $params = [
            $Badges->name,
            $Badges->image_url,
            $Badges->description,
            $Badges->hint,
            $Badges->badge_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Badges suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Badges dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Badges [$Badges] El objeto de tipo Badges a crear.
     */
    final private static function create(Badges $Badges) {
        if (is_null($Badges->name)) {
            $Badges->name = 'MyBadge';
        }
        $sql = 'INSERT INTO Badges (`badge_id`, `name`, `image_url`, `description`, `hint`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            $Badges->badge_id,
            $Badges->name,
            $Badges->image_url,
            $Badges->description,
            $Badges->hint,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Badges->badge_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link Badges} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link Badges}.
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
     * @param Badges [$Badges] El objeto de tipo Badges
     * @param Badges [$Badges] El objeto de tipo Badges
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange(Badges $BadgesA, Badges $BadgesB, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

        $a = $BadgesA->badge_id;
        $b = $BadgesB->badge_id;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`badge_id` >= ? AND `badge_id` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`badge_id` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $BadgesA->name;
        $b = $BadgesB->name;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`name` >= ? AND `name` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`name` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $BadgesA->image_url;
        $b = $BadgesB->image_url;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`image_url` >= ? AND `image_url` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`image_url` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $BadgesA->description;
        $b = $BadgesB->description;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`description` >= ? AND `description` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`description` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $a = $BadgesA->hint;
        $b = $BadgesB->hint;
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`hint` >= ? AND `hint` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`hint` = ?';
            $params[] = is_null($a) ? $b : $a;
        }

        $sql = 'SELECT * FROM `Badges`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new Badges($row);
        }
        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Badges suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param Badges [$Badges] El objeto de tipo Badges a eliminar
     */
    final public static function delete(Badges $Badges) {
        if (is_null(self::getByPK($Badges->badge_id))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `Badges` WHERE badge_id = ?;';
        $params = [$Badges->badge_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
