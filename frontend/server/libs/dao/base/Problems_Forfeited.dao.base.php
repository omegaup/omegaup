<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsForfeited Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsForfeited}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsForfeitedDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsForfeited}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsForfeited [$Problems_Forfeited] El objeto de tipo ProblemsForfeited
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemsForfeited $Problems_Forfeited) : int {
        if (is_null($Problems_Forfeited->user_id) || is_null($Problems_Forfeited->problem_id)) {
            throw new NotFoundException('recordNotFound');
        }
        if (is_null($Problems_Forfeited->forfeited_date)) {
            $Problems_Forfeited->forfeited_date = Time::get();
        }
        $sql = 'REPLACE INTO Problems_Forfeited (`user_id`, `problem_id`, `forfeited_date`) VALUES (?, ?, ?);';
        $params = [
            (int)$Problems_Forfeited->user_id,
            (int)$Problems_Forfeited->problem_id,
            DAO::toMySQLTimestamp($Problems_Forfeited->forfeited_date),
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ProblemsForfeited [$Problems_Forfeited] El objeto de tipo ProblemsForfeited a actualizar.
     */
    final public static function update(ProblemsForfeited $Problems_Forfeited) : int {
        $sql = 'UPDATE `Problems_Forfeited` SET `forfeited_date` = ? WHERE `user_id` = ? AND `problem_id` = ?;';
        $params = [
            DAO::toMySQLTimestamp($Problems_Forfeited->forfeited_date),
            (int)$Problems_Forfeited->user_id,
            (int)$Problems_Forfeited->problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsForfeited} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsForfeited} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsForfeited Un objeto del tipo {@link ProblemsForfeited}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $user_id, int $problem_id) : ?ProblemsForfeited {
        $sql = 'SELECT `Problems_Forfeited`.`user_id`, `Problems_Forfeited`.`problem_id`, `Problems_Forfeited`.`forfeited_date` FROM Problems_Forfeited WHERE (user_id = ? AND problem_id = ?) LIMIT 1;';
        $params = [$user_id, $problem_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsForfeited($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsForfeited suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ProblemsForfeited [$Problems_Forfeited] El objeto de tipo ProblemsForfeited a eliminar
     */
    final public static function delete(ProblemsForfeited $Problems_Forfeited) : void {
        $sql = 'DELETE FROM `Problems_Forfeited` WHERE user_id = ? AND problem_id = ?;';
        $params = [$Problems_Forfeited->user_id, $Problems_Forfeited->problem_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsForfeited}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsForfeited}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problems_Forfeited`.`user_id`, `Problems_Forfeited`.`problem_id`, `Problems_Forfeited`.`forfeited_date` from Problems_Forfeited';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ProblemsForfeited($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsForfeited suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ProblemsForfeited [$Problems_Forfeited] El objeto de tipo ProblemsForfeited a crear.
     */
    final public static function create(ProblemsForfeited $Problems_Forfeited) : int {
        if (is_null($Problems_Forfeited->forfeited_date)) {
            $Problems_Forfeited->forfeited_date = Time::get();
        }
        $sql = 'INSERT INTO Problems_Forfeited (`user_id`, `problem_id`, `forfeited_date`) VALUES (?, ?, ?);';
        $params = [
            (int)$Problems_Forfeited->user_id,
            (int)$Problems_Forfeited->problem_id,
            DAO::toMySQLTimestamp($Problems_Forfeited->forfeited_date),
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
