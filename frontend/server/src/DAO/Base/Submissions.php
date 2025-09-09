<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Submissions Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Submissions}.
 * @access public
 * @abstract
 */
abstract class Submissions {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Submissions $Submissions El objeto de tipo Submissions a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Submissions $Submissions
    ): int {
        $sql = '
            UPDATE
                `Submissions`
            SET
                `current_run_id` = ?,
                `identity_id` = ?,
                `problem_id` = ?,
                `problemset_id` = ?,
                `guid` = ?,
                `language` = ?,
                `time` = ?,
                `status` = ?,
                `verdict` = ?,
                `submit_delay` = ?,
                `type` = ?,
                `school_id` = ?
            WHERE
                (
                    `submission_id` = ?
                );';
        $params = [
            (
                is_null($Submissions->current_run_id) ?
                null :
                intval($Submissions->current_run_id)
            ),
            (
                is_null($Submissions->identity_id) ?
                null :
                intval($Submissions->identity_id)
            ),
            (
                is_null($Submissions->problem_id) ?
                null :
                intval($Submissions->problem_id)
            ),
            (
                is_null($Submissions->problemset_id) ?
                null :
                intval($Submissions->problemset_id)
            ),
            $Submissions->guid,
            $Submissions->language,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Submissions->time
            ),
            $Submissions->status,
            $Submissions->verdict,
            intval($Submissions->submit_delay),
            $Submissions->type,
            (
                is_null($Submissions->school_id) ?
                null :
                intval($Submissions->school_id)
            ),
            intval($Submissions->submission_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Submissions} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Submissions}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Submissions Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Submissions} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $submission_id
    ): ?\OmegaUp\DAO\VO\Submissions {
        $sql = '
            SELECT
                `Submissions`.`submission_id`,
                `Submissions`.`current_run_id`,
                `Submissions`.`identity_id`,
                `Submissions`.`problem_id`,
                `Submissions`.`problemset_id`,
                `Submissions`.`guid`,
                `Submissions`.`language`,
                `Submissions`.`time`,
                `Submissions`.`status`,
                `Submissions`.`verdict`,
                `Submissions`.`submit_delay`,
                `Submissions`.`type`,
                `Submissions`.`school_id`
            FROM
                `Submissions`
            WHERE
                (
                    `submission_id` = ?
                )
            LIMIT 1;';
        $params = [$submission_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Submissions($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Submissions} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Submissions}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $submission_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Submissions`
            WHERE
                (
                    `submission_id` = ?
                );';
        $params = [$submission_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Submissions`.
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
                `Submissions`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Submissions} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Submissions $Submissions El
     * objeto de tipo \OmegaUp\DAO\VO\Submissions a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Submissions $Submissions
    ): void {
        $sql = '
            DELETE FROM
                `Submissions`
            WHERE
                (
                    `submission_id` = ?
                );';
        $params = [
            $Submissions->submission_id
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
     * {@link \OmegaUp\DAO\VO\Submissions}.
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
     * @return list<\OmegaUp\DAO\VO\Submissions> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Submissions}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'submission_id',
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
                `Submissions`.`submission_id`,
                `Submissions`.`current_run_id`,
                `Submissions`.`identity_id`,
                `Submissions`.`problem_id`,
                `Submissions`.`problemset_id`,
                `Submissions`.`guid`,
                `Submissions`.`language`,
                `Submissions`.`time`,
                `Submissions`.`status`,
                `Submissions`.`verdict`,
                `Submissions`.`submit_delay`,
                `Submissions`.`type`,
                `Submissions`.`school_id`
            FROM
                `Submissions`
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
            $allData[] = new \OmegaUp\DAO\VO\Submissions(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Submissions}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Submissions $Submissions El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Submissions}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Submissions $Submissions
    ): int {
        $sql = '
            INSERT INTO
                `Submissions` (
                    `current_run_id`,
                    `identity_id`,
                    `problem_id`,
                    `problemset_id`,
                    `guid`,
                    `language`,
                    `time`,
                    `status`,
                    `verdict`,
                    `submit_delay`,
                    `type`,
                    `school_id`
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
            (
                is_null($Submissions->current_run_id) ?
                null :
                intval($Submissions->current_run_id)
            ),
            (
                is_null($Submissions->identity_id) ?
                null :
                intval($Submissions->identity_id)
            ),
            (
                is_null($Submissions->problem_id) ?
                null :
                intval($Submissions->problem_id)
            ),
            (
                is_null($Submissions->problemset_id) ?
                null :
                intval($Submissions->problemset_id)
            ),
            $Submissions->guid,
            $Submissions->language,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Submissions->time
            ),
            $Submissions->status,
            $Submissions->verdict,
            intval($Submissions->submit_delay),
            $Submissions->type,
            (
                is_null($Submissions->school_id) ?
                null :
                intval($Submissions->school_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Submissions->submission_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
