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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link UserRankCutoffs }.
 * @access public
 * @abstract
 *
 */
abstract class UserRankCutoffsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link UserRankCutoffs} pasado en la base de datos.
     * save() siempre creara una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(UserRankCutoffs $User_Rank_Cutoffs) {
        return UserRankCutoffsDAOBase::create($User_Rank_Cutoffs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link UserRankCutoffs}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link UserRankCutoffs}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `User_Rank_Cutoffs`.`score`, `User_Rank_Cutoffs`.`percentile`, `User_Rank_Cutoffs`.`classname` from User_Rank_Cutoffs';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new UserRankCutoffs($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto UserRankCutoffs suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto UserRankCutoffs dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param UserRankCutoffs [$User_Rank_Cutoffs] El objeto de tipo UserRankCutoffs a crear.
     */
    final private static function create(UserRankCutoffs $User_Rank_Cutoffs) {
        $sql = 'INSERT INTO User_Rank_Cutoffs (`score`, `percentile`, `classname`) VALUES (?, ?, ?);';
        $params = [
            $User_Rank_Cutoffs->score,
            $User_Rank_Cutoffs->percentile,
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
