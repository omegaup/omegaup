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
     * Actualizar registros.
     *
     * @param ProblemOfTheWeek $Problem_Of_The_Week El objeto de tipo ProblemOfTheWeek a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemOfTheWeek $Problem_Of_The_Week) : int {
        $sql = 'UPDATE `Problem_Of_The_Week` SET `problem_id` = ?, `time` = ?, `difficulty` = ? WHERE `problem_of_the_week_id` = ?;';
        $params = [
            (int)$Problem_Of_The_Week->problem_id,
            $Problem_Of_The_Week->time,
            $Problem_Of_The_Week->difficulty,
            (int)$Problem_Of_The_Week->problem_of_the_week_id,
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
     * @return ?ProblemOfTheWeek Un objeto del tipo {@link ProblemOfTheWeek}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $problem_of_the_week_id) : ?ProblemOfTheWeek {
        $sql = 'SELECT `Problem_Of_The_Week`.`problem_of_the_week_id`, `Problem_Of_The_Week`.`problem_id`, `Problem_Of_The_Week`.`time`, `Problem_Of_The_Week`.`difficulty` FROM Problem_Of_The_Week WHERE (problem_of_the_week_id = ?) LIMIT 1;';
        $params = [$problem_of_the_week_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemOfTheWeek($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemOfTheWeek suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemOfTheWeek $Problem_Of_The_Week El objeto de tipo ProblemOfTheWeek a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemOfTheWeek $Problem_Of_The_Week) : void {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return array Un arreglo que contiene objetos del tipo {@link ProblemOfTheWeek}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problem_Of_The_Week`.`problem_of_the_week_id`, `Problem_Of_The_Week`.`problem_id`, `Problem_Of_The_Week`.`time`, `Problem_Of_The_Week`.`difficulty` from Problem_Of_The_Week';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
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
     * @param ProblemOfTheWeek $Problem_Of_The_Week El objeto de tipo ProblemOfTheWeek a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemOfTheWeek $Problem_Of_The_Week) : int {
        if (is_null($Problem_Of_The_Week->time)) {
            $Problem_Of_The_Week->time = '2000-01-01';
        }
        $sql = 'INSERT INTO Problem_Of_The_Week (`problem_id`, `time`, `difficulty`) VALUES (?, ?, ?);';
        $params = [
            (int)$Problem_Of_The_Week->problem_id,
            $Problem_Of_The_Week->time,
            $Problem_Of_The_Week->difficulty,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Problem_Of_The_Week->problem_of_the_week_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
