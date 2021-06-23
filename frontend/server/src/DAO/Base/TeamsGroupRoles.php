<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** TeamsGroupRoles Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamsGroupRoles}.
 * @access public
 * @abstract
 */
abstract class TeamsGroupRoles {
    /**
     * Obtener {@link \OmegaUp\DAO\VO\TeamsGroupRoles} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\TeamsGroupRoles}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\TeamsGroupRoles Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\TeamsGroupRoles} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $team_group_id,
        ?int $role_id,
        ?int $acl_id
    ): ?\OmegaUp\DAO\VO\TeamsGroupRoles {
        $sql = '
            SELECT
                `Teams_Group_Roles`.`team_group_id`,
                `Teams_Group_Roles`.`role_id`,
                `Teams_Group_Roles`.`acl_id`
            FROM
                `Teams_Group_Roles`
            WHERE
                (
                    `team_group_id` = ? AND
                    `role_id` = ? AND
                    `acl_id` = ?
                )
            LIMIT 1;';
        $params = [$team_group_id, $role_id, $acl_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamsGroupRoles($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\TeamsGroupRoles} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\TeamsGroupRoles $Teams_Group_Roles El
     * objeto de tipo \OmegaUp\DAO\VO\TeamsGroupRoles a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\TeamsGroupRoles $Teams_Group_Roles
    ): void {
        $sql = '
            DELETE FROM
                `Teams_Group_Roles`
            WHERE
                (
                    `team_group_id` = ? AND
                    `role_id` = ? AND
                    `acl_id` = ?
                );';
        $params = [
            $Teams_Group_Roles->team_group_id,
            $Teams_Group_Roles->role_id,
            $Teams_Group_Roles->acl_id
        ];

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
     * {@link \OmegaUp\DAO\VO\TeamsGroupRoles}.
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
     * @return list<\OmegaUp\DAO\VO\TeamsGroupRoles> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\TeamsGroupRoles}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Teams_Group_Roles`.`team_group_id`,
                `Teams_Group_Roles`.`role_id`,
                `Teams_Group_Roles`.`acl_id`
            FROM
                `Teams_Group_Roles`
        ';
        if (!is_null($orden)) {
            $sql .= (
                ' ORDER BY `' .
                \OmegaUp\MySQLConnection::getInstance()->escape($orden) .
                '` ' .
                ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC')
            );
        }
        if (!is_null($pagina)) {
            $sql .= (
                ' LIMIT ' .
                (($pagina - 1) * $filasPorPagina) .
                ', ' .
                intval($filasPorPagina)
            );
        }
        $allData = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row
        ) {
            $allData[] = new \OmegaUp\DAO\VO\TeamsGroupRoles(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\TeamsGroupRoles}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\TeamsGroupRoles $Teams_Group_Roles El
     * objeto de tipo {@link \OmegaUp\DAO\VO\TeamsGroupRoles}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\TeamsGroupRoles $Teams_Group_Roles
    ): int {
        $sql = '
            INSERT INTO
                `Teams_Group_Roles` (
                    `team_group_id`,
                    `role_id`,
                    `acl_id`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Teams_Group_Roles->team_group_id) ?
                null :
                intval($Teams_Group_Roles->team_group_id)
            ),
            (
                is_null($Teams_Group_Roles->role_id) ?
                null :
                intval($Teams_Group_Roles->role_id)
            ),
            (
                is_null($Teams_Group_Roles->acl_id) ?
                null :
                intval($Teams_Group_Roles->acl_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
