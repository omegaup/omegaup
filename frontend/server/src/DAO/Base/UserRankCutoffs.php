<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** UserRankCutoffs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRankCutoffs}.
 * @access public
 * @abstract
 */
abstract class UserRankCutoffs {
    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\UserRankCutoffs}.
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
     * @return list<\OmegaUp\DAO\VO\UserRankCutoffs> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\UserRankCutoffs}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = 'score',
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
                `User_Rank_Cutoffs`.`score`,
                `User_Rank_Cutoffs`.`percentile`,
                `User_Rank_Cutoffs`.`classname`
            FROM
                `User_Rank_Cutoffs`
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
            $allData[] = new \OmegaUp\DAO\VO\UserRankCutoffs(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\UserRankCutoffs}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\UserRankCutoffs $User_Rank_Cutoffs El
     * objeto de tipo {@link \OmegaUp\DAO\VO\UserRankCutoffs}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\UserRankCutoffs $User_Rank_Cutoffs
    ): int {
        $sql = '
            INSERT INTO
                `User_Rank_Cutoffs` (
                    `score`,
                    `percentile`,
                    `classname`
                ) VALUES (
                    ?,
                    ?,
                    ?
                );';
        $params = [
            (
                $User_Rank_Cutoffs->score === null ?
                null :
                floatval($User_Rank_Cutoffs->score)
            ),
            (
                $User_Rank_Cutoffs->percentile === null ?
                null :
                floatval($User_Rank_Cutoffs->percentile)
            ),
            $User_Rank_Cutoffs->classname,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
