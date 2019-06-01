<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsLanguages Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsLanguages}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsLanguagesDAOBase {
    /**
     * Obtener {@link ProblemsLanguages} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsLanguages} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsLanguages Un objeto del tipo {@link ProblemsLanguages}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id, $language_id) {
        if (is_null($problem_id) || is_null($language_id)) {
            return null;
        }
        $sql = 'SELECT `Problems_Languages`.`problem_id`, `Problems_Languages`.`language_id` FROM Problems_Languages WHERE (problem_id = ? AND language_id = ?) LIMIT 1;';
        $params = [$problem_id, $language_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsLanguages($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsLanguages suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages a eliminar
     */
    final public static function delete(ProblemsLanguages $Problems_Languages) {
        $sql = 'DELETE FROM `Problems_Languages` WHERE problem_id = ? AND language_id = ?;';
        $params = [$Problems_Languages->problem_id, $Problems_Languages->language_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsLanguages}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsLanguages}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problems_Languages`.`problem_id`, `Problems_Languages`.`language_id` from Problems_Languages';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ProblemsLanguages($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsLanguages suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsLanguages [$Problems_Languages] El objeto de tipo ProblemsLanguages a crear.
     */
    final public static function create(ProblemsLanguages $Problems_Languages) {
        $sql = 'INSERT INTO Problems_Languages (`problem_id`, `language_id`) VALUES (?, ?);';
        $params = [
            is_null($Problems_Languages->problem_id) ? null : (int)$Problems_Languages->problem_id,
            is_null($Problems_Languages->language_id) ? null : (int)$Problems_Languages->language_id,
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
