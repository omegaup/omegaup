<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** UserRankCutoffs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link UserRankCutoffs}.
 * @access public
 * @abstract
 *
 */
abstract class UserRankCutoffsDAOBase {
    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link UserRankCutoffs}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UserRankCutoffs}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `User_Rank_Cutoffs`.`score`, `User_Rank_Cutoffs`.`percentile`, `User_Rank_Cutoffs`.`classname` from User_Rank_Cutoffs';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new UserRankCutoffs($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRankCutoffs suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs a crear.
     */
    final public static function create(UserRankCutoffs $User_Rank_Cutoffs) {
        $sql = 'INSERT INTO User_Rank_Cutoffs (`score`, `percentile`, `classname`) VALUES (?, ?, ?);';
        $params = [
            is_null($User_Rank_Cutoffs->score) ? null : (float)$User_Rank_Cutoffs->score,
            is_null($User_Rank_Cutoffs->percentile) ? null : (float)$User_Rank_Cutoffs->percentile,
            $User_Rank_Cutoffs->classname,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
