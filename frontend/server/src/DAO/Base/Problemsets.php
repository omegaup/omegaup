<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Problemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problemsets}.
 * @access public
 * @abstract
 */
abstract class Problemsets {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Problemsets $Problemsets El objeto de tipo Problemsets a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Problemsets $Problemsets
    ): int {
        $sql = '
            UPDATE
                `Problemsets`
            SET
                `acl_id` = ?,
                `access_mode` = ?,
                `languages` = ?,
                `needs_basic_information` = ?,
                `requests_user_information` = ?,
                `scoreboard_url` = ?,
                `scoreboard_url_admin` = ?,
                `type` = ?,
                `contest_id` = ?,
                `assignment_id` = ?,
                `interview_id` = ?
            WHERE
                (
                    `problemset_id` = ?
                );';
        $params = [
            (
                is_null($Problemsets->acl_id) ?
                null :
                intval($Problemsets->acl_id)
            ),
            $Problemsets->access_mode,
            $Problemsets->languages,
            intval($Problemsets->needs_basic_information),
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            (
                is_null($Problemsets->contest_id) ?
                null :
                intval($Problemsets->contest_id)
            ),
            (
                is_null($Problemsets->assignment_id) ?
                null :
                intval($Problemsets->assignment_id)
            ),
            (
                is_null($Problemsets->interview_id) ?
                null :
                intval($Problemsets->interview_id)
            ),
            intval($Problemsets->problemset_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Problemsets} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Problemsets}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Problemsets Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Problemsets} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $problemset_id
    ): ?\OmegaUp\DAO\VO\Problemsets {
        $sql = '
            SELECT
                `Problemsets`.`problemset_id`,
                `Problemsets`.`acl_id`,
                `Problemsets`.`access_mode`,
                `Problemsets`.`languages`,
                `Problemsets`.`needs_basic_information`,
                `Problemsets`.`requests_user_information`,
                `Problemsets`.`scoreboard_url`,
                `Problemsets`.`scoreboard_url_admin`,
                `Problemsets`.`type`,
                `Problemsets`.`contest_id`,
                `Problemsets`.`assignment_id`,
                `Problemsets`.`interview_id`
            FROM
                `Problemsets`
            WHERE
                (
                    `problemset_id` = ?
                )
            LIMIT 1;';
        $params = [$problemset_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Problemsets($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Problemsets} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Problemsets $Problemsets El
     * objeto de tipo \OmegaUp\DAO\VO\Problemsets a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Problemsets $Problemsets
    ): void {
        $sql = '
            DELETE FROM
                `Problemsets`
            WHERE
                (
                    `problemset_id` = ?
                );';
        $params = [
            $Problemsets->problemset_id
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
     * {@link \OmegaUp\DAO\VO\Problemsets}.
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
     * @return list<\OmegaUp\DAO\VO\Problemsets> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Problemsets}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Problemsets`.`problemset_id`,
                `Problemsets`.`acl_id`,
                `Problemsets`.`access_mode`,
                `Problemsets`.`languages`,
                `Problemsets`.`needs_basic_information`,
                `Problemsets`.`requests_user_information`,
                `Problemsets`.`scoreboard_url`,
                `Problemsets`.`scoreboard_url_admin`,
                `Problemsets`.`type`,
                `Problemsets`.`contest_id`,
                `Problemsets`.`assignment_id`,
                `Problemsets`.`interview_id`
            FROM
                `Problemsets`
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
            $allData[] = new \OmegaUp\DAO\VO\Problemsets(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Problemsets}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Problemsets $Problemsets El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Problemsets}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Problemsets $Problemsets
    ): int {
        $sql = '
            INSERT INTO
                `Problemsets` (
                    `acl_id`,
                    `access_mode`,
                    `languages`,
                    `needs_basic_information`,
                    `requests_user_information`,
                    `scoreboard_url`,
                    `scoreboard_url_admin`,
                    `type`,
                    `contest_id`,
                    `assignment_id`,
                    `interview_id`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Problemsets->acl_id) ?
                null :
                intval($Problemsets->acl_id)
            ),
            $Problemsets->access_mode,
            $Problemsets->languages,
            intval($Problemsets->needs_basic_information),
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            (
                is_null($Problemsets->contest_id) ?
                null :
                intval($Problemsets->contest_id)
            ),
            (
                is_null($Problemsets->assignment_id) ?
                null :
                intval($Problemsets->assignment_id)
            ),
            (
                is_null($Problemsets->interview_id) ?
                null :
                intval($Problemsets->interview_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problemsets->problemset_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
