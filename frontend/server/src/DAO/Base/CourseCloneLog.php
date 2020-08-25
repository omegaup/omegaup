<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** CourseCloneLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CourseCloneLog}.
 * @access public
 * @abstract
 */
abstract class CourseCloneLog {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log El objeto de tipo CourseCloneLog a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log
    ): int {
        $sql = '
            UPDATE
                `Course_Clone_Log`
            SET
                `ip` = ?,
                `course_id` = ?,
                `new_course_id` = ?,
                `token_payload` = ?,
                `timestamp` = ?,
                `user_id` = ?,
                `result` = ?
            WHERE
                (
                    `course_clone_log_id` = ?
                );';
        $params = [
            $Course_Clone_Log->ip,
            (
                is_null($Course_Clone_Log->course_id) ?
                null :
                intval($Course_Clone_Log->course_id)
            ),
            (
                is_null($Course_Clone_Log->new_course_id) ?
                null :
                intval($Course_Clone_Log->new_course_id)
            ),
            $Course_Clone_Log->token_payload,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Clone_Log->timestamp
            ),
            (
                is_null($Course_Clone_Log->user_id) ?
                null :
                intval($Course_Clone_Log->user_id)
            ),
            $Course_Clone_Log->result,
            intval($Course_Clone_Log->course_clone_log_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\CourseCloneLog} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\CourseCloneLog}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\CourseCloneLog Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\CourseCloneLog} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $course_clone_log_id
    ): ?\OmegaUp\DAO\VO\CourseCloneLog {
        $sql = '
            SELECT
                `Course_Clone_Log`.`course_clone_log_id`,
                `Course_Clone_Log`.`ip`,
                `Course_Clone_Log`.`course_id`,
                `Course_Clone_Log`.`new_course_id`,
                `Course_Clone_Log`.`token_payload`,
                `Course_Clone_Log`.`timestamp`,
                `Course_Clone_Log`.`user_id`,
                `Course_Clone_Log`.`result`
            FROM
                `Course_Clone_Log`
            WHERE
                (
                    `course_clone_log_id` = ?
                )
            LIMIT 1;';
        $params = [$course_clone_log_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CourseCloneLog($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\CourseCloneLog} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log El
     * objeto de tipo \OmegaUp\DAO\VO\CourseCloneLog a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log
    ): void {
        $sql = '
            DELETE FROM
                `Course_Clone_Log`
            WHERE
                (
                    `course_clone_log_id` = ?
                );';
        $params = [
            $Course_Clone_Log->course_clone_log_id
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
     * {@link \OmegaUp\DAO\VO\CourseCloneLog}.
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
     * @return list<\OmegaUp\DAO\VO\CourseCloneLog> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\CourseCloneLog}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Course_Clone_Log`.`course_clone_log_id`,
                `Course_Clone_Log`.`ip`,
                `Course_Clone_Log`.`course_id`,
                `Course_Clone_Log`.`new_course_id`,
                `Course_Clone_Log`.`token_payload`,
                `Course_Clone_Log`.`timestamp`,
                `Course_Clone_Log`.`user_id`,
                `Course_Clone_Log`.`result`
            FROM
                `Course_Clone_Log`
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
            $allData[] = new \OmegaUp\DAO\VO\CourseCloneLog(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\CourseCloneLog}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CourseCloneLog}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\CourseCloneLog $Course_Clone_Log
    ): int {
        $sql = '
            INSERT INTO
                `Course_Clone_Log` (
                    `ip`,
                    `course_id`,
                    `new_course_id`,
                    `token_payload`,
                    `timestamp`,
                    `user_id`,
                    `result`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Course_Clone_Log->ip,
            (
                is_null($Course_Clone_Log->course_id) ?
                null :
                intval($Course_Clone_Log->course_id)
            ),
            (
                is_null($Course_Clone_Log->new_course_id) ?
                null :
                intval($Course_Clone_Log->new_course_id)
            ),
            $Course_Clone_Log->token_payload,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Clone_Log->timestamp
            ),
            (
                is_null($Course_Clone_Log->user_id) ?
                null :
                intval($Course_Clone_Log->user_id)
            ),
            $Course_Clone_Log->result,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Course_Clone_Log->course_clone_log_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
