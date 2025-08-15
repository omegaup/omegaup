<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Assignments Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Assignments}.
 * @access public
 * @abstract
 */
abstract class Assignments {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Assignments $Assignments El objeto de tipo Assignments a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Assignments $Assignments
    ): int {
        $sql = '
            UPDATE
                `Assignments`
            SET
                `course_id` = ?,
                `problemset_id` = ?,
                `acl_id` = ?,
                `name` = ?,
                `description` = ?,
                `alias` = ?,
                `publish_time_delay` = ?,
                `assignment_type` = ?,
                `start_time` = ?,
                `finish_time` = ?,
                `max_points` = ?,
                `order` = ?
            WHERE
                (
                    `assignment_id` = ?
                );';
        $params = [
            (
                is_null($Assignments->course_id) ?
                null :
                intval($Assignments->course_id)
            ),
            (
                is_null($Assignments->problemset_id) ?
                null :
                intval($Assignments->problemset_id)
            ),
            (
                is_null($Assignments->acl_id) ?
                null :
                intval($Assignments->acl_id)
            ),
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            (
                is_null($Assignments->publish_time_delay) ?
                null :
                intval($Assignments->publish_time_delay)
            ),
            $Assignments->assignment_type,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Assignments->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Assignments->finish_time
            ),
            floatval($Assignments->max_points),
            intval($Assignments->order),
            intval($Assignments->assignment_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Assignments} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Assignments}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Assignments Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Assignments} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $assignment_id
    ): ?\OmegaUp\DAO\VO\Assignments {
        $sql = '
            SELECT
                `Assignments`.`assignment_id`,
                `Assignments`.`course_id`,
                `Assignments`.`problemset_id`,
                `Assignments`.`acl_id`,
                `Assignments`.`name`,
                `Assignments`.`description`,
                `Assignments`.`alias`,
                `Assignments`.`publish_time_delay`,
                `Assignments`.`assignment_type`,
                `Assignments`.`start_time`,
                `Assignments`.`finish_time`,
                `Assignments`.`max_points`,
                `Assignments`.`order`
            FROM
                `Assignments`
            WHERE
                (
                    `assignment_id` = ?
                )
            LIMIT 1;';
        $params = [$assignment_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Assignments} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Assignments}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $assignment_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Assignments`
            WHERE
                (
                    `assignment_id` = ?
                );';
        $params = [$assignment_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Assignments} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Assignments $Assignments El
     * objeto de tipo \OmegaUp\DAO\VO\Assignments a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Assignments $Assignments
    ): void {
        $sql = '
            DELETE FROM
                `Assignments`
            WHERE
                (
                    `assignment_id` = ?
                );';
        $params = [
            $Assignments->assignment_id
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
     * {@link \OmegaUp\DAO\VO\Assignments}.
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
     * @return list<\OmegaUp\DAO\VO\Assignments> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Assignments}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'assignment_id',
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
                `Assignments`.`assignment_id`,
                `Assignments`.`course_id`,
                `Assignments`.`problemset_id`,
                `Assignments`.`acl_id`,
                `Assignments`.`name`,
                `Assignments`.`description`,
                `Assignments`.`alias`,
                `Assignments`.`publish_time_delay`,
                `Assignments`.`assignment_type`,
                `Assignments`.`start_time`,
                `Assignments`.`finish_time`,
                `Assignments`.`max_points`,
                `Assignments`.`order`
            FROM
                `Assignments`
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
            $allData[] = new \OmegaUp\DAO\VO\Assignments(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Assignments}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Assignments $Assignments El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Assignments}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Assignments $Assignments
    ): int {
        $sql = '
            INSERT INTO
                `Assignments` (
                    `course_id`,
                    `problemset_id`,
                    `acl_id`,
                    `name`,
                    `description`,
                    `alias`,
                    `publish_time_delay`,
                    `assignment_type`,
                    `start_time`,
                    `finish_time`,
                    `max_points`,
                    `order`
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
                is_null($Assignments->course_id) ?
                null :
                intval($Assignments->course_id)
            ),
            (
                is_null($Assignments->problemset_id) ?
                null :
                intval($Assignments->problemset_id)
            ),
            (
                is_null($Assignments->acl_id) ?
                null :
                intval($Assignments->acl_id)
            ),
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            (
                is_null($Assignments->publish_time_delay) ?
                null :
                intval($Assignments->publish_time_delay)
            ),
            $Assignments->assignment_type,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Assignments->start_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Assignments->finish_time
            ),
            floatval($Assignments->max_points),
            intval($Assignments->order),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Assignments->assignment_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
