<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** SchoolOfTheMonth Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}.
 * @access public
 * @abstract
 */
abstract class SchoolOfTheMonth {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month El objeto de tipo SchoolOfTheMonth a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month
    ): int {
        $sql = '
            UPDATE
                `School_Of_The_Month`
            SET
                `school_id` = ?,
                `time` = ?,
                `ranking` = ?,
                `selected_by` = ?,
                `score` = ?
            WHERE
                (
                    `school_of_the_month_id` = ?
                );';
        $params = [
            (
                is_null($School_Of_The_Month->school_id) ?
                null :
                intval($School_Of_The_Month->school_id)
            ),
            $School_Of_The_Month->time,
            (
                is_null($School_Of_The_Month->ranking) ?
                null :
                intval($School_Of_The_Month->ranking)
            ),
            (
                is_null($School_Of_The_Month->selected_by) ?
                null :
                intval($School_Of_The_Month->selected_by)
            ),
            floatval($School_Of_The_Month->score),
            intval($School_Of_The_Month->school_of_the_month_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\SchoolOfTheMonth} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\SchoolOfTheMonth Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\SchoolOfTheMonth} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $school_of_the_month_id
    ): ?\OmegaUp\DAO\VO\SchoolOfTheMonth {
        $sql = '
            SELECT
                `School_Of_The_Month`.`school_of_the_month_id`,
                `School_Of_The_Month`.`school_id`,
                `School_Of_The_Month`.`time`,
                `School_Of_The_Month`.`ranking`,
                `School_Of_The_Month`.`selected_by`,
                `School_Of_The_Month`.`score`
            FROM
                `School_Of_The_Month`
            WHERE
                (
                    `school_of_the_month_id` = ?
                )
            LIMIT 1;';
        $params = [$school_of_the_month_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SchoolOfTheMonth($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\SchoolOfTheMonth} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month El
     * objeto de tipo \OmegaUp\DAO\VO\SchoolOfTheMonth a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month
    ): void {
        $sql = '
            DELETE FROM
                `School_Of_The_Month`
            WHERE
                (
                    `school_of_the_month_id` = ?
                );';
        $params = [
            $School_Of_The_Month->school_of_the_month_id
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
     * {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}.
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
     * @return list<\OmegaUp\DAO\VO\SchoolOfTheMonth> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `School_Of_The_Month`.`school_of_the_month_id`,
                `School_Of_The_Month`.`school_id`,
                `School_Of_The_Month`.`time`,
                `School_Of_The_Month`.`ranking`,
                `School_Of_The_Month`.`selected_by`,
                `School_Of_The_Month`.`score`
            FROM
                `School_Of_The_Month`
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
            $allData[] = new \OmegaUp\DAO\VO\SchoolOfTheMonth(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month El
     * objeto de tipo {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\SchoolOfTheMonth $School_Of_The_Month
    ): int {
        $sql = '
            INSERT INTO
                `School_Of_The_Month` (
                    `school_id`,
                    `time`,
                    `ranking`,
                    `selected_by`,
                    `score`
                ) VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($School_Of_The_Month->school_id) ?
                null :
                intval($School_Of_The_Month->school_id)
            ),
            $School_Of_The_Month->time,
            (
                is_null($School_Of_The_Month->ranking) ?
                null :
                intval($School_Of_The_Month->ranking)
            ),
            (
                is_null($School_Of_The_Month->selected_by) ?
                null :
                intval($School_Of_The_Month->selected_by)
            ),
            floatval($School_Of_The_Month->score),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $School_Of_The_Month->school_of_the_month_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
