<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** AIEditorialJobs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\AIEditorialJobs}.
 * @access public
 * @abstract
 */
abstract class AIEditorialJobs {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\AIEditorialJobs}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AIEditorialJobs}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs
    ): int {
        if (
            empty($AI_Editorial_Jobs->job_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                AI_Editorial_Jobs (
                    `job_id`,
                    `problem_id`,
                    `user_id`,
                    `status`,
                    `error_message`,
                    `is_retriable`,
                    `attempts`,
                    `created_at`,
                    `md_en`,
                    `md_es`,
                    `md_pt`,
                    `validation_verdict`
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
                    ?
                );';
        $params = [
            $AI_Editorial_Jobs->job_id,
            (
                $AI_Editorial_Jobs->problem_id !== null ?
                intval($AI_Editorial_Jobs->problem_id) :
                null
            ),
            (
                $AI_Editorial_Jobs->user_id !== null ?
                intval($AI_Editorial_Jobs->user_id) :
                null
            ),
            $AI_Editorial_Jobs->status,
            $AI_Editorial_Jobs->error_message,
            intval($AI_Editorial_Jobs->is_retriable),
            intval($AI_Editorial_Jobs->attempts),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $AI_Editorial_Jobs->created_at
            ),
            $AI_Editorial_Jobs->md_en,
            $AI_Editorial_Jobs->md_es,
            $AI_Editorial_Jobs->md_pt,
            $AI_Editorial_Jobs->validation_verdict,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs El objeto de tipo AIEditorialJobs a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs
    ): int {
        $sql = '
            UPDATE
                `AI_Editorial_Jobs`
            SET
                `problem_id` = ?,
                `user_id` = ?,
                `status` = ?,
                `error_message` = ?,
                `is_retriable` = ?,
                `attempts` = ?,
                `created_at` = ?,
                `md_en` = ?,
                `md_es` = ?,
                `md_pt` = ?,
                `validation_verdict` = ?
            WHERE
                (
                    `job_id` = ?
                );';
        $params = [
            (
                $AI_Editorial_Jobs->problem_id === null ?
                null :
                intval($AI_Editorial_Jobs->problem_id)
            ),
            (
                $AI_Editorial_Jobs->user_id === null ?
                null :
                intval($AI_Editorial_Jobs->user_id)
            ),
            $AI_Editorial_Jobs->status,
            $AI_Editorial_Jobs->error_message,
            intval($AI_Editorial_Jobs->is_retriable),
            intval($AI_Editorial_Jobs->attempts),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $AI_Editorial_Jobs->created_at
            ),
            $AI_Editorial_Jobs->md_en,
            $AI_Editorial_Jobs->md_es,
            $AI_Editorial_Jobs->md_pt,
            $AI_Editorial_Jobs->validation_verdict,
            $AI_Editorial_Jobs->job_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\AIEditorialJobs} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\AIEditorialJobs}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\AIEditorialJobs Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\AIEditorialJobs} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?string $job_id
    ): ?\OmegaUp\DAO\VO\AIEditorialJobs {
        $sql = '
            SELECT
                `AI_Editorial_Jobs`.`job_id`,
                `AI_Editorial_Jobs`.`problem_id`,
                `AI_Editorial_Jobs`.`user_id`,
                `AI_Editorial_Jobs`.`status`,
                `AI_Editorial_Jobs`.`error_message`,
                `AI_Editorial_Jobs`.`is_retriable`,
                `AI_Editorial_Jobs`.`attempts`,
                `AI_Editorial_Jobs`.`created_at`,
                `AI_Editorial_Jobs`.`md_en`,
                `AI_Editorial_Jobs`.`md_es`,
                `AI_Editorial_Jobs`.`md_pt`,
                `AI_Editorial_Jobs`.`validation_verdict`
            FROM
                `AI_Editorial_Jobs`
            WHERE
                (
                    `job_id` = ?
                )
            LIMIT 1;';
        $params = [$job_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\AIEditorialJobs($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\AIEditorialJobs} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\AIEditorialJobs}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?string $job_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `AI_Editorial_Jobs`
            WHERE
                (
                    `job_id` = ?
                );';
        $params = [$job_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `AI_Editorial_Jobs`.
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
                `AI_Editorial_Jobs`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\AIEditorialJobs} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo \OmegaUp\DAO\VO\AIEditorialJobs a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs
    ): void {
        $sql = '
            DELETE FROM
                `AI_Editorial_Jobs`
            WHERE
                (
                    `job_id` = ?
                );';
        $params = [
            $AI_Editorial_Jobs->job_id
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
     * {@link \OmegaUp\DAO\VO\AIEditorialJobs}.
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
     * @return list<\OmegaUp\DAO\VO\AIEditorialJobs> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\AIEditorialJobs}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'job_id',
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
                `AI_Editorial_Jobs`.`job_id`,
                `AI_Editorial_Jobs`.`problem_id`,
                `AI_Editorial_Jobs`.`user_id`,
                `AI_Editorial_Jobs`.`status`,
                `AI_Editorial_Jobs`.`error_message`,
                `AI_Editorial_Jobs`.`is_retriable`,
                `AI_Editorial_Jobs`.`attempts`,
                `AI_Editorial_Jobs`.`created_at`,
                `AI_Editorial_Jobs`.`md_en`,
                `AI_Editorial_Jobs`.`md_es`,
                `AI_Editorial_Jobs`.`md_pt`,
                `AI_Editorial_Jobs`.`validation_verdict`
            FROM
                `AI_Editorial_Jobs`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
        if ($pagina !== null) {
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
            $allData[] = new \OmegaUp\DAO\VO\AIEditorialJobs(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\AIEditorialJobs}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\AIEditorialJobs}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\AIEditorialJobs $AI_Editorial_Jobs
    ): int {
        $sql = '
            INSERT INTO
                `AI_Editorial_Jobs` (
                    `job_id`,
                    `problem_id`,
                    `user_id`,
                    `status`,
                    `error_message`,
                    `is_retriable`,
                    `attempts`,
                    `created_at`,
                    `md_en`,
                    `md_es`,
                    `md_pt`,
                    `validation_verdict`
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
                    ?
                );';
        $params = [
            $AI_Editorial_Jobs->job_id,
            (
                $AI_Editorial_Jobs->problem_id === null ?
                null :
                intval($AI_Editorial_Jobs->problem_id)
            ),
            (
                $AI_Editorial_Jobs->user_id === null ?
                null :
                intval($AI_Editorial_Jobs->user_id)
            ),
            $AI_Editorial_Jobs->status,
            $AI_Editorial_Jobs->error_message,
            intval($AI_Editorial_Jobs->is_retriable),
            intval($AI_Editorial_Jobs->attempts),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $AI_Editorial_Jobs->created_at
            ),
            $AI_Editorial_Jobs->md_en,
            $AI_Editorial_Jobs->md_es,
            $AI_Editorial_Jobs->md_pt,
            $AI_Editorial_Jobs->validation_verdict,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
