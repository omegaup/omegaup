<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** CronRunRequests Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CronRunRequests}.
 * @access public
 * @abstract
 */
abstract class CronRunRequests {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests El objeto de tipo CronRunRequests a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests
    ): int {
        $sql = '
            UPDATE
                `Cron_Run_Requests`
            SET
                `name` = ?,
                `requested_by` = ?,
                `status` = ?,
                `requested_at` = ?,
                `picked_at` = ?,
                `finished_at` = ?,
                `run_id` = ?,
                `error_text` = ?
            WHERE
                (
                    `request_id` = ?
                );';
        $params = [
            $Cron_Run_Requests->name,
            (
                is_null($Cron_Run_Requests->requested_by) ?
                null :
                intval($Cron_Run_Requests->requested_by)
            ),
            $Cron_Run_Requests->status,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->requested_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->picked_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->finished_at
            ),
            (
                is_null($Cron_Run_Requests->run_id) ?
                null :
                intval($Cron_Run_Requests->run_id)
            ),
            $Cron_Run_Requests->error_text,
            intval($Cron_Run_Requests->request_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\CronRunRequests} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\CronRunRequests}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\CronRunRequests Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\CronRunRequests} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $request_id
    ): ?\OmegaUp\DAO\VO\CronRunRequests {
        $sql = '
            SELECT
                `Cron_Run_Requests`.`request_id`,
                `Cron_Run_Requests`.`name`,
                `Cron_Run_Requests`.`requested_by`,
                `Cron_Run_Requests`.`status`,
                `Cron_Run_Requests`.`requested_at`,
                `Cron_Run_Requests`.`picked_at`,
                `Cron_Run_Requests`.`finished_at`,
                `Cron_Run_Requests`.`run_id`,
                `Cron_Run_Requests`.`error_text`
            FROM
                `Cron_Run_Requests`
            WHERE
                (
                    `request_id` = ?
                )
            LIMIT 1;';
        $params = [$request_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CronRunRequests($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\CronRunRequests} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\CronRunRequests}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $request_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Cron_Run_Requests`
            WHERE
                (
                    `request_id` = ?
                );';
        $params = [$request_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Cron_Run_Requests`.
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
                `Cron_Run_Requests`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\CronRunRequests} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests El
     * objeto de tipo \OmegaUp\DAO\VO\CronRunRequests a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests
    ): void {
        $sql = '
            DELETE FROM
                `Cron_Run_Requests`
            WHERE
                (
                    `request_id` = ?
                );';
        $params = [
            $Cron_Run_Requests->request_id
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
     * {@link \OmegaUp\DAO\VO\CronRunRequests}.
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
     * @return list<\OmegaUp\DAO\VO\CronRunRequests> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\CronRunRequests}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'request_id',
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
                `Cron_Run_Requests`.`request_id`,
                `Cron_Run_Requests`.`name`,
                `Cron_Run_Requests`.`requested_by`,
                `Cron_Run_Requests`.`status`,
                `Cron_Run_Requests`.`requested_at`,
                `Cron_Run_Requests`.`picked_at`,
                `Cron_Run_Requests`.`finished_at`,
                `Cron_Run_Requests`.`run_id`,
                `Cron_Run_Requests`.`error_text`
            FROM
                `Cron_Run_Requests`
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
            $allData[] = new \OmegaUp\DAO\VO\CronRunRequests(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\CronRunRequests}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CronRunRequests}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\CronRunRequests $Cron_Run_Requests
    ): int {
        $sql = '
            INSERT INTO
                `Cron_Run_Requests` (
                    `name`,
                    `requested_by`,
                    `status`,
                    `requested_at`,
                    `picked_at`,
                    `finished_at`,
                    `run_id`,
                    `error_text`
                ) VALUES (
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
            $Cron_Run_Requests->name,
            (
                is_null($Cron_Run_Requests->requested_by) ?
                null :
                intval($Cron_Run_Requests->requested_by)
            ),
            $Cron_Run_Requests->status,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->requested_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->picked_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Cron_Run_Requests->finished_at
            ),
            (
                is_null($Cron_Run_Requests->run_id) ?
                null :
                intval($Cron_Run_Requests->run_id)
            ),
            $Cron_Run_Requests->error_text,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Cron_Run_Requests->request_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
