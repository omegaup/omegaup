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
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param ProblemsetProblemOpened $Problemset_Problem_Opened El objeto de tipo ProblemsetProblemOpened
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemsetProblemOpened $Problemset_Problem_Opened) : int {
        if (empty($Problemset_Problem_Opened->problemset_id) || empty($Problemset_Problem_Opened->problem_id) || empty($Problemset_Problem_Opened->identity_id)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Problemset_Problem_Opened (`problemset_id`, `problem_id`, `identity_id`, `open_time`) VALUES (?, ?, ?, ?);';
        $params = [
            $Problemset_Problem_Opened->problemset_id,
            $Problemset_Problem_Opened->problem_id,
            $Problemset_Problem_Opened->identity_id,
            DAO::toMySQLTimestamp($Problemset_Problem_Opened->open_time),
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param ProblemsetProblemOpened $Problemset_Problem_Opened El objeto de tipo ProblemsetProblemOpened a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemsetProblemOpened $Problemset_Problem_Opened) : int {
        $sql = 'UPDATE `Problemset_Problem_Opened` SET `open_time` = ? WHERE `problemset_id` = ? AND `problem_id` = ? AND `identity_id` = ?;';
        $params = [
            DAO::toMySQLTimestamp($Problemset_Problem_Opened->open_time),
            is_null($Problemset_Problem_Opened->problemset_id) ? null : (int)$Problemset_Problem_Opened->problemset_id,
            is_null($Problemset_Problem_Opened->problem_id) ? null : (int)$Problemset_Problem_Opened->problem_id,
            is_null($Problemset_Problem_Opened->identity_id) ? null : (int)$Problemset_Problem_Opened->identity_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetProblemOpened} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetProblemOpened} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?ProblemsetProblemOpened Un objeto del tipo {@link ProblemsetProblemOpened}. NULL si no hay tal registro.
     */
    final public static function getByPK(?int $problemset_id, ?int $problem_id, ?int $identity_id) : ?ProblemsetProblemOpened {
        $sql = 'SELECT `Problemset_Problem_Opened`.`problemset_id`, `Problemset_Problem_Opened`.`problem_id`, `Problemset_Problem_Opened`.`identity_id`, `Problemset_Problem_Opened`.`open_time` FROM Problemset_Problem_Opened WHERE (problemset_id = ? AND problem_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$problemset_id, $problem_id, $identity_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsetProblemOpened($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetProblemOpened suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemsetProblemOpened $Problemset_Problem_Opened El objeto de tipo ProblemsetProblemOpened a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemsetProblemOpened $Problemset_Problem_Opened) : void {
        $sql = 'DELETE FROM `Problemset_Problem_Opened` WHERE problemset_id = ? AND problem_id = ? AND identity_id = ?;';
        $params = [$Problemset_Problem_Opened->problemset_id, $Problemset_Problem_Opened->problem_id, $Problemset_Problem_Opened->identity_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return ProblemsetProblemOpened[] Un arreglo que contiene objetos del tipo {@link ProblemsetProblemOpened}.
     *
     * @psalm-return array<int, ProblemsetProblemOpened>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemset_Problem_Opened`.`problemset_id`, `Problemset_Problem_Opened`.`problem_id`, `Problemset_Problem_Opened`.`identity_id`, `Problemset_Problem_Opened`.`open_time` from Problemset_Problem_Opened';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param ProblemsetProblemOpened $Problemset_Problem_Opened El objeto de tipo ProblemsetProblemOpened a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemsetProblemOpened $Problemset_Problem_Opened) : int {
        $sql = 'INSERT INTO Problemset_Problem_Opened (`problemset_id`, `problem_id`, `identity_id`, `open_time`) VALUES (?, ?, ?, ?);';
        $params = [
            is_null($Problemset_Problem_Opened->problemset_id) ? null : (int)$Problemset_Problem_Opened->problemset_id,
            is_null($Problemset_Problem_Opened->problem_id) ? null : (int)$Problemset_Problem_Opened->problem_id,
            is_null($Problemset_Problem_Opened->identity_id) ? null : (int)$Problemset_Problem_Opened->identity_id,
            DAO::toMySQLTimestamp($Problemset_Problem_Opened->open_time),
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
