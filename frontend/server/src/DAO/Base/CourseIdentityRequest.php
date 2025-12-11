<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** CourseIdentityRequest Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CourseIdentityRequest}.
 * @access public
 * @abstract
 */
abstract class CourseIdentityRequest {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\CourseIdentityRequest}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CourseIdentityRequest}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request
    ): int {
        if (
            empty($Course_Identity_Request->identity_id) ||
            empty($Course_Identity_Request->course_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Course_Identity_Request (
                    `identity_id`,
                    `course_id`,
                    `request_time`,
                    `last_update`,
                    `accepted`,
                    `extra_note`,
                    `accept_teacher`,
                    `share_user_information`
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
            $Course_Identity_Request->identity_id,
            $Course_Identity_Request->course_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->request_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->last_update
            ),
            (
                !is_null($Course_Identity_Request->accepted) ?
                intval($Course_Identity_Request->accepted) :
                null
            ),
            $Course_Identity_Request->extra_note,
            (
                !is_null($Course_Identity_Request->accept_teacher) ?
                intval($Course_Identity_Request->accept_teacher) :
                null
            ),
            (
                !is_null($Course_Identity_Request->share_user_information) ?
                intval($Course_Identity_Request->share_user_information) :
                null
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request El objeto de tipo CourseIdentityRequest a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request
    ): int {
        $sql = '
            UPDATE
                `Course_Identity_Request`
            SET
                `request_time` = ?,
                `last_update` = ?,
                `accepted` = ?,
                `extra_note` = ?,
                `accept_teacher` = ?,
                `share_user_information` = ?
            WHERE
                (
                    `identity_id` = ? AND
                    `course_id` = ?
                );';
        $params = [
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->request_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->last_update
            ),
            (
                is_null($Course_Identity_Request->accepted) ?
                null :
                intval($Course_Identity_Request->accepted)
            ),
            $Course_Identity_Request->extra_note,
            (
                is_null($Course_Identity_Request->accept_teacher) ?
                null :
                intval($Course_Identity_Request->accept_teacher)
            ),
            (
                is_null($Course_Identity_Request->share_user_information) ?
                null :
                intval($Course_Identity_Request->share_user_information)
            ),
            (
                is_null($Course_Identity_Request->identity_id) ?
                null :
                intval($Course_Identity_Request->identity_id)
            ),
            (
                is_null($Course_Identity_Request->course_id) ?
                null :
                intval($Course_Identity_Request->course_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\CourseIdentityRequest} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\CourseIdentityRequest}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\CourseIdentityRequest Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\CourseIdentityRequest} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $identity_id,
        ?int $course_id
    ): ?\OmegaUp\DAO\VO\CourseIdentityRequest {
        $sql = '
            SELECT
                `Course_Identity_Request`.`identity_id`,
                `Course_Identity_Request`.`course_id`,
                `Course_Identity_Request`.`request_time`,
                `Course_Identity_Request`.`last_update`,
                `Course_Identity_Request`.`accepted`,
                `Course_Identity_Request`.`extra_note`,
                `Course_Identity_Request`.`accept_teacher`,
                `Course_Identity_Request`.`share_user_information`
            FROM
                `Course_Identity_Request`
            WHERE
                (
                    `identity_id` = ? AND
                    `course_id` = ?
                )
            LIMIT 1;';
        $params = [$identity_id, $course_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\CourseIdentityRequest($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\CourseIdentityRequest} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\CourseIdentityRequest}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?int $identity_id,
        ?int $course_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Course_Identity_Request`
            WHERE
                (
                    `identity_id` = ? AND
                    `course_id` = ?
                );';
        $params = [$identity_id, $course_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Course_Identity_Request`.
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
                `Course_Identity_Request`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\CourseIdentityRequest} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request El
     * objeto de tipo \OmegaUp\DAO\VO\CourseIdentityRequest a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request
    ): void {
        $sql = '
            DELETE FROM
                `Course_Identity_Request`
            WHERE
                (
                    `identity_id` = ? AND
                    `course_id` = ?
                );';
        $params = [
            $Course_Identity_Request->identity_id,
            $Course_Identity_Request->course_id
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
     * {@link \OmegaUp\DAO\VO\CourseIdentityRequest}.
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
     * @return list<\OmegaUp\DAO\VO\CourseIdentityRequest> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\CourseIdentityRequest}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'identity_id',
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
                `Course_Identity_Request`.`identity_id`,
                `Course_Identity_Request`.`course_id`,
                `Course_Identity_Request`.`request_time`,
                `Course_Identity_Request`.`last_update`,
                `Course_Identity_Request`.`accepted`,
                `Course_Identity_Request`.`extra_note`,
                `Course_Identity_Request`.`accept_teacher`,
                `Course_Identity_Request`.`share_user_information`
            FROM
                `Course_Identity_Request`
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
            $allData[] = new \OmegaUp\DAO\VO\CourseIdentityRequest(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\CourseIdentityRequest}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request El
     * objeto de tipo {@link \OmegaUp\DAO\VO\CourseIdentityRequest}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\CourseIdentityRequest $Course_Identity_Request
    ): int {
        $sql = '
            INSERT INTO
                `Course_Identity_Request` (
                    `identity_id`,
                    `course_id`,
                    `request_time`,
                    `last_update`,
                    `accepted`,
                    `extra_note`,
                    `accept_teacher`,
                    `share_user_information`
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
            (
                is_null($Course_Identity_Request->identity_id) ?
                null :
                intval($Course_Identity_Request->identity_id)
            ),
            (
                is_null($Course_Identity_Request->course_id) ?
                null :
                intval($Course_Identity_Request->course_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->request_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Course_Identity_Request->last_update
            ),
            (
                is_null($Course_Identity_Request->accepted) ?
                null :
                intval($Course_Identity_Request->accepted)
            ),
            $Course_Identity_Request->extra_note,
            (
                is_null($Course_Identity_Request->accept_teacher) ?
                null :
                intval($Course_Identity_Request->accept_teacher)
            ),
            (
                is_null($Course_Identity_Request->share_user_information) ?
                null :
                intval($Course_Identity_Request->share_user_information)
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
