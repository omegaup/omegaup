<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Courses Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Courses}.
 * @access public
 * @abstract
 */
abstract class Courses {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Courses $Courses El objeto de tipo Courses a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Courses $Courses
    ): int {
        $sql = '
            UPDATE
                `Courses`
            SET
                `name` = ?,
                `description` = ?,
                `alias` = ?,
                `group_id` = ?,
                `acl_id` = ?,
                `start_time` = ?,
                `finish_time` = ?,
                `admission_mode` = ?,
                `school_id` = ?,
                `needs_basic_information` = ?,
                `requests_user_information` = ?,
                `show_scoreboard` = ?,
                `languages` = ?,
                `archived` = ?
            WHERE
                (
                    `course_id` = ?
                );';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            (
                is_null($Courses->group_id) ?
                null :
                intval($Courses->group_id)
            ),
            (
                is_null($Courses->acl_id) ?
                null :
                intval($Courses->acl_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Courses->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Courses->finish_time
            ),
            $Courses->admission_mode,
            (
                is_null($Courses->school_id) ?
                null :
                intval($Courses->school_id)
            ),
            intval($Courses->needs_basic_information),
            $Courses->requests_user_information,
            intval($Courses->show_scoreboard),
            $Courses->languages,
            intval($Courses->archived),
            intval($Courses->course_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Courses} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Courses}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Courses Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Courses} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $course_id
    ): ?\OmegaUp\DAO\VO\Courses {
        $sql = '
            SELECT
                `Courses`.`course_id`,
                `Courses`.`name`,
                `Courses`.`description`,
                `Courses`.`alias`,
                `Courses`.`group_id`,
                `Courses`.`acl_id`,
                `Courses`.`start_time`,
                `Courses`.`finish_time`,
                `Courses`.`admission_mode`,
                `Courses`.`school_id`,
                `Courses`.`needs_basic_information`,
                `Courses`.`requests_user_information`,
                `Courses`.`show_scoreboard`,
                `Courses`.`languages`,
                `Courses`.`archived`
            FROM
                `Courses`
            WHERE
                (
                    `course_id` = ?
                )
            LIMIT 1;';
        $params = [$course_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Courses($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Courses} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Courses $Courses El
     * objeto de tipo \OmegaUp\DAO\VO\Courses a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Courses $Courses
    ): void {
        $sql = '
            DELETE FROM
                `Courses`
            WHERE
                (
                    `course_id` = ?
                );';
        $params = [
            $Courses->course_id
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
     * {@link \OmegaUp\DAO\VO\Courses}.
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
     * @return list<\OmegaUp\DAO\VO\Courses> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Courses}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Courses`.`course_id`,
                `Courses`.`name`,
                `Courses`.`description`,
                `Courses`.`alias`,
                `Courses`.`group_id`,
                `Courses`.`acl_id`,
                `Courses`.`start_time`,
                `Courses`.`finish_time`,
                `Courses`.`admission_mode`,
                `Courses`.`school_id`,
                `Courses`.`needs_basic_information`,
                `Courses`.`requests_user_information`,
                `Courses`.`show_scoreboard`,
                `Courses`.`languages`,
                `Courses`.`archived`
            FROM
                `Courses`
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
            $allData[] = new \OmegaUp\DAO\VO\Courses(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Courses}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Courses $Courses El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Courses}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Courses $Courses
    ): int {
        $sql = '
            INSERT INTO
                `Courses` (
                    `name`,
                    `description`,
                    `alias`,
                    `group_id`,
                    `acl_id`,
                    `start_time`,
                    `finish_time`,
                    `admission_mode`,
                    `school_id`,
                    `needs_basic_information`,
                    `requests_user_information`,
                    `show_scoreboard`,
                    `languages`,
                    `archived`
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
                    ?
                );';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            (
                is_null($Courses->group_id) ?
                null :
                intval($Courses->group_id)
            ),
            (
                is_null($Courses->acl_id) ?
                null :
                intval($Courses->acl_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Courses->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Courses->finish_time
            ),
            $Courses->admission_mode,
            (
                is_null($Courses->school_id) ?
                null :
                intval($Courses->school_id)
            ),
            intval($Courses->needs_basic_information),
            $Courses->requests_user_information,
            intval($Courses->show_scoreboard),
            $Courses->languages,
            intval($Courses->archived),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Courses->course_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
