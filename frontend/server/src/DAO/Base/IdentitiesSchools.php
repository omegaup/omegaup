<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** IdentitiesSchools Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\IdentitiesSchools}.
 * @access public
 * @abstract
 */
abstract class IdentitiesSchools {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools El objeto de tipo IdentitiesSchools a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools
    ): int {
        $sql = '
            UPDATE
                `Identities_Schools`
            SET
                `identity_id` = ?,
                `school_id` = ?,
                `graduation_date` = ?,
                `creation_time` = ?,
                `end_time` = ?
            WHERE
                (
                    `identity_school_id` = ?
                );';
        $params = [
            (
                $Identities_Schools->identity_id === null ?
                null :
                intval($Identities_Schools->identity_id)
            ),
            (
                $Identities_Schools->school_id === null ?
                null :
                intval($Identities_Schools->school_id)
            ),
            $Identities_Schools->graduation_date,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Identities_Schools->creation_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Identities_Schools->end_time
            ),
            intval($Identities_Schools->identity_school_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\IdentitiesSchools} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\IdentitiesSchools}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\IdentitiesSchools Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\IdentitiesSchools} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $identity_school_id
    ): ?\OmegaUp\DAO\VO\IdentitiesSchools {
        $sql = '
            SELECT
                `Identities_Schools`.`identity_school_id`,
                `Identities_Schools`.`identity_id`,
                `Identities_Schools`.`school_id`,
                `Identities_Schools`.`graduation_date`,
                `Identities_Schools`.`creation_time`,
                `Identities_Schools`.`end_time`
            FROM
                `Identities_Schools`
            WHERE
                (
                    `identity_school_id` = ?
                )
            LIMIT 1;';
        $params = [$identity_school_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\IdentitiesSchools($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\IdentitiesSchools} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\IdentitiesSchools}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $identity_school_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Identities_Schools`
            WHERE
                (
                    `identity_school_id` = ?
                );';
        $params = [$identity_school_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `Identities_Schools`.
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
                `Identities_Schools`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\IdentitiesSchools} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools El
     * objeto de tipo \OmegaUp\DAO\VO\IdentitiesSchools a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools
    ): void {
        $sql = '
            DELETE FROM
                `Identities_Schools`
            WHERE
                (
                    `identity_school_id` = ?
                );';
        $params = [
            $Identities_Schools->identity_school_id
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
     * {@link \OmegaUp\DAO\VO\IdentitiesSchools}.
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
     * @return list<\OmegaUp\DAO\VO\IdentitiesSchools> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\IdentitiesSchools}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'identity_school_id',
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
                `Identities_Schools`.`identity_school_id`,
                `Identities_Schools`.`identity_id`,
                `Identities_Schools`.`school_id`,
                `Identities_Schools`.`graduation_date`,
                `Identities_Schools`.`creation_time`,
                `Identities_Schools`.`end_time`
            FROM
                `Identities_Schools`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
        if ($pagina !== null) {
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
            $allData[] = new \OmegaUp\DAO\VO\IdentitiesSchools(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\IdentitiesSchools}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools El
     * objeto de tipo {@link \OmegaUp\DAO\VO\IdentitiesSchools}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\IdentitiesSchools $Identities_Schools
    ): int {
        $sql = '
            INSERT INTO
                `Identities_Schools` (
                    `identity_id`,
                    `school_id`,
                    `graduation_date`,
                    `creation_time`,
                    `end_time`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                $Identities_Schools->identity_id === null ?
                null :
                intval($Identities_Schools->identity_id)
            ),
            (
                $Identities_Schools->school_id === null ?
                null :
                intval($Identities_Schools->school_id)
            ),
            $Identities_Schools->graduation_date,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Identities_Schools->creation_time
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Identities_Schools->end_time
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Identities_Schools->identity_school_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
