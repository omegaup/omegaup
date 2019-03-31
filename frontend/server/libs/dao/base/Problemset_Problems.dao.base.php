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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetProblems }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetProblemsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetProblems} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetProblems $Problemset_Problems) {
        if (!is_null(self::getByPK($Problemset_Problems->problemset_id, $Problemset_Problems->problem_id))) {
            return ProblemsetProblemsDAOBase::update($Problemset_Problems);
        } else {
            return ProblemsetProblemsDAOBase::create($Problemset_Problems);
        }
    }

    /**
     * Obtener {@link ProblemsetProblems} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetProblems} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetProblems Un objeto del tipo {@link ProblemsetProblems}. NULL si no hay tal registro.
     */
    final public static function getByPK($problemset_id, $problem_id) {
        if (is_null($problemset_id) || is_null($problem_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` FROM Problemset_Problems WHERE (problemset_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$problemset_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetProblems($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetProblems}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetProblems}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Problems`.`problemset_id`, `Problemset_Problems`.`problem_id`, `Problemset_Problems`.`points`, `Problemset_Problems`.`order` from Problemset_Problems';
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
            $allData[] = new ProblemsetProblems($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems a actualizar.
      */
    final private static function update(ProblemsetProblems $Problemset_Problems) {
        $sql = 'UPDATE `Problemset_Problems` SET `points` = ?, `order` = ? WHERE `problemset_id` = ? AND `problem_id` = ?;';
        $params = [
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
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetProblems suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetProblems dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetProblems [$Problemset_Problems] El objeto de tipo ProblemsetProblems a crear.
     */
    final private static function create(ProblemsetProblems $Problemset_Problems) {
        if (is_null($Problemset_Problems->points)) {
            $Problemset_Problems->points = '1';
        }
        if (is_null($Problemset_Problems->order)) {
            $Problemset_Problems->order = '1';
        }
        $sql = 'INSERT INTO Problemset_Problems (`problemset_id`, `problem_id`, `points`, `order`) VALUES (?, ?, ?, ?);';
        $params = [
            $Problemset_Problems->problemset_id,
            $Problemset_Problems->problem_id,
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

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetProblems suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
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
}
