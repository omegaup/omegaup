<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** SchoolRank Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SchoolRank}.
 * @access public
 * @abstract
 */
abstract class SchoolRank {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\SchoolRank}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\SchoolRank $School_Rank El
     * objeto de tipo {@link \OmegaUp\DAO\VO\SchoolRank}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\SchoolRank $School_Rank
    ): int {
        if (
            empty($School_Rank->school_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                School_Rank (
                    `school_id`,
                    `ranking`,
                    `score`,
                    `country_id`,
                    `state_id`,
                    `timestamp`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $School_Rank->school_id,
            (
                !is_null($School_Rank->ranking) ?
                intval($School_Rank->ranking) :
                null
            ),
            floatval($School_Rank->score),
            $School_Rank->country_id,
            $School_Rank->state_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $School_Rank->timestamp
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\SchoolRank $School_Rank El objeto de tipo SchoolRank a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\SchoolRank $School_Rank
    ): int {
        $sql = '
            UPDATE
                `School_Rank`
            SET
                `ranking` = ?,
                `score` = ?,
                `country_id` = ?,
                `state_id` = ?,
                `timestamp` = ?
            WHERE
                (
                    `school_id` = ?
                );';
        $params = [
            (
                is_null($School_Rank->ranking) ?
                null :
                intval($School_Rank->ranking)
            ),
            floatval($School_Rank->score),
            $School_Rank->country_id,
            $School_Rank->state_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $School_Rank->timestamp
            ),
            (
                is_null($School_Rank->school_id) ?
                null :
                intval($School_Rank->school_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\SchoolRank} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\SchoolRank}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\SchoolRank Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\SchoolRank} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $school_id
    ): ?\OmegaUp\DAO\VO\SchoolRank {
        $sql = '
            SELECT
                `School_Rank`.`school_id`,
                `School_Rank`.`ranking`,
                `School_Rank`.`score`,
                `School_Rank`.`country_id`,
                `School_Rank`.`state_id`,
                `School_Rank`.`timestamp`
            FROM
                `School_Rank`
            WHERE
                (
                    `school_id` = ?
                )
            LIMIT 1;';
        $params = [$school_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SchoolRank($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\SchoolRank} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\SchoolRank}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        ?int $school_id
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `School_Rank`
            WHERE
                (
                    `school_id` = ?
                );';
        $params = [$school_id];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\SchoolRank} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\SchoolRank $School_Rank El
     * objeto de tipo \OmegaUp\DAO\VO\SchoolRank a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\SchoolRank $School_Rank
    ): void {
        $sql = '
            DELETE FROM
                `School_Rank`
            WHERE
                (
                    `school_id` = ?
                );';
        $params = [
            $School_Rank->school_id
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
     * {@link \OmegaUp\DAO\VO\SchoolRank}.
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
     * @return list<\OmegaUp\DAO\VO\SchoolRank> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\SchoolRank}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `School_Rank`.`school_id`,
                `School_Rank`.`ranking`,
                `School_Rank`.`score`,
                `School_Rank`.`country_id`,
                `School_Rank`.`state_id`,
                `School_Rank`.`timestamp`
            FROM
                `School_Rank`
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
            $allData[] = new \OmegaUp\DAO\VO\SchoolRank(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\SchoolRank}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\SchoolRank $School_Rank El
     * objeto de tipo {@link \OmegaUp\DAO\VO\SchoolRank}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\SchoolRank $School_Rank
    ): int {
        $sql = '
            INSERT INTO
                `School_Rank` (
                    `school_id`,
                    `ranking`,
                    `score`,
                    `country_id`,
                    `state_id`,
                    `timestamp`
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
                is_null($School_Rank->school_id) ?
                null :
                intval($School_Rank->school_id)
            ),
            (
                is_null($School_Rank->ranking) ?
                null :
                intval($School_Rank->ranking)
            ),
            floatval($School_Rank->score),
            $School_Rank->country_id,
            $School_Rank->state_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $School_Rank->timestamp
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
