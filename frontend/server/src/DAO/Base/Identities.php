<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Identities Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Identities}.
 * @access public
 * @abstract
 */
abstract class Identities {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El objeto de tipo Identities a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Identities $Identities
    ): int {
        $sql = '
            UPDATE
                `Identities`
            SET
                `username` = ?,
                `password` = ?,
                `name` = ?,
                `user_id` = ?,
                `language_id` = ?,
                `country_id` = ?,
                `state_id` = ?,
                `gender` = ?,
                `current_identity_school_id` = ?
            WHERE
                (
                    `identity_id` = ?
                );';
        $params = [
            $Identities->username,
            $Identities->password,
            $Identities->name,
            (
                is_null($Identities->user_id) ?
                null :
                intval($Identities->user_id)
            ),
            (
                is_null($Identities->language_id) ?
                null :
                intval($Identities->language_id)
            ),
            $Identities->country_id,
            $Identities->state_id,
            $Identities->gender,
            (
                is_null($Identities->current_identity_school_id) ?
                null :
                intval($Identities->current_identity_school_id)
            ),
            intval($Identities->identity_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Identities} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Identities}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Identities Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Identities} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $identity_id
    ): ?\OmegaUp\DAO\VO\Identities {
        $sql = '
            SELECT
                `Identities`.`identity_id`,
                `Identities`.`username`,
                `Identities`.`password`,
                `Identities`.`name`,
                `Identities`.`user_id`,
                `Identities`.`language_id`,
                `Identities`.`country_id`,
                `Identities`.`state_id`,
                `Identities`.`gender`,
                `Identities`.`current_identity_school_id`
            FROM
                `Identities`
            WHERE
                (
                    `identity_id` = ?
                )
            LIMIT 1;';
        $params = [$identity_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Identities($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\Identities} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\Identities}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $identity_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Identities`
            WHERE
                (
                    `identity_id` = ?
                );';
        $params = [$identity_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Identities} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El
     * objeto de tipo \OmegaUp\DAO\VO\Identities a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Identities $Identities
    ): void {
        $sql = '
            DELETE FROM
                `Identities`
            WHERE
                (
                    `identity_id` = ?
                );';
        $params = [
            $Identities->identity_id
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
     * {@link \OmegaUp\DAO\VO\Identities}.
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
     * @return list<\OmegaUp\DAO\VO\Identities> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Identities}.
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
                `Identities`.`identity_id`,
                `Identities`.`username`,
                `Identities`.`password`,
                `Identities`.`name`,
                `Identities`.`user_id`,
                `Identities`.`language_id`,
                `Identities`.`country_id`,
                `Identities`.`state_id`,
                `Identities`.`gender`,
                `Identities`.`current_identity_school_id`
            FROM
                `Identities`
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
            $allData[] = new \OmegaUp\DAO\VO\Identities(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Identities}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Identities $Identities El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Identities}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Identities $Identities
    ): int {
        $sql = '
            INSERT INTO
                `Identities` (
                    `username`,
                    `password`,
                    `name`,
                    `user_id`,
                    `language_id`,
                    `country_id`,
                    `state_id`,
                    `gender`,
                    `current_identity_school_id`
                ) VALUES (
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
            $Identities->username,
            $Identities->password,
            $Identities->name,
            (
                is_null($Identities->user_id) ?
                null :
                intval($Identities->user_id)
            ),
            (
                is_null($Identities->language_id) ?
                null :
                intval($Identities->language_id)
            ),
            $Identities->country_id,
            $Identities->state_id,
            $Identities->gender,
            (
                is_null($Identities->current_identity_school_id) ?
                null :
                intval($Identities->current_identity_school_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Identities->identity_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
