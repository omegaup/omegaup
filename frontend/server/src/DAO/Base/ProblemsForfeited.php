<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** ProblemsForfeited Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsForfeited}.
 * @access public
 * @abstract
 */
abstract class ProblemsForfeited {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\ProblemsForfeited}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsForfeited}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited
    ): int {
        if (
            empty($Problems_Forfeited->user_id) ||
            empty($Problems_Forfeited->problem_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                Problems_Forfeited (
                    `user_id`,
                    `problem_id`,
                    `forfeited_date`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            $Problems_Forfeited->user_id,
            $Problems_Forfeited->problem_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problems_Forfeited->forfeited_date
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited El objeto de tipo ProblemsForfeited a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited
    ): int {
        $sql = '
            UPDATE
                `Problems_Forfeited`
            SET
                `forfeited_date` = ?
            WHERE
                (
                    `user_id` = ? AND
                    `problem_id` = ?
                );';
        $params = [
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problems_Forfeited->forfeited_date
            ),
            (
                is_null($Problems_Forfeited->user_id) ?
                null :
                intval($Problems_Forfeited->user_id)
            ),
            (
                is_null($Problems_Forfeited->problem_id) ?
                null :
                intval($Problems_Forfeited->problem_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\ProblemsForfeited} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\ProblemsForfeited}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\ProblemsForfeited Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsForfeited} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $user_id,
        ?int $problem_id
    ): ?\OmegaUp\DAO\VO\ProblemsForfeited {
        $sql = '
            SELECT
                `Problems_Forfeited`.`user_id`,
                `Problems_Forfeited`.`problem_id`,
                `Problems_Forfeited`.`forfeited_date`
            FROM
                `Problems_Forfeited`
            WHERE
                (
                    `user_id` = ? AND
                    `problem_id` = ?
                )
            LIMIT 1;';
        $params = [$user_id, $problem_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\ProblemsForfeited($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\ProblemsForfeited} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited El
     * objeto de tipo \OmegaUp\DAO\VO\ProblemsForfeited a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited
    ): void {
        $sql = '
            DELETE FROM
                `Problems_Forfeited`
            WHERE
                (
                    `user_id` = ? AND
                    `problem_id` = ?
                );';
        $params = [
            $Problems_Forfeited->user_id,
            $Problems_Forfeited->problem_id
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
     * {@link \OmegaUp\DAO\VO\ProblemsForfeited}.
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
     * @return list<\OmegaUp\DAO\VO\ProblemsForfeited> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\ProblemsForfeited}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Problems_Forfeited`.`user_id`,
                `Problems_Forfeited`.`problem_id`,
                `Problems_Forfeited`.`forfeited_date`
            FROM
                `Problems_Forfeited`
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
            $allData[] = new \OmegaUp\DAO\VO\ProblemsForfeited(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\ProblemsForfeited}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited El
     * objeto de tipo {@link \OmegaUp\DAO\VO\ProblemsForfeited}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\ProblemsForfeited $Problems_Forfeited
    ): int {
        $sql = '
            INSERT INTO
                `Problems_Forfeited` (
                    `user_id`,
                    `problem_id`,
                    `forfeited_date`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Problems_Forfeited->user_id) ?
                null :
                intval($Problems_Forfeited->user_id)
            ),
            (
                is_null($Problems_Forfeited->problem_id) ?
                null :
                intval($Problems_Forfeited->problem_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Problems_Forfeited->forfeited_date
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
