<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Favorites Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Favorites}.
 * @access public
 * @abstract
 *
 */
abstract class FavoritesDAOBase {
    /**
     * Obtener {@link Favorites} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Favorites} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Favorites Un objeto del tipo {@link Favorites}. NULL si no hay tal registro.
     */
    final public static function getByPK($user_id, $problem_id) {
        if (is_null($user_id) || is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Favorites`.`user_id`, `Favorites`.`problem_id` FROM Favorites WHERE (user_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$user_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new Favorites($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Favorites suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Favorites [$Favorites] El objeto de tipo Favorites a eliminar
     */
    final public static function delete(Favorites $Favorites) {
        $sql = 'DELETE FROM `Favorites` WHERE user_id = ? AND problem_id = ?;';
        $params = [$Favorites->user_id, $Favorites->problem_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Favorites}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Favorites}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Favorites`.`user_id`, `Favorites`.`problem_id` from Favorites';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Favorites($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Favorites suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Favorites [$Favorites] El objeto de tipo Favorites a crear.
     */
    final public static function create(Favorites $Favorites) {
        $sql = 'INSERT INTO Favorites (`user_id`, `problem_id`) VALUES (?, ?);';
        $params = [
            $Favorites->user_id,
            $Favorites->problem_id,
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
