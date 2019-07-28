<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** RolesPermissions Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link RolesPermissions}.
 * @access public
 * @abstract
 *
 */
abstract class RolesPermissionsDAOBase {
    /**
     * Obtener {@link RolesPermissions} por llave primaria.
     *
     * Este metodo cargará un objeto {@link RolesPermissions} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link RolesPermissions Un objeto del tipo {@link RolesPermissions}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $role_id, int $permission_id) : ?RolesPermissions {
        $sql = 'SELECT `Roles_Permissions`.`role_id`, `Roles_Permissions`.`permission_id` FROM Roles_Permissions WHERE (role_id = ? AND permission_id = ?) LIMIT 1;';
        $params = [$role_id, $permission_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new RolesPermissions($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto RolesPermissions suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions a eliminar
     */
    final public static function delete(RolesPermissions $Roles_Permissions) : void {
        $sql = 'DELETE FROM `Roles_Permissions` WHERE role_id = ? AND permission_id = ?;';
        $params = [$Roles_Permissions->role_id, $Roles_Permissions->permission_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link RolesPermissions}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link RolesPermissions}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Roles_Permissions`.`role_id`, `Roles_Permissions`.`permission_id` from Roles_Permissions';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new RolesPermissions($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto RolesPermissions suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param RolesPermissions [$Roles_Permissions] El objeto de tipo RolesPermissions a crear.
     */
    final public static function create(RolesPermissions $Roles_Permissions) : int {
        $sql = 'INSERT INTO Roles_Permissions (`role_id`, `permission_id`) VALUES (?, ?);';
        $params = [
            (int)$Roles_Permissions->role_id,
            (int)$Roles_Permissions->permission_id,
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
