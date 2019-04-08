<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetProblemOpened Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsetProblemOpened}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetProblemOpenedDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetProblemOpened}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ProblemsetProblemOpened $Problemset_Problem_Opened) {
        if (is_null(self::getByPK($Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->identity_id))) {
            return ProblemsetProblemOpenedDAOBase::create($Problemset_Problem_Opened);
        }
        return ProblemsetProblemOpenedDAOBase::update($Problemset_Problem_Opened);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a actualizar.
     */
    final public static function update(ProblemsetProblemOpened $Problemset_Problem_Opened) {
        $sql = 'UPDATE `Problemset_Problem_Opened` SET `open_time` = ? WHERE `problemset_id` = ? AND `problem_id` = ? AND `identity_id` = ?;';
        $params = [
            $Problemset_Problem_Opened->open_time,
            $Problemset_Problem_Opened->problemset_id,
            $Problemset_Problem_Opened->problem_id,
            $Problemset_Problem_Opened->identity_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetProblemOpened} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetProblemOpened} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetProblemOpened Un objeto del tipo {@link ProblemsetProblemOpened}. NULL si no hay tal registro.
     */
    final public static function getByPK($problemset_id, $problem_id, $identity_id) {
        if (is_null($problemset_id) || is_null($problem_id) || is_null($identity_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Problem_Opened`.`problemset_id`, `Problemset_Problem_Opened`.`problem_id`, `Problemset_Problem_Opened`.`identity_id`, `Problemset_Problem_Opened`.`open_time` FROM Problemset_Problem_Opened WHERE (problemset_id = ? AND problem_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$problemset_id, $problem_id, $identity_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetProblemOpened($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetProblemOpened suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a eliminar
     */
    final public static function delete(ProblemsetProblemOpened $Problemset_Problem_Opened) {
        $sql = 'DELETE FROM `Problemset_Problem_Opened` WHERE problemset_id = ? AND problem_id = ? AND identity_id = ?;';
        $params = [$Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->identity_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsetProblemOpened}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetProblemOpened}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problemset_Problem_Opened`.`problemset_id`, `Problemset_Problem_Opened`.`problem_id`, `Problemset_Problem_Opened`.`identity_id`, `Problemset_Problem_Opened`.`open_time` from Problemset_Problem_Opened';
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
            $allData[] = new ProblemsetProblemOpened($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetProblemOpened suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsetProblemOpened [$Problemset_Problem_Opened] El objeto de tipo ProblemsetProblemOpened a crear.
     */
    final public static function create(ProblemsetProblemOpened $Problemset_Problem_Opened) {
        if (is_null($Problemset_Problem_Opened->open_time)) {
            $Problemset_Problem_Opened->open_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Problem_Opened (`problemset_id`, `problem_id`, `identity_id`, `open_time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Problemset_Problem_Opened->problemset_id,
            $Problemset_Problem_Opened->problem_id,
            $Problemset_Problem_Opened->identity_id,
            $Problemset_Problem_Opened->open_time,
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
