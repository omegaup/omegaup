<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsScoreboards Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link GroupsScoreboards}.
 * @access public
 * @abstract
 *
 */
abstract class GroupsScoreboardsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsScoreboards}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(GroupsScoreboards $Groups_Scoreboards) {
        if (is_null(self::getByPK($Groups_Scoreboards->group_scoreboard_id))) {
            return GroupsScoreboardsDAOBase::create($Groups_Scoreboards);
        }
        return GroupsScoreboardsDAOBase::update($Groups_Scoreboards);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a actualizar.
     */
    final public static function update(GroupsScoreboards $Groups_Scoreboards) {
        $sql = 'UPDATE `Groups_Scoreboards` SET `group_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE `group_scoreboard_id` = ?;';
        $params = [
            is_null($Groups_Scoreboards->group_id) ? null : (int)$Groups_Scoreboards->group_id,
            $Groups_Scoreboards->create_time,
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
            is_null($Groups_Scoreboards->group_scoreboard_id) ? null : (int)$Groups_Scoreboards->group_scoreboard_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link GroupsScoreboards} por llave primaria.
     *
     * Este metodo cargará un objeto {@link GroupsScoreboards} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link GroupsScoreboards Un objeto del tipo {@link GroupsScoreboards}. NULL si no hay tal registro.
     */
    final public static function getByPK($group_scoreboard_id) {
        if (is_null($group_scoreboard_id)) {
            return null;
        }
        $sql = 'SELECT `Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description` FROM Groups_Scoreboards WHERE (group_scoreboard_id = ?) LIMIT 1;';
        $params = [$group_scoreboard_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new GroupsScoreboards($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto GroupsScoreboards suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a eliminar
     */
    final public static function delete(GroupsScoreboards $Groups_Scoreboards) {
        $sql = 'DELETE FROM `Groups_Scoreboards` WHERE group_scoreboard_id = ?;';
        $params = [$Groups_Scoreboards->group_scoreboard_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link GroupsScoreboards}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsScoreboards}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Groups_Scoreboards`.`group_scoreboard_id`, `Groups_Scoreboards`.`group_id`, `Groups_Scoreboards`.`create_time`, `Groups_Scoreboards`.`alias`, `Groups_Scoreboards`.`name`, `Groups_Scoreboards`.`description` from Groups_Scoreboards';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new GroupsScoreboards($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsScoreboards suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param GroupsScoreboards [$Groups_Scoreboards] El objeto de tipo GroupsScoreboards a crear.
     */
    final public static function create(GroupsScoreboards $Groups_Scoreboards) {
        if (is_null($Groups_Scoreboards->create_time)) {
            $Groups_Scoreboards->create_time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Groups_Scoreboards (`group_id`, `create_time`, `alias`, `name`, `description`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Groups_Scoreboards->group_id) ? null : (int)$Groups_Scoreboards->group_id,
            $Groups_Scoreboards->create_time,
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Groups_Scoreboards->group_scoreboard_id = $conn->Insert_ID();

        return $ar;
    }
}
