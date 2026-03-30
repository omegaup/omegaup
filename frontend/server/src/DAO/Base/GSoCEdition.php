<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** GSoCEdition Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GSoCEdition}.
 * @access public
 * @abstract
 */
abstract class GSoCEdition {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition El objeto de tipo GSoCEdition a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition
    ): int {
        $sql = '
            UPDATE
                `GSoC_Edition`
            SET
                `year` = ?,
                `is_active` = ?,
                `application_deadline` = ?,
                `created_at` = ?,
                `updated_at` = ?
            WHERE
                (
                    `edition_id` = ?
                );';
        $params = [
            (
                is_null($GSoC_Edition->year) ?
                null :
                intval($GSoC_Edition->year)
            ),
            intval($GSoC_Edition->is_active),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->application_deadline
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->updated_at
            ),
            intval($GSoC_Edition->edition_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\GSoCEdition} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\GSoCEdition}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\GSoCEdition Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\GSoCEdition} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $edition_id
    ): ?\OmegaUp\DAO\VO\GSoCEdition {
        $sql = '
            SELECT
                `GSoC_Edition`.`edition_id`,
                `GSoC_Edition`.`year`,
                `GSoC_Edition`.`is_active`,
                `GSoC_Edition`.`application_deadline`,
                `GSoC_Edition`.`created_at`,
                `GSoC_Edition`.`updated_at`
            FROM
                `GSoC_Edition`
            WHERE
                (
                    `edition_id` = ?
                )
            LIMIT 1;';
        $params = [$edition_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\GSoCEdition($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\GSoCEdition} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\GSoCEdition}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        int $edition_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `GSoC_Edition`
            WHERE
                (
                    `edition_id` = ?
                );';
        $params = [$edition_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `GSoC_Edition`.
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
                `GSoC_Edition`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\GSoCEdition} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition El
     * objeto de tipo \OmegaUp\DAO\VO\GSoCEdition a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition
    ): void {
        $sql = '
            DELETE FROM
                `GSoC_Edition`
            WHERE
                (
                    `edition_id` = ?
                );';
        $params = [
            $GSoC_Edition->edition_id
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
     * {@link \OmegaUp\DAO\VO\GSoCEdition}.
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
     * @return list<\OmegaUp\DAO\VO\GSoCEdition> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\GSoCEdition}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'edition_id',
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
                `GSoC_Edition`.`edition_id`,
                `GSoC_Edition`.`year`,
                `GSoC_Edition`.`is_active`,
                `GSoC_Edition`.`application_deadline`,
                `GSoC_Edition`.`created_at`,
                `GSoC_Edition`.`updated_at`
            FROM
                `GSoC_Edition`
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
            $allData[] = new \OmegaUp\DAO\VO\GSoCEdition(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\GSoCEdition}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition El
     * objeto de tipo {@link \OmegaUp\DAO\VO\GSoCEdition}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\GSoCEdition $GSoC_Edition
    ): int {
        $sql = '
            INSERT INTO
                `GSoC_Edition` (
                    `year`,
                    `is_active`,
                    `application_deadline`,
                    `created_at`,
                    `updated_at`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($GSoC_Edition->year) ?
                null :
                intval($GSoC_Edition->year)
            ),
            intval($GSoC_Edition->is_active),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->application_deadline
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->created_at
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $GSoC_Edition->updated_at
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $GSoC_Edition->edition_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
