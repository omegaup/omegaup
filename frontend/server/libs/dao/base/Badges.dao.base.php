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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Badges}.
 * @access public
 * @abstract
 *
 */
abstract class BadgesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Badges}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Badges [$Badges] El objeto de tipo Badges
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Badges $Badges) {
        if (is_null(self::getByPK($Badges->badge_id))) {
            return BadgesDAOBase::create($Badges);
        }
        return BadgesDAOBase::update($Badges);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Badges [$Badges] El objeto de tipo Badges a actualizar.
     */
    final public static function update(Badges $Badges) {
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
     * Obtener {@link Badges} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Badges} de la base
     * de datos usando sus llaves primarias.
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
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Badges suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
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

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Badges}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Badges}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Badges`.`badge_id`, `Badges`.`name`, `Badges`.`image_url`, `Badges`.`description`, `Badges`.`hint` from Badges';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Badges($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Badges suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Badges [$Badges] El objeto de tipo Badges a crear.
     */
    final public static function create(Badges $Badges) {
        if (is_null($Badges->name)) {
            $Badges->name = 'MyBadge';
        }
        $sql = 'INSERT INTO Badges (`name`, `image_url`, `description`, `hint`) VALUES (?, ?, ?, ?);';
        $params = [
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
}
