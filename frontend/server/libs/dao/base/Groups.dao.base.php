<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Groups Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Groups}.
 * @access public
 * @abstract
 *
 */
abstract class GroupsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Groups}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Groups [$Groups] El objeto de tipo Groups
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Groups $Groups) : int {
        if (is_null($Groups->group_id) ||
            is_null(self::getByPK($Groups->group_id))
        ) {
            return GroupsDAOBase::create($Groups);
        }
        return GroupsDAOBase::update($Groups);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Groups [$Groups] El objeto de tipo Groups a actualizar.
     */
    final public static function update(Groups $Groups) : int {
        $sql = 'UPDATE `Groups` SET `acl_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE `group_id` = ?;';
        $params = [
            (int)$Groups->acl_id,
            $Groups->create_time,
            $Groups->alias,
            $Groups->name,
            $Groups->description,
            (int)$Groups->group_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Groups} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Groups} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Groups Un objeto del tipo {@link Groups}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $group_id) : ?Groups {
        $sql = 'SELECT `Groups`.`group_id`, `Groups`.`acl_id`, `Groups`.`create_time`, `Groups`.`alias`, `Groups`.`name`, `Groups`.`description` FROM Groups WHERE (group_id = ?) LIMIT 1;';
        $params = [$group_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Groups($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Groups suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Groups [$Groups] El objeto de tipo Groups a eliminar
     */
    final public static function delete(Groups $Groups) : void {
        $sql = 'DELETE FROM `Groups` WHERE group_id = ?;';
        $params = [$Groups->group_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Groups}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Groups}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Groups`.`group_id`, `Groups`.`acl_id`, `Groups`.`create_time`, `Groups`.`alias`, `Groups`.`name`, `Groups`.`description` from Groups';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Groups($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Groups suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Groups [$Groups] El objeto de tipo Groups a crear.
     */
    final public static function create(Groups $Groups) : int {
        if (is_null($Groups->create_time)) {
            $Groups->create_time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Groups (`acl_id`, `create_time`, `alias`, `name`, `description`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$Groups->acl_id,
            $Groups->create_time,
            $Groups->alias,
            $Groups->name,
            $Groups->description,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Groups->group_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
