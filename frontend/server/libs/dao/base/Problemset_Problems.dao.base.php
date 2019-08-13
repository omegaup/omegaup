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
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param ProblemsetProblems $Problemset_Problems El objeto de tipo ProblemsetProblems
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemsetProblems $Problemset_Problems) : int {
        if (is_null($Problemset_Problems->problemset_id) || is_null($Problemset_Problems->problem_id)) {
            throw new NotFoundException('recordNotFound');
        }
        if (is_null($Problemset_Problems->commit)) {
            $Problemset_Problems->commit = 'published';
        }
        if (is_null($Problemset_Problems->points)) {
            $Problemset_Problems->points = 1.00;
        }
        if (is_null($Problemset_Problems->order)) {
            $Problemset_Problems->order = 1;
        }
        $sql = 'REPLACE INTO Problemset_Problems (`problemset_id`, `problem_id`, `commit`, `version`, `points`, `order`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemset_Problems->problemset_id,
            (int)$Problemset_Problems->problem_id,
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            (float)$Problemset_Problems->points,
            (int)$Problemset_Problems->order,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param ProblemsetProblems $Problemset_Problems El objeto de tipo ProblemsetProblems a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemsetProblems $Problemset_Problems) : int {
        $sql = 'UPDATE `Problemset_Problems` SET `commit` = ?, `version` = ?, `points` = ?, `order` = ? WHERE `problemset_id` = ? AND `problem_id` = ?;';
        $params = [
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            (float)$Problemset_Problems->points,
            (int)$Problemset_Problems->order,
            (int)$Problemset_Problems->problemset_id,
            (int)$Problemset_Problems->problem_id,
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
     * @return ?ProblemsetProblems Un objeto del tipo {@link ProblemsetProblems}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $problemset_id, int $problem_id) : ?ProblemsetProblems {
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`commit`, `Problemset_Problems`.`version`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` FROM Problemset_Problems WHERE (problemset_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$problemset_id, $problem_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsetProblems($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetProblems suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemsetProblems $Problemset_Problems El objeto de tipo ProblemsetProblems a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemsetProblems $Problemset_Problems) : void {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return array Un arreglo que contiene objetos del tipo {@link ProblemsetProblems}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`commit`, `Problemset_Problems`.`version`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` from Problemset_Problems';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
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
     * @param ProblemsetProblems $Problemset_Problems El objeto de tipo ProblemsetProblems a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemsetProblems $Problemset_Problems) : int {
        if (is_null($Problemset_Problems->commit)) {
            $Problemset_Problems->commit = 'published';
        }
        if (is_null($Problemset_Problems->points)) {
            $Problemset_Problems->points = 1.00;
        }
        if (is_null($Problemset_Problems->order)) {
            $Problemset_Problems->order = 1;
        }
        $sql = 'INSERT INTO Problemset_Problems (`problemset_id`, `problem_id`, `commit`, `version`, `points`, `order`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Problemset_Problems->problemset_id,
            (int)$Problemset_Problems->problem_id,
            $Problemset_Problems->commit,
            $Problemset_Problems->version,
            (float)$Problemset_Problems->points,
            (int)$Problemset_Problems->order,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
