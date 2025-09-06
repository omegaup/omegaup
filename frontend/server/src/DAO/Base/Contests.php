<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Contests Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Contests}.
 * @access public
 * @abstract
 */
abstract class Contests {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Contests $Contests El objeto de tipo Contests a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Contests $Contests
    ): int {
        $sql = '
            UPDATE
                `Contests`
            SET
                `problemset_id` = ?,
                `acl_id` = ?,
                `title` = ?,
                `description` = ?,
                `start_time` = ?,
                `finish_time` = ?,
                `last_updated` = ?,
                `window_length` = ?,
                `rerun_id` = ?,
                `admission_mode` = ?,
                `alias` = ?,
                `scoreboard` = ?,
                `points_decay_factor` = ?,
                `partial_score` = ?,
                `submissions_gap` = ?,
                `feedback` = ?,
                `penalty` = ?,
                `penalty_type` = ?,
                `penalty_calc_policy` = ?,
                `show_scoreboard_after` = ?,
                `urgent` = ?,
                `languages` = ?,
                `recommended` = ?,
                `archived` = ?,
                `certificate_cutoff` = ?,
                `certificates_status` = ?,
                `contest_for_teams` = ?,
                `default_show_all_contestants_in_scoreboard` = ?,
                `score_mode` = ?,
                `plagiarism_threshold` = ?,
                `check_plagiarism` = ?
            WHERE
                (
                    `contest_id` = ?
                );';
        $params = [
            (
                is_null($Contests->problemset_id) ?
                null :
                intval($Contests->problemset_id)
            ),
            (
                is_null($Contests->acl_id) ?
                null :
                intval($Contests->acl_id)
            ),
            $Contests->title,
            $Contests->description,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->finish_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->last_updated
            ),
            (
                is_null($Contests->window_length) ?
                null :
                intval($Contests->window_length)
            ),
            (
                is_null($Contests->rerun_id) ?
                null :
                intval($Contests->rerun_id)
            ),
            $Contests->admission_mode,
            $Contests->alias,
            intval($Contests->scoreboard),
            floatval($Contests->points_decay_factor),
            intval($Contests->partial_score),
            intval($Contests->submissions_gap),
            $Contests->feedback,
            intval($Contests->penalty),
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            intval($Contests->show_scoreboard_after),
            intval($Contests->urgent),
            $Contests->languages,
            intval($Contests->recommended),
            intval($Contests->archived),
            (
                is_null($Contests->certificate_cutoff) ?
                null :
                intval($Contests->certificate_cutoff)
            ),
            $Contests->certificates_status,
            intval($Contests->contest_for_teams),
            intval($Contests->default_show_all_contestants_in_scoreboard),
            $Contests->score_mode,
            intval($Contests->plagiarism_threshold),
            intval($Contests->check_plagiarism),
            intval($Contests->contest_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Contests} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Contests}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Contests Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Contests} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $contest_id
    ): ?\OmegaUp\DAO\VO\Contests {
        $sql = '
            SELECT
                `Contests`.`contest_id`,
                `Contests`.`problemset_id`,
                `Contests`.`acl_id`,
                `Contests`.`title`,
                `Contests`.`description`,
                `Contests`.`start_time`,
                `Contests`.`finish_time`,
                `Contests`.`last_updated`,
                `Contests`.`window_length`,
                `Contests`.`rerun_id`,
                `Contests`.`admission_mode`,
                `Contests`.`alias`,
                `Contests`.`scoreboard`,
                `Contests`.`points_decay_factor`,
                `Contests`.`partial_score`,
                `Contests`.`submissions_gap`,
                `Contests`.`feedback`,
                `Contests`.`penalty`,
                `Contests`.`penalty_type`,
                `Contests`.`penalty_calc_policy`,
                `Contests`.`show_scoreboard_after`,
                `Contests`.`urgent`,
                `Contests`.`languages`,
                `Contests`.`recommended`,
                `Contests`.`archived`,
                `Contests`.`certificate_cutoff`,
                `Contests`.`certificates_status`,
                `Contests`.`contest_for_teams`,
                `Contests`.`default_show_all_contestants_in_scoreboard`,
                `Contests`.`score_mode`,
                `Contests`.`plagiarism_threshold`,
                `Contests`.`check_plagiarism`
            FROM
                `Contests`
            WHERE
                (
                    `contest_id` = ?
                )
            LIMIT 1;';
        $params = [$contest_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Contests($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Contests} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Contests}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $contest_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Contests`
            WHERE
                (
                    `contest_id` = ?
                );';
        $params = [$contest_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Contests`.
     *
     * Este método obtiene el número total de filas de la tabla **sin cargar campos**,
     * útil para pruebas donde sólo se valida el conteo.
     *
     * @return int Número total de registros.
     */
    final public static function countAll(): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Contests`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Contests} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Contests $Contests El
     * objeto de tipo \OmegaUp\DAO\VO\Contests a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Contests $Contests
    ): void {
        $sql = '
            DELETE FROM
                `Contests`
            WHERE
                (
                    `contest_id` = ?
                );';
        $params = [
            $Contests->contest_id
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
     * {@link \OmegaUp\DAO\VO\Contests}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return list<\OmegaUp\DAO\VO\Contests> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Contests}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'contest_id',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sanitizedOrder = \OmegaUp\MySQLConnection::getInstance()->escape(
            $orden
        );
        \OmegaUp\Validators::validateInEnum(
            $tipoDeOrden,
            'order_type',
            [
                'ASC',
                'DESC',
            ]
        );
        $sql = "
            SELECT
                `Contests`.`contest_id`,
                `Contests`.`problemset_id`,
                `Contests`.`acl_id`,
                `Contests`.`title`,
                `Contests`.`description`,
                `Contests`.`start_time`,
                `Contests`.`finish_time`,
                `Contests`.`last_updated`,
                `Contests`.`window_length`,
                `Contests`.`rerun_id`,
                `Contests`.`admission_mode`,
                `Contests`.`alias`,
                `Contests`.`scoreboard`,
                `Contests`.`points_decay_factor`,
                `Contests`.`partial_score`,
                `Contests`.`submissions_gap`,
                `Contests`.`feedback`,
                `Contests`.`penalty`,
                `Contests`.`penalty_type`,
                `Contests`.`penalty_calc_policy`,
                `Contests`.`show_scoreboard_after`,
                `Contests`.`urgent`,
                `Contests`.`languages`,
                `Contests`.`recommended`,
                `Contests`.`archived`,
                `Contests`.`certificate_cutoff`,
                `Contests`.`certificates_status`,
                `Contests`.`contest_for_teams`,
                `Contests`.`default_show_all_contestants_in_scoreboard`,
                `Contests`.`score_mode`,
                `Contests`.`plagiarism_threshold`,
                `Contests`.`check_plagiarism`
            FROM
                `Contests`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
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
            $allData[] = new \OmegaUp\DAO\VO\Contests(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Contests}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Contests $Contests El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Contests}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Contests $Contests
    ): int {
        $sql = '
            INSERT INTO
                `Contests` (
                    `problemset_id`,
                    `acl_id`,
                    `title`,
                    `description`,
                    `start_time`,
                    `finish_time`,
                    `last_updated`,
                    `window_length`,
                    `rerun_id`,
                    `admission_mode`,
                    `alias`,
                    `scoreboard`,
                    `points_decay_factor`,
                    `partial_score`,
                    `submissions_gap`,
                    `feedback`,
                    `penalty`,
                    `penalty_type`,
                    `penalty_calc_policy`,
                    `show_scoreboard_after`,
                    `urgent`,
                    `languages`,
                    `recommended`,
                    `archived`,
                    `certificate_cutoff`,
                    `certificates_status`,
                    `contest_for_teams`,
                    `default_show_all_contestants_in_scoreboard`,
                    `score_mode`,
                    `plagiarism_threshold`,
                    `check_plagiarism`
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
                is_null($Contests->problemset_id) ?
                null :
                intval($Contests->problemset_id)
            ),
            (
                is_null($Contests->acl_id) ?
                null :
                intval($Contests->acl_id)
            ),
            $Contests->title,
            $Contests->description,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->finish_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Contests->last_updated
            ),
            (
                is_null($Contests->window_length) ?
                null :
                intval($Contests->window_length)
            ),
            (
                is_null($Contests->rerun_id) ?
                null :
                intval($Contests->rerun_id)
            ),
            $Contests->admission_mode,
            $Contests->alias,
            intval($Contests->scoreboard),
            floatval($Contests->points_decay_factor),
            intval($Contests->partial_score),
            intval($Contests->submissions_gap),
            $Contests->feedback,
            intval($Contests->penalty),
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            intval($Contests->show_scoreboard_after),
            intval($Contests->urgent),
            $Contests->languages,
            intval($Contests->recommended),
            intval($Contests->archived),
            (
                is_null($Contests->certificate_cutoff) ?
                null :
                intval($Contests->certificate_cutoff)
            ),
            $Contests->certificates_status,
            intval($Contests->contest_for_teams),
            intval($Contests->default_show_all_contestants_in_scoreboard),
            $Contests->score_mode,
            intval($Contests->plagiarism_threshold),
            intval($Contests->check_plagiarism),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Contests->contest_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
