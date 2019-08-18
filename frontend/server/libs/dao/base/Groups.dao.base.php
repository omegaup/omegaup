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
     * Actualizar registros.
     *
     * @param Groups $Groups El objeto de tipo Groups a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Groups $Groups) : int {
        $sql = 'UPDATE `Groups` SET `acl_id` = ?, `create_time` = ?, `alias` = ?, `name` = ?, `description` = ? WHERE `group_id` = ?;';
        $params = [
            is_null($Groups->acl_id) ? null : (int)$Groups->acl_id,
            DAO::toMySQLTimestamp($Groups->create_time),
            $Groups->alias,
            $Groups->name,
            $Groups->description,
            (int)$Groups->group_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Groups} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Groups} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Groups Un objeto del tipo {@link Groups}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $group_id) : ?Groups {
        $sql = 'SELECT `Groups`.`group_id`, `Groups`.`acl_id`, `Groups`.`create_time`, `Groups`.`alias`, `Groups`.`name`, `Groups`.`description` FROM Groups WHERE (group_id = ?) LIMIT 1;';
        $params = [$group_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Groups $Groups El objeto de tipo Groups a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Groups $Groups) : void {
        $sql = 'DELETE FROM `Groups` WHERE group_id = ?;';
        $params = [$Groups->group_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return Groups[] Un arreglo que contiene objetos del tipo {@link Groups}.
     *
     * @psalm-return array<int, Groups>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Groups`.`group_id`, `Groups`.`acl_id`, `Groups`.`create_time`, `Groups`.`alias`, `Groups`.`name`, `Groups`.`description` from Groups';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param Groups $Groups El objeto de tipo Groups a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Groups $Groups) : int {
        $sql = 'INSERT INTO Groups (`acl_id`, `create_time`, `alias`, `name`, `description`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Groups->acl_id) ? null : (int)$Groups->acl_id,
            DAO::toMySQLTimestamp($Groups->create_time),
            $Groups->alias,
            $Groups->name,
            $Groups->description,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Groups->group_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
