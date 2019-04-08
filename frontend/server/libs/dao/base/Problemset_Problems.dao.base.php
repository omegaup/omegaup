<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetProblems Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsetProblems}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetProblemsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetProblems}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ProblemsetProblems $Problemset_Problems) {
        if (is_null(self::getByPK($Problemset_Problems->problemset_id, $Problemset_Problems->problem_id))) {
            return ProblemsetProblemsDAOBase::create($Problemset_Problems);
        }
        return ProblemsetProblemsDAOBase::update($Problemset_Problems);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems a actualizar.
     */
    final public static function update(ProblemsetProblems $Problemset_Problems) {
        $sql = 'UPDATE `Problemset_Problems` SET `version` = ?, `points` = ?, `order` = ? WHERE `problemset_id` = ? AND `problem_id` = ?;';
        $params = [
            $Problemset_Problems->version,
            $Problemset_Problems->points,
            $Problemset_Problems->order,
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetProblems} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetProblems} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetProblems Un objeto del tipo {@link ProblemsetProblems}. NULL si no hay tal registro.
     */
    final public static function getByPK($problemset_id, $problem_id) {
        if (is_null($problemset_id) || is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`version`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` FROM Problemset_Problems WHERE (problemset_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$problemset_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetProblems($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetProblems suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems a eliminar
     */
    final public static function delete(ProblemsetProblems $Problemset_Problems) {
        $sql = 'DELETE FROM `Problemset_Problems` WHERE problemset_id = ? AND problem_id = ?;';
        $params = [$Problemset_Problems->problemset_id, $Problemset_Problems->problem_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsetProblems}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetProblems}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`version`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` from Problemset_Problems';
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
            $allData[] = new ProblemsetProblems($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetProblems suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems a crear.
     */
    final public static function create(ProblemsetProblems $Problemset_Problems) {
        if (is_null($Problemset_Problems->points)) {
            $Problemset_Problems->points = '1';
        }
        if (is_null($Problemset_Problems->order)) {
            $Problemset_Problems->order = '1';
        }
        $sql = 'INSERT INTO Problemset_Problems (`problemset_id`, `problem_id`, `version`, `points`, `order`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id,
            $Problemset_Problems->version,
            $Problemset_Problems->points,
            $Problemset_Problems->order,
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
