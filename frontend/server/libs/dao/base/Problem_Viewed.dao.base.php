<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemViewed Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemViewed}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemViewedDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemViewed}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemViewed [$Problem_Viewed] El objeto de tipo ProblemViewed
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ProblemViewed $Problem_Viewed) {
        if (is_null(self::getByPK($Problem_Viewed->problem_id, $Problem_Viewed->identity_id))) {
            return ProblemViewedDAOBase::create($Problem_Viewed);
        }
        return ProblemViewedDAOBase::update($Problem_Viewed);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemViewed [$Problem_Viewed] El objeto de tipo ProblemViewed a actualizar.
     */
    final public static function update(ProblemViewed $Problem_Viewed) {
        $sql = 'UPDATE `Problem_Viewed` SET `view_time` = ? WHERE `problem_id` = ? AND `identity_id` = ?;';
        $params = [
            $Problem_Viewed->view_time,
            is_null($Problem_Viewed->problem_id) ? null : (int)$Problem_Viewed->problem_id,
            is_null($Problem_Viewed->identity_id) ? null : (int)$Problem_Viewed->identity_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemViewed} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemViewed} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemViewed Un objeto del tipo {@link ProblemViewed}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_id, $identity_id) {
        if (is_null($problem_id) || is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT `Problem_Viewed`.`problem_id`, `Problem_Viewed`.`identity_id`, `Problem_Viewed`.`view_time` FROM Problem_Viewed WHERE (problem_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$problem_id, $identity_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemViewed($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemViewed suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemViewed [$Problem_Viewed] El objeto de tipo ProblemViewed a eliminar
     */
    final public static function delete(ProblemViewed $Problem_Viewed) {
        $sql = 'DELETE FROM `Problem_Viewed` WHERE problem_id = ? AND identity_id = ?;';
        $params = [$Problem_Viewed->problem_id, $Problem_Viewed->identity_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemViewed}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemViewed}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problem_Viewed`.`problem_id`, `Problem_Viewed`.`identity_id`, `Problem_Viewed`.`view_time` from Problem_Viewed';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ProblemViewed($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemViewed suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemViewed [$Problem_Viewed] El objeto de tipo ProblemViewed a crear.
     */
    final public static function create(ProblemViewed $Problem_Viewed) {
        if (is_null($Problem_Viewed->view_time)) {
            $Problem_Viewed->view_time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Problem_Viewed (`problem_id`, `identity_id`, `view_time`) VALUES (?, ?, ?);';
        $params = [
            is_null($Problem_Viewed->problem_id) ? null : (int)$Problem_Viewed->problem_id,
            is_null($Problem_Viewed->identity_id) ? null : (int)$Problem_Viewed->identity_id,
            $Problem_Viewed->view_time,
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
