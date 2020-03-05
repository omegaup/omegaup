<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** Schools Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Schools}.
 * @access public
 * @abstract
 */
abstract class Schools {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\Schools $Schools El objeto de tipo Schools a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\Schools $Schools
    ): int {
        $sql = '
            UPDATE
                `Schools`
            SET
                `country_id` = ?,
                `state_id` = ?,
                `name` = ?,
                `rank` = ?,
                `score` = ?
            WHERE
                (
                    `school_id` = ?
                );';
        $params = [
            $Schools->country_id,
            $Schools->state_id,
            $Schools->name,
            (
                is_null($Schools->rank) ?
                null :
                intval($Schools->rank)
            ),
            floatval($Schools->score),
            intval($Schools->school_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\Schools} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\Schools}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\Schools Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\Schools} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $school_id
    ): ?\OmegaUp\DAO\VO\Schools {
        $sql = '
            SELECT
                `Schools`.`school_id`,
                `Schools`.`country_id`,
                `Schools`.`state_id`,
                `Schools`.`name`,
                `Schools`.`rank`,
                `Schools`.`score`
            FROM
                `Schools`
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
        return new \OmegaUp\DAO\VO\Schools($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\Schools} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\Schools $Schools El
     * objeto de tipo \OmegaUp\DAO\VO\Schools a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\Schools $Schools
    ): void {
        $sql = '
            DELETE FROM
                `Schools`
            WHERE
                (
                    `school_id` = ?
                );';
        $params = [
            $Schools->school_id
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
     * {@link \OmegaUp\DAO\VO\Schools}.
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
     * @return list<\OmegaUp\DAO\VO\Schools> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\Schools}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Schools`.`school_id`,
                `Schools`.`country_id`,
                `Schools`.`state_id`,
                `Schools`.`name`,
                `Schools`.`rank`,
                `Schools`.`score`
            FROM
                `Schools`
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
            $allData[] = new \OmegaUp\DAO\VO\Schools(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\Schools}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\Schools $Schools El
     * objeto de tipo {@link \OmegaUp\DAO\VO\Schools}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\Schools $Schools
    ): int {
        $sql = '
            INSERT INTO
                `Schools` (
                    `country_id`,
                    `state_id`,
                    `name`,
                    `rank`,
                    `score`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Schools->country_id,
            $Schools->state_id,
            $Schools->name,
            (
                is_null($Schools->rank) ?
                null :
                intval($Schools->rank)
            ),
            floatval($Schools->score),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Schools->school_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
