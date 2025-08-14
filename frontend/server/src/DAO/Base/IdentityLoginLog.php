<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** IdentityLoginLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\IdentityLoginLog}.
 * @access public
 * @abstract
 */
abstract class IdentityLoginLog {
    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\IdentityLoginLog}.
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
     * @return list<\OmegaUp\DAO\VO\IdentityLoginLog> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\IdentityLoginLog}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = '`Identity_Login_Log`.`identity_id`',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sql = '
            SELECT
                `Identity_Login_Log`.`identity_id`,
                `Identity_Login_Log`.`ip`,
                `Identity_Login_Log`.`time`
            FROM
                `Identity_Login_Log`
        ';
        $sql .= (
            ' ORDER BY `' .
            \OmegaUp\MySQLConnection::getInstance()->escape($orden) .
            '` ' .
            ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC')
        );
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
            $allData[] = new \OmegaUp\DAO\VO\IdentityLoginLog(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\IdentityLoginLog}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\IdentityLoginLog $Identity_Login_Log El
     * objeto de tipo {@link \OmegaUp\DAO\VO\IdentityLoginLog}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\IdentityLoginLog $Identity_Login_Log
    ): int {
        $sql = '
            INSERT INTO
                `Identity_Login_Log` (
                    `identity_id`,
                    `ip`,
                    `time`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                is_null($Identity_Login_Log->identity_id) ?
                null :
                intval($Identity_Login_Log->identity_id)
            ),
            (
                is_null($Identity_Login_Log->ip) ?
                null :
                intval($Identity_Login_Log->ip)
            ),
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                $Identity_Login_Log->time
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
