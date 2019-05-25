<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsBadges Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsBadges}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsBadgesDAOBase {
    /**
     * Obtener {@link ProblemsBadges} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsBadges} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsBadges Un objeto del tipo {@link ProblemsBadges}. NULL si no hay tal registro.
     */
    final public static function getByPK($badge_id, $problem_id) {
        if (is_null($badge_id) || is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problems_Badges`.`badge_id`, `Problems_Badges`.`problem_id` FROM Problems_Badges WHERE (badge_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$badge_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new ProblemsBadges($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsBadges suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsBadges [$Problems_Badges] El objeto de tipo ProblemsBadges a eliminar
     */
    final public static function delete(ProblemsBadges $Problems_Badges) {
        $sql = 'DELETE FROM `Problems_Badges` WHERE badge_id = ? AND problem_id = ?;';
        $params = [$Problems_Badges->badge_id, $Problems_Badges->problem_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsBadges}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsBadges}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problems_Badges`.`badge_id`, `Problems_Badges`.`problem_id` from Problems_Badges';
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
            $allData[] = new ProblemsBadges($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsBadges suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsBadges [$Problems_Badges] El objeto de tipo ProblemsBadges a crear.
     */
    final public static function create(ProblemsBadges $Problems_Badges) {
        $sql = 'INSERT INTO Problems_Badges (`badge_id`, `problem_id`) VALUES (?, ?);';
        $params = [
            $Problems_Badges->badge_id,
            $Problems_Badges->problem_id,
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
