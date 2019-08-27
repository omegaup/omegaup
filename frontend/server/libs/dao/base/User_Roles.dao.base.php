<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRoles Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRoles}.
 * @access public
 * @abstract
 */
abstract class UserRolesDAOBase {
    /**
     * Obtener {@link \OmegaUp\DAO\VO\UserRoles} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\UserRoles}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\UserRoles Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\UserRoles} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(?int $user_id, ?int $role_id, ?int $acl_id) : ?\OmegaUp\DAO\VO\UserRoles {
        $sql = 'SELECT `User_Roles`.`user_id`, `User_Roles`.`role_id`, `User_Roles`.`acl_id` FROM User_Roles WHERE (user_id = ? AND role_id = ? AND acl_id = ?) LIMIT 1;';
        $params = [$user_id, $role_id, $acl_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\UserRoles($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\UserRoles} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\UserRoles $User_Roles El
     * objeto de tipo \OmegaUp\DAO\VO\UserRoles a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\UserRoles $User_Roles) : void {
        $sql = 'DELETE FROM `User_Roles` WHERE user_id = ? AND role_id = ? AND acl_id = ?;';
        $params = [$User_Roles->user_id, $User_Roles->role_id, $User_Roles->acl_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\UserRoles}.
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
     * @return \OmegaUp\DAO\VO\UserRoles[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\UserRoles}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\UserRoles>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `User_Roles`.`user_id`, `User_Roles`.`role_id`, `User_Roles`.`acl_id` from User_Roles';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\UserRoles($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\UserRoles}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\UserRoles $User_Roles El
     * objeto de tipo {@link \OmegaUp\DAO\VO\UserRoles} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\UserRoles $User_Roles) : int {
        $sql = 'INSERT INTO User_Roles (`user_id`, `role_id`, `acl_id`) VALUES (?, ?, ?);';
        $params = [
            is_null($User_Roles->user_id) ? null : (int)$User_Roles->user_id,
            is_null($User_Roles->role_id) ? null : (int)$User_Roles->role_id,
            is_null($User_Roles->acl_id) ? null : (int)$User_Roles->acl_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
