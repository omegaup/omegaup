<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** APITokens Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\APITokens}.
 * @access public
 * @abstract
 */
abstract class APITokens {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\APITokens $API_Tokens El objeto de tipo APITokens a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\APITokens $API_Tokens
    ): int {
        $sql = '
            UPDATE
                `API_Tokens`
            SET
                `user_id` = ?,
                `timestamp` = ?,
                `name` = ?,
                `token` = ?,
                `last_used` = ?,
                `use_count` = ?
            WHERE
                (
                    `apitoken_id` = ?
                );';
        $params = [
            (
                is_null($API_Tokens->user_id) ?
                null :
                intval($API_Tokens->user_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $API_Tokens->timestamp
            ),
            $API_Tokens->name,
            $API_Tokens->token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $API_Tokens->last_used
            ),
            intval($API_Tokens->use_count),
            intval($API_Tokens->apitoken_id),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\APITokens} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\APITokens}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\APITokens Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\APITokens} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        int $apitoken_id
    ): ?\OmegaUp\DAO\VO\APITokens {
        $sql = '
            SELECT
                `API_Tokens`.`apitoken_id`,
                `API_Tokens`.`user_id`,
                `API_Tokens`.`timestamp`,
                `API_Tokens`.`name`,
                `API_Tokens`.`token`,
                `API_Tokens`.`last_used`,
                `API_Tokens`.`use_count`
            FROM
                `API_Tokens`
            WHERE
                (
                    `apitoken_id` = ?
                )
            LIMIT 1;';
        $params = [$apitoken_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\APITokens($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\APITokens} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\APITokens $API_Tokens El
     * objeto de tipo \OmegaUp\DAO\VO\APITokens a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\APITokens $API_Tokens
    ): void {
        $sql = '
            DELETE FROM
                `API_Tokens`
            WHERE
                (
                    `apitoken_id` = ?
                );';
        $params = [
            $API_Tokens->apitoken_id
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
     * {@link \OmegaUp\DAO\VO\APITokens}.
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
     * @return list<\OmegaUp\DAO\VO\APITokens> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\APITokens}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `API_Tokens`.`apitoken_id`,
                `API_Tokens`.`user_id`,
                `API_Tokens`.`timestamp`,
                `API_Tokens`.`name`,
                `API_Tokens`.`token`,
                `API_Tokens`.`last_used`,
                `API_Tokens`.`use_count`
            FROM
                `API_Tokens`
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
            $allData[] = new \OmegaUp\DAO\VO\APITokens(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\APITokens}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\APITokens $API_Tokens El
     * objeto de tipo {@link \OmegaUp\DAO\VO\APITokens}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\APITokens $API_Tokens
    ): int {
        $sql = '
            INSERT INTO
                `API_Tokens` (
                    `user_id`,
                    `timestamp`,
                    `name`,
                    `token`,
                    `last_used`,
                    `use_count`
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
                is_null($API_Tokens->user_id) ?
                null :
                intval($API_Tokens->user_id)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $API_Tokens->timestamp
            ),
            $API_Tokens->name,
            $API_Tokens->token,
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $API_Tokens->last_used
            ),
            intval($API_Tokens->use_count),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $API_Tokens->apitoken_id = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );

        return $affectedRows;
    }
}
