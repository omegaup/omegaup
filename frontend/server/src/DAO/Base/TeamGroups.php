<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** TeamGroups Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamGroups}.
 * @access public
 * @abstract
 */
abstract class TeamGroups {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\TeamGroups $Team_Groups El objeto de tipo TeamGroups a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\TeamGroups $Team_Groups
    ): int {
        $sql = '
            UPDATE
                `Team_Groups`
            SET
                `acl_id` = ?,
                `create_time` = ?,
                `alias` = ?,
                `name` = ?,
                `description` = ?
            WHERE
                (
                    `team_group_id` = ?
                );';
        $params = [
            (
                is_null($Team_Groups->acl_id) ?
                null :
                intval($Team_Groups->acl_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Team_Groups->create_time
            ),
            $Team_Groups->alias,
            $Team_Groups->name,
            $Team_Groups->description,
            intval($Team_Groups->team_group_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\TeamGroups} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\TeamGroups}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\TeamGroups Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\TeamGroups} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $team_group_id
    ): ?\OmegaUp\DAO\VO\TeamGroups {
        $sql = '
            SELECT
                `Team_Groups`.`team_group_id`,
                `Team_Groups`.`acl_id`,
                `Team_Groups`.`create_time`,
                `Team_Groups`.`alias`,
                `Team_Groups`.`name`,
                `Team_Groups`.`description`
            FROM
                `Team_Groups`
            WHERE
                (
                    `team_group_id` = ?
                )
            LIMIT 1;';
        $params = [$team_group_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamGroups($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\TeamGroups} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\TeamGroups $Team_Groups El
     * objeto de tipo \OmegaUp\DAO\VO\TeamGroups a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\TeamGroups $Team_Groups
    ): void {
        $sql = '
            DELETE FROM
                `Team_Groups`
            WHERE
                (
                    `team_group_id` = ?
                );';
        $params = [
            $Team_Groups->team_group_id
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
     * {@link \OmegaUp\DAO\VO\TeamGroups}.
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
     * @return list<\OmegaUp\DAO\VO\TeamGroups> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\TeamGroups}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Team_Groups`.`team_group_id`,
                `Team_Groups`.`acl_id`,
                `Team_Groups`.`create_time`,
                `Team_Groups`.`alias`,
                `Team_Groups`.`name`,
                `Team_Groups`.`description`
            FROM
                `Team_Groups`
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
            $allData[] = new \OmegaUp\DAO\VO\TeamGroups(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\TeamGroups}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\TeamGroups $Team_Groups El
     * objeto de tipo {@link \OmegaUp\DAO\VO\TeamGroups}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\TeamGroups $Team_Groups
    ): int {
        $sql = '
            INSERT INTO
                `Team_Groups` (
                    `acl_id`,
                    `create_time`,
                    `alias`,
                    `name`,
                    `description`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Team_Groups->acl_id) ?
                null :
                intval($Team_Groups->acl_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Team_Groups->create_time
            ),
            $Team_Groups->alias,
            $Team_Groups->name,
            $Team_Groups->description,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Team_Groups->team_group_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
