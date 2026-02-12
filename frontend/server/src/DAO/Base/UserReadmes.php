<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** UserReadmes Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserReadmes}.
 * @access public
 * @abstract
 */
abstract class UserReadmes {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\UserReadmes $User_Readmes El objeto de tipo UserReadmes a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\UserReadmes $User_Readmes
    ): int {
        $sql = '
            UPDATE
                `User_Readmes`
            SET
                `user_id` = ?,
                `content` = ?,
                `is_visible` = ?,
                `last_edit_time` = ?,
                `report_count` = ?,
                `is_disabled` = ?
            WHERE
                (
                    `readme_id` = ?
                );';
        $params = [
            (
                is_null($User_Readmes->user_id) ?
                null :
                intval($User_Readmes->user_id)
            ),
            $User_Readmes->content,
            intval($User_Readmes->is_visible),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $User_Readmes->last_edit_time
            ),
            intval($User_Readmes->report_count),
            intval($User_Readmes->is_disabled),
            intval($User_Readmes->readme_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\UserReadmes} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\UserReadmes}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\UserReadmes Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\UserReadmes} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $readme_id
    ): ?\OmegaUp\DAO\VO\UserReadmes {
        $sql = '
            SELECT
                `User_Readmes`.`readme_id`,
                `User_Readmes`.`user_id`,
                `User_Readmes`.`content`,
                `User_Readmes`.`is_visible`,
                `User_Readmes`.`last_edit_time`,
                `User_Readmes`.`report_count`,
                `User_Readmes`.`is_disabled`
            FROM
                `User_Readmes`
            WHERE
                (
                    `readme_id` = ?
                )
            LIMIT 1;';
        $params = [$readme_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\UserReadmes($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\UserReadmes} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\UserReadmes}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $readme_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `User_Readmes`
            WHERE
                (
                    `readme_id` = ?
                );';
        $params = [$readme_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `User_Readmes`.
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
                `User_Readmes`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\UserReadmes} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\UserReadmes $User_Readmes El
     * objeto de tipo \OmegaUp\DAO\VO\UserReadmes a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\UserReadmes $User_Readmes
    ): void {
        $sql = '
            DELETE FROM
                `User_Readmes`
            WHERE
                (
                    `readme_id` = ?
                );';
        $params = [
            $User_Readmes->readme_id
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
     * {@link \OmegaUp\DAO\VO\UserReadmes}.
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
     * @return list<\OmegaUp\DAO\VO\UserReadmes> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\UserReadmes}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'readme_id',
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
                `User_Readmes`.`readme_id`,
                `User_Readmes`.`user_id`,
                `User_Readmes`.`content`,
                `User_Readmes`.`is_visible`,
                `User_Readmes`.`last_edit_time`,
                `User_Readmes`.`report_count`,
                `User_Readmes`.`is_disabled`
            FROM
                `User_Readmes`
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
            $allData[] = new \OmegaUp\DAO\VO\UserReadmes(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\UserReadmes}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\UserReadmes $User_Readmes El
     * objeto de tipo {@link \OmegaUp\DAO\VO\UserReadmes}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\UserReadmes $User_Readmes
    ): int {
        $sql = '
            INSERT INTO
                `User_Readmes` (
                    `user_id`,
                    `content`,
                    `is_visible`,
                    `last_edit_time`,
                    `report_count`,
                    `is_disabled`
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
                is_null($User_Readmes->user_id) ?
                null :
                intval($User_Readmes->user_id)
            ),
            $User_Readmes->content,
            intval($User_Readmes->is_visible),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $User_Readmes->last_edit_time
            ),
            intval($User_Readmes->report_count),
            intval($User_Readmes->is_disabled),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $User_Readmes->readme_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
