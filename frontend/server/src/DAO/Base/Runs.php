<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Runs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Runs}.
 * @access public
 * @abstract
 */
abstract class Runs {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Runs $Runs El objeto de tipo Runs a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Runs $Runs
    ): int {
        $sql = '
            UPDATE
                `Runs`
            SET
                `submission_id` = ?,
                `version` = ?,
                `commit` = ?,
                `status` = ?,
                `verdict` = ?,
                `runtime` = ?,
                `penalty` = ?,
                `memory` = ?,
                `score` = ?,
                `contest_score` = ?,
                `time` = ?,
                `judged_by` = ?
            WHERE
                (
                    `run_id` = ?
                );';
        $params = [
            (
                is_null($Runs->submission_id) ?
                null :
                intval($Runs->submission_id)
            ),
            $Runs->version,
            $Runs->commit,
            $Runs->status,
            $Runs->verdict,
            intval($Runs->runtime),
            intval($Runs->penalty),
            intval($Runs->memory),
            floatval($Runs->score),
            (
                is_null($Runs->contest_score) ?
                null :
                floatval($Runs->contest_score)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Runs->time
            ),
            $Runs->judged_by,
            intval($Runs->run_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Runs} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Runs}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Runs Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Runs} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $run_id
    ): ?\OmegaUp\DAO\VO\Runs {
        $sql = '
            SELECT
                `Runs`.`run_id`,
                `Runs`.`submission_id`,
                `Runs`.`version`,
                `Runs`.`commit`,
                `Runs`.`status`,
                `Runs`.`verdict`,
                `Runs`.`runtime`,
                `Runs`.`penalty`,
                `Runs`.`memory`,
                `Runs`.`score`,
                `Runs`.`contest_score`,
                `Runs`.`time`,
                `Runs`.`judged_by`
            FROM
                `Runs`
            WHERE
                (
                    `run_id` = ?
                )
            LIMIT 1;';
        $params = [$run_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Runs($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Runs} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Runs}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $run_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Runs`
            WHERE
                (
                    `run_id` = ?
                );';
        $params = [$run_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Runs} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Runs $Runs El
     * objeto de tipo \OmegaUp\DAO\VO\Runs a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Runs $Runs
    ): void {
        $sql = '
            DELETE FROM
                `Runs`
            WHERE
                (
                    `run_id` = ?
                );';
        $params = [
            $Runs->run_id
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
     * {@link \OmegaUp\DAO\VO\Runs}.
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
     * @return list<\OmegaUp\DAO\VO\Runs> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Runs}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'run_id',
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
                `Runs`.`run_id`,
                `Runs`.`submission_id`,
                `Runs`.`version`,
                `Runs`.`commit`,
                `Runs`.`status`,
                `Runs`.`verdict`,
                `Runs`.`runtime`,
                `Runs`.`penalty`,
                `Runs`.`memory`,
                `Runs`.`score`,
                `Runs`.`contest_score`,
                `Runs`.`time`,
                `Runs`.`judged_by`
            FROM
                `Runs`
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
            $allData[] = new \OmegaUp\DAO\VO\Runs(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Runs}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Runs $Runs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Runs}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Runs $Runs
    ): int {
        $sql = '
            INSERT INTO
                `Runs` (
                    `submission_id`,
                    `version`,
                    `commit`,
                    `status`,
                    `verdict`,
                    `runtime`,
                    `penalty`,
                    `memory`,
                    `score`,
                    `contest_score`,
                    `time`,
                    `judged_by`
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
                is_null($Runs->submission_id) ?
                null :
                intval($Runs->submission_id)
            ),
            $Runs->version,
            $Runs->commit,
            $Runs->status,
            $Runs->verdict,
            intval($Runs->runtime),
            intval($Runs->penalty),
            intval($Runs->memory),
            floatval($Runs->score),
            (
                is_null($Runs->contest_score) ?
                null :
                floatval($Runs->contest_score)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Runs->time
            ),
            $Runs->judged_by,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Runs->run_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
