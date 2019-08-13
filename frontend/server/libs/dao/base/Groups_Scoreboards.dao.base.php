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
     * Actualizar registros.
     *
     * @param GroupsScoreboards $Groups_Scoreboards El objeto de tipo GroupsScoreboards a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(GroupsScoreboards $Groups_Scoreboards) : int {
        $sql = 'UPDATE `Groups_Scoreboards` SET `group_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE `group_scoreboard_id` = ?;';
        $params = [
            (int)$Groups_Scoreboards->group_id,
            DAO::toMySQLTimestamp($Groups_Scoreboards->create_time),
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
            (int)$Groups_Scoreboards->group_scoreboard_id,
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
     * @return ?GroupsScoreboards Un objeto del tipo {@link GroupsScoreboards}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $group_scoreboard_id) : ?GroupsScoreboards {
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param GroupsScoreboards $Groups_Scoreboards El objeto de tipo GroupsScoreboards a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(GroupsScoreboards $Groups_Scoreboards) : void {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return array Un arreglo que contiene objetos del tipo {@link GroupsScoreboards}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
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
     * @param GroupsScoreboards $Groups_Scoreboards El objeto de tipo GroupsScoreboards a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(GroupsScoreboards $Groups_Scoreboards) : int {
        if (is_null($Groups_Scoreboards->create_time)) {
            $Groups_Scoreboards->create_time = Time::get();
        }
        $sql = 'INSERT INTO Groups_Scoreboards (`group_id`, `create_time`, `alias`, `name`, `description`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$Groups_Scoreboards->group_id,
            DAO::toMySQLTimestamp($Groups_Scoreboards->create_time),
            $Groups_Scoreboards->alias,
            $Groups_Scoreboards->name,
            $Groups_Scoreboards->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Groups_Scoreboards->group_scoreboard_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
