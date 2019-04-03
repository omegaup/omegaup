<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemOfTheWeek Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemOfTheWeek}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemOfTheWeekDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemOfTheWeek}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemOfTheWeek [$Problem_Of_The_Week] El objeto de tipo ProblemOfTheWeek
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ProblemOfTheWeek $Problem_Of_The_Week) {
        if (is_null(self::getByPK($Problem_Of_The_Week->problem_of_the_week_id))) {
            return ProblemOfTheWeekDAOBase::create($Problem_Of_The_Week);
        }
        return ProblemOfTheWeekDAOBase::update($Problem_Of_The_Week);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemOfTheWeek [$Problem_Of_The_Week] El objeto de tipo ProblemOfTheWeek a actualizar.
     */
    final public static function update(ProblemOfTheWeek $Problem_Of_The_Week) {
        $sql = 'UPDATE `Problem_Of_The_Week` SET `problem_id` = ?, `time` = ?, `difficulty` = ? WHERE `problem_of_the_week_id` = ?;';
        $params = [
            $Problem_Of_The_Week->problem_id,
            $Problem_Of_The_Week->time,
            $Problem_Of_The_Week->difficulty,
            $Problem_Of_The_Week->problem_of_the_week_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemOfTheWeek} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemOfTheWeek} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemOfTheWeek Un objeto del tipo {@link ProblemOfTheWeek}. NULL si no hay tal registro.
     */
    final public static function getByPK($problem_of_the_week_id) {
        if (is_null($problem_of_the_week_id)) {
            return null;
        }
        $sql = 'SELECT `Problem_Of_The_Week`.`problem_of_the_week_id`, `Problem_Of_The_Week`.`problem_id`, `Problem_Of_The_Week`.`time`, `Problem_Of_The_Week`.`difficulty` FROM Problem_Of_The_Week WHERE (problem_of_the_week_id = ?) LIMIT 1;';
        $params = [$problem_of_the_week_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemOfTheWeek($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemOfTheWeek suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemOfTheWeek [$Problem_Of_The_Week] El objeto de tipo ProblemOfTheWeek a eliminar
     */
    final public static function delete(ProblemOfTheWeek $Problem_Of_The_Week) {
        $sql = 'DELETE FROM `Problem_Of_The_Week` WHERE problem_of_the_week_id = ?;';
        $params = [$Problem_Of_The_Week->problem_of_the_week_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemOfTheWeek}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemOfTheWeek}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Problem_Of_The_Week`.`problem_of_the_week_id`, `Problem_Of_The_Week`.`problem_id`, `Problem_Of_The_Week`.`time`, `Problem_Of_The_Week`.`difficulty` from Problem_Of_The_Week';
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
            $allData[] = new ProblemOfTheWeek($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemOfTheWeek suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemOfTheWeek [$Problem_Of_The_Week] El objeto de tipo ProblemOfTheWeek a crear.
     */
    final public static function create(ProblemOfTheWeek $Problem_Of_The_Week) {
        if (is_null($Problem_Of_The_Week->time)) {
            $Problem_Of_The_Week->time = '2000-01-01';
        }
        $sql = 'INSERT INTO Problem_Of_The_Week (`problem_of_the_week_id`, `problem_id`, `time`, `difficulty`) VALUES (?, ?, ?, ?);';
        $params = [
            $Problem_Of_The_Week->problem_of_the_week_id,
            $Problem_Of_The_Week->problem_id,
            $Problem_Of_The_Week->time,
            $Problem_Of_The_Week->difficulty,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problem_Of_The_Week->problem_of_the_week_id = $conn->Insert_ID();

        return $ar;
    }
}
