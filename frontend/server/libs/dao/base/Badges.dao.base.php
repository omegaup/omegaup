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
abstract class BadgesDAOBase {
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
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Badges suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Badges [$Badges] El objeto de tipo Badges a eliminar
     */
    final public static function delete(Badges $Badges) {
        $sql = 'DELETE FROM `Badges` WHERE badge_id = ?;';
        $params = [$Badges->badge_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
