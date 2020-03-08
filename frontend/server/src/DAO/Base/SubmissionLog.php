<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** SubmissionLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SubmissionLog}.
 * @access public
 * @abstract
 */
abstract class SubmissionLog {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\SubmissionLog}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\SubmissionLog $Submission_Log El
     * objeto de tipo {@link \OmegaUp\DAO\VO\SubmissionLog}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\SubmissionLog $Submission_Log
    ): int {
        if (
            empty($Submission_Log->submission_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Submission_Log (
                    `problemset_id`,
                    `submission_id`,
                    `user_id`,
                    `identity_id`,
                    `ip`,
                    `time`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                !is_null($Submission_Log->problemset_id) ?
                intval($Submission_Log->problemset_id) :
                null
            ),
            $Submission_Log->submission_id,
            (
                !is_null($Submission_Log->user_id) ?
                intval($Submission_Log->user_id) :
                null
            ),
            (
                !is_null($Submission_Log->identity_id) ?
                intval($Submission_Log->identity_id) :
                null
            ),
            (
                !is_null($Submission_Log->ip) ?
                intval($Submission_Log->ip) :
                null
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Submission_Log->time
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\SubmissionLog $Submission_Log El objeto de tipo SubmissionLog a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\SubmissionLog $Submission_Log
    ): int {
        $sql = '
            UPDATE
                `Submission_Log`
            SET
                `problemset_id` = ?,
                `user_id` = ?,
                `identity_id` = ?,
                `ip` = ?,
                `time` = ?
            WHERE
                (
                    `submission_id` = ?
                );';
        $params = [
            (
                is_null($Submission_Log->problemset_id) ?
                null :
                intval($Submission_Log->problemset_id)
            ),
            (
                is_null($Submission_Log->user_id) ?
                null :
                intval($Submission_Log->user_id)
            ),
            (
                is_null($Submission_Log->identity_id) ?
                null :
                intval($Submission_Log->identity_id)
            ),
            (
                is_null($Submission_Log->ip) ?
                null :
                intval($Submission_Log->ip)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Submission_Log->time
            ),
            (
                is_null($Submission_Log->submission_id) ?
                null :
                intval($Submission_Log->submission_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\SubmissionLog} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\SubmissionLog}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\SubmissionLog Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\SubmissionLog} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $submission_id
    ): ?\OmegaUp\DAO\VO\SubmissionLog {
        $sql = '
            SELECT
                `Submission_Log`.`problemset_id`,
                `Submission_Log`.`submission_id`,
                `Submission_Log`.`user_id`,
                `Submission_Log`.`identity_id`,
                `Submission_Log`.`ip`,
                `Submission_Log`.`time`
            FROM
                `Submission_Log`
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
        return new \OmegaUp\DAO\VO\SubmissionLog($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\SubmissionLog} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\SubmissionLog $Submission_Log El
     * objeto de tipo \OmegaUp\DAO\VO\SubmissionLog a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\SubmissionLog $Submission_Log
    ): void {
        $sql = '
            DELETE FROM
                `Submission_Log`
            WHERE
                (
                    `submission_id` = ?
                );';
        $params = [
            $Submission_Log->submission_id
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
     * {@link \OmegaUp\DAO\VO\SubmissionLog}.
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
     * @return list<\OmegaUp\DAO\VO\SubmissionLog> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\SubmissionLog}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Submission_Log`.`problemset_id`,
                `Submission_Log`.`submission_id`,
                `Submission_Log`.`user_id`,
                `Submission_Log`.`identity_id`,
                `Submission_Log`.`ip`,
                `Submission_Log`.`time`
            FROM
                `Submission_Log`
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
            $allData[] = new \OmegaUp\DAO\VO\SubmissionLog(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\SubmissionLog}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\SubmissionLog $Submission_Log El
     * objeto de tipo {@link \OmegaUp\DAO\VO\SubmissionLog}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\SubmissionLog $Submission_Log
    ): int {
        $sql = '
            INSERT INTO
                `Submission_Log` (
                    `problemset_id`,
                    `submission_id`,
                    `user_id`,
                    `identity_id`,
                    `ip`,
                    `time`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Submission_Log->problemset_id) ?
                null :
                intval($Submission_Log->problemset_id)
            ),
            (
                is_null($Submission_Log->submission_id) ?
                null :
                intval($Submission_Log->submission_id)
            ),
            (
                is_null($Submission_Log->user_id) ?
                null :
                intval($Submission_Log->user_id)
            ),
            (
                is_null($Submission_Log->identity_id) ?
                null :
                intval($Submission_Log->identity_id)
            ),
            (
                is_null($Submission_Log->ip) ?
                null :
                intval($Submission_Log->ip)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Submission_Log->time
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
