<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Problems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problems}.
 * @access public
 * @abstract
 */
abstract class Problems {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El objeto de tipo Problems a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Problems $Problems
    ): int {
        $sql = '
            UPDATE
                `Problems`
            SET
                `acl_id` = ?,
                `visibility` = ?,
                `title` = ?,
                `alias` = ?,
                `commit` = ?,
                `current_version` = ?,
                `languages` = ?,
                `input_limit` = ?,
                `visits` = ?,
                `submissions` = ?,
                `accepted` = ?,
                `difficulty` = ?,
                `creation_date` = ?,
                `source` = ?,
                `order` = ?,
                `deprecated` = ?,
                `email_clarifications` = ?,
                `quality` = ?,
                `quality_histogram` = ?,
                `difficulty_histogram` = ?,
                `quality_seal` = ?,
                `show_diff` = ?,
                `allow_user_add_tags` = ?
            WHERE
                (
                    `problem_id` = ?
                );';
        $params = [
            (
                is_null($Problems->acl_id) ?
                null :
                intval($Problems->acl_id)
            ),
            $Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            intval($Problems->input_limit),
            intval($Problems->visits),
            intval($Problems->submissions),
            intval($Problems->accepted),
            (
                is_null($Problems->difficulty) ?
                null :
                floatval($Problems->difficulty)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problems->creation_date
            ),
            $Problems->source,
            $Problems->order,
            intval($Problems->deprecated),
            intval($Problems->email_clarifications),
            (
                is_null($Problems->quality) ?
                null :
                floatval($Problems->quality)
            ),
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
            intval($Problems->quality_seal),
            $Problems->show_diff,
            intval($Problems->allow_user_add_tags),
            intval($Problems->problem_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Problems} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Problems}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Problems Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Problems} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $problem_id
    ): ?\OmegaUp\DAO\VO\Problems {
        $sql = '
            SELECT
                `Problems`.`problem_id`,
                `Problems`.`acl_id`,
                `Problems`.`visibility`,
                `Problems`.`title`,
                `Problems`.`alias`,
                `Problems`.`commit`,
                `Problems`.`current_version`,
                `Problems`.`languages`,
                `Problems`.`input_limit`,
                `Problems`.`visits`,
                `Problems`.`submissions`,
                `Problems`.`accepted`,
                `Problems`.`difficulty`,
                `Problems`.`creation_date`,
                `Problems`.`source`,
                `Problems`.`order`,
                `Problems`.`deprecated`,
                `Problems`.`email_clarifications`,
                `Problems`.`quality`,
                `Problems`.`quality_histogram`,
                `Problems`.`difficulty_histogram`,
                `Problems`.`quality_seal`,
                `Problems`.`show_diff`,
                `Problems`.`allow_user_add_tags`
            FROM
                `Problems`
            WHERE
                (
                    `problem_id` = ?
                )
            LIMIT 1;';
        $params = [$problem_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Problems($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Problems} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Problems}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $problem_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Problems`
            WHERE
                (
                    `problem_id` = ?
                );';
        $params = [$problem_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Problems`.
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
                `Problems`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Problems} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El
     * objeto de tipo \OmegaUp\DAO\VO\Problems a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Problems $Problems
    ): void {
        $sql = '
            DELETE FROM
                `Problems`
            WHERE
                (
                    `problem_id` = ?
                );';
        $params = [
            $Problems->problem_id
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
     * {@link \OmegaUp\DAO\VO\Problems}.
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
     * @return list<\OmegaUp\DAO\VO\Problems> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Problems}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'problem_id',
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
                `Problems`.`problem_id`,
                `Problems`.`acl_id`,
                `Problems`.`visibility`,
                `Problems`.`title`,
                `Problems`.`alias`,
                `Problems`.`commit`,
                `Problems`.`current_version`,
                `Problems`.`languages`,
                `Problems`.`input_limit`,
                `Problems`.`visits`,
                `Problems`.`submissions`,
                `Problems`.`accepted`,
                `Problems`.`difficulty`,
                `Problems`.`creation_date`,
                `Problems`.`source`,
                `Problems`.`order`,
                `Problems`.`deprecated`,
                `Problems`.`email_clarifications`,
                `Problems`.`quality`,
                `Problems`.`quality_histogram`,
                `Problems`.`difficulty_histogram`,
                `Problems`.`quality_seal`,
                `Problems`.`show_diff`,
                `Problems`.`allow_user_add_tags`
            FROM
                `Problems`
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
            $allData[] = new \OmegaUp\DAO\VO\Problems(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Problems}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Problems $Problems El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Problems}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Problems $Problems
    ): int {
        $sql = '
            INSERT INTO
                `Problems` (
                    `acl_id`,
                    `visibility`,
                    `title`,
                    `alias`,
                    `commit`,
                    `current_version`,
                    `languages`,
                    `input_limit`,
                    `visits`,
                    `submissions`,
                    `accepted`,
                    `difficulty`,
                    `creation_date`,
                    `source`,
                    `order`,
                    `deprecated`,
                    `email_clarifications`,
                    `quality`,
                    `quality_histogram`,
                    `difficulty_histogram`,
                    `quality_seal`,
                    `show_diff`,
                    `allow_user_add_tags`
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
                    ?
                );';
        $params = [
            (
                is_null($Problems->acl_id) ?
                null :
                intval($Problems->acl_id)
            ),
            $Problems->visibility,
            $Problems->title,
            $Problems->alias,
            $Problems->commit,
            $Problems->current_version,
            $Problems->languages,
            intval($Problems->input_limit),
            intval($Problems->visits),
            intval($Problems->submissions),
            intval($Problems->accepted),
            (
                is_null($Problems->difficulty) ?
                null :
                floatval($Problems->difficulty)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problems->creation_date
            ),
            $Problems->source,
            $Problems->order,
            intval($Problems->deprecated),
            intval($Problems->email_clarifications),
            (
                is_null($Problems->quality) ?
                null :
                floatval($Problems->quality)
            ),
            $Problems->quality_histogram,
            $Problems->difficulty_histogram,
            intval($Problems->quality_seal),
            $Problems->show_diff,
            intval($Problems->allow_user_add_tags),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problems->problem_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
