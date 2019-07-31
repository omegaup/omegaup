<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** GroupsScoreboardsProblemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link GroupsScoreboardsProblemsets}.
 * @access public
 * @abstract
 *
 */
abstract class GroupsScoreboardsProblemsetsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link GroupsScoreboardsProblemsets}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) : int {
        if (is_null($Groups_Scoreboards_Problemsets->group_scoreboard_id) ||
            is_null($Groups_Scoreboards_Problemsets->problemset_id) ||
            is_null(self::getByPK($Groups_Scoreboards_Problemsets->group_scoreboard_id, $Groups_Scoreboards_Problemsets->problemset_id))
        ) {
            return GroupsScoreboardsProblemsetsDAOBase::create($Groups_Scoreboards_Problemsets);
        }
        return GroupsScoreboardsProblemsetsDAOBase::update($Groups_Scoreboards_Problemsets);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a actualizar.
     */
    final public static function update(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) : int {
        $sql = 'UPDATE `Groups_Scoreboards_Problemsets` SET `only_ac` = ?, `weight` = ? WHERE `group_scoreboard_id` = ? AND `problemset_id` = ?;';
        $params = [
            (int)$Groups_Scoreboards_Problemsets->only_ac,
            (int)$Groups_Scoreboards_Problemsets->weight,
            (int)$Groups_Scoreboards_Problemsets->group_scoreboard_id,
            (int)$Groups_Scoreboards_Problemsets->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link GroupsScoreboardsProblemsets} por llave primaria.
     *
     * Este metodo cargará un objeto {@link GroupsScoreboardsProblemsets} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link GroupsScoreboardsProblemsets Un objeto del tipo {@link GroupsScoreboardsProblemsets}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $group_scoreboard_id, int $problemset_id) : ?GroupsScoreboardsProblemsets {
        $sql = 'SELECT `Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight` FROM Groups_Scoreboards_Problemsets WHERE (group_scoreboard_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$group_scoreboard_id, $problemset_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new GroupsScoreboardsProblemsets($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto GroupsScoreboardsProblemsets suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a eliminar
     */
    final public static function delete(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) : void {
        $sql = 'DELETE FROM `Groups_Scoreboards_Problemsets` WHERE group_scoreboard_id = ? AND problemset_id = ?;';
        $params = [$Groups_Scoreboards_Problemsets->group_scoreboard_id, $Groups_Scoreboards_Problemsets->problemset_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link GroupsScoreboardsProblemsets}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link GroupsScoreboardsProblemsets}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Groups_Scoreboards_Problemsets`.`group_scoreboard_id`, `Groups_Scoreboards_Problemsets`.`problemset_id`, `Groups_Scoreboards_Problemsets`.`only_ac`, `Groups_Scoreboards_Problemsets`.`weight` from Groups_Scoreboards_Problemsets';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new GroupsScoreboardsProblemsets($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto GroupsScoreboardsProblemsets suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param GroupsScoreboardsProblemsets [$Groups_Scoreboards_Problemsets] El objeto de tipo GroupsScoreboardsProblemsets a crear.
     */
    final public static function create(GroupsScoreboardsProblemsets $Groups_Scoreboards_Problemsets) : int {
        if (is_null($Groups_Scoreboards_Problemsets->only_ac)) {
            $Groups_Scoreboards_Problemsets->only_ac = false;
        }
        if (is_null($Groups_Scoreboards_Problemsets->weight)) {
            $Groups_Scoreboards_Problemsets->weight = 1;
        }
        $sql = 'INSERT INTO Groups_Scoreboards_Problemsets (`group_scoreboard_id`, `problemset_id`, `only_ac`, `weight`) VALUES (?, ?, ?, ?);';
        $params = [
            (int)$Groups_Scoreboards_Problemsets->group_scoreboard_id,
            (int)$Groups_Scoreboards_Problemsets->problemset_id,
            (int)$Groups_Scoreboards_Problemsets->only_ac,
            (int)$Groups_Scoreboards_Problemsets->weight,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
