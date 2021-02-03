<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** UserRank Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRank}.
 * @access public
 * @abstract
 */
abstract class UserRank {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\UserRank}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\UserRank $User_Rank El
     * objeto de tipo {@link \OmegaUp\DAO\VO\UserRank}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\UserRank $User_Rank
    ): int {
        if (
            empty($User_Rank->user_id)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                User_Rank (
                    `user_id`,
                    `ranking`,
                    `problems_solved_count`,
                    `score`,
                    `username`,
                    `name`,
                    `country_id`,
                    `state_id`,
                    `school_id`,
                    `author_score`,
                    `author_ranking`
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
                    ?
                );';
        $params = [
            $User_Rank->user_id,
            (
                !is_null($User_Rank->ranking) ?
                intval($User_Rank->ranking) :
                null
            ),
            intval($User_Rank->problems_solved_count),
            floatval($User_Rank->score),
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            (
                !is_null($User_Rank->school_id) ?
                intval($User_Rank->school_id) :
                null
            ),
            floatval($User_Rank->author_score),
            (
                !is_null($User_Rank->author_ranking) ?
                intval($User_Rank->author_ranking) :
                null
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\UserRank $User_Rank El objeto de tipo UserRank a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\UserRank $User_Rank
    ): int {
        $sql = '
            UPDATE
                `User_Rank`
            SET
                `ranking` = ?,
                `problems_solved_count` = ?,
                `score` = ?,
                `username` = ?,
                `name` = ?,
                `country_id` = ?,
                `state_id` = ?,
                `school_id` = ?,
                `author_score` = ?,
                `author_ranking` = ?
            WHERE
                (
                    `user_id` = ?
                );';
        $params = [
            (
                is_null($User_Rank->ranking) ?
                null :
                intval($User_Rank->ranking)
            ),
            intval($User_Rank->problems_solved_count),
            floatval($User_Rank->score),
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            (
                is_null($User_Rank->school_id) ?
                null :
                intval($User_Rank->school_id)
            ),
            floatval($User_Rank->author_score),
            (
                is_null($User_Rank->author_ranking) ?
                null :
                intval($User_Rank->author_ranking)
            ),
            (
                is_null($User_Rank->user_id) ?
                null :
                intval($User_Rank->user_id)
            ),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\UserRank} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\UserRank}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\UserRank Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\UserRank} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        ?int $user_id
    ): ?\OmegaUp\DAO\VO\UserRank {
        $sql = '
            SELECT
                `User_Rank`.`user_id`,
                `User_Rank`.`ranking`,
                `User_Rank`.`problems_solved_count`,
                `User_Rank`.`score`,
                `User_Rank`.`username`,
                `User_Rank`.`name`,
                `User_Rank`.`country_id`,
                `User_Rank`.`state_id`,
                `User_Rank`.`school_id`,
                `User_Rank`.`author_score`,
                `User_Rank`.`author_ranking`
            FROM
                `User_Rank`
            WHERE
                (
                    `user_id` = ?
                )
            LIMIT 1;';
        $params = [$user_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\UserRank($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\UserRank} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\UserRank $User_Rank El
     * objeto de tipo \OmegaUp\DAO\VO\UserRank a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\UserRank $User_Rank
    ): void {
        $sql = '
            DELETE FROM
                `User_Rank`
            WHERE
                (
                    `user_id` = ?
                );';
        $params = [
            $User_Rank->user_id
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
     * {@link \OmegaUp\DAO\VO\UserRank}.
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
     * @return list<\OmegaUp\DAO\VO\UserRank> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\UserRank}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `User_Rank`.`user_id`,
                `User_Rank`.`ranking`,
                `User_Rank`.`problems_solved_count`,
                `User_Rank`.`score`,
                `User_Rank`.`username`,
                `User_Rank`.`name`,
                `User_Rank`.`country_id`,
                `User_Rank`.`state_id`,
                `User_Rank`.`school_id`,
                `User_Rank`.`author_score`,
                `User_Rank`.`author_ranking`
            FROM
                `User_Rank`
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
            $allData[] = new \OmegaUp\DAO\VO\UserRank(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\UserRank}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\UserRank $User_Rank El
     * objeto de tipo {@link \OmegaUp\DAO\VO\UserRank}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\UserRank $User_Rank
    ): int {
        $sql = '
            INSERT INTO
                `User_Rank` (
                    `user_id`,
                    `ranking`,
                    `problems_solved_count`,
                    `score`,
                    `username`,
                    `name`,
                    `country_id`,
                    `state_id`,
                    `school_id`,
                    `author_score`,
                    `author_ranking`
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
                    ?
                );';
        $params = [
            (
                is_null($User_Rank->user_id) ?
                null :
                intval($User_Rank->user_id)
            ),
            (
                is_null($User_Rank->ranking) ?
                null :
                intval($User_Rank->ranking)
            ),
            intval($User_Rank->problems_solved_count),
            floatval($User_Rank->score),
            $User_Rank->username,
            $User_Rank->name,
            $User_Rank->country_id,
            $User_Rank->state_id,
            (
                is_null($User_Rank->school_id) ?
                null :
                intval($User_Rank->school_id)
            ),
            floatval($User_Rank->author_score),
            (
                is_null($User_Rank->author_ranking) ?
                null :
                intval($User_Rank->author_ranking)
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
