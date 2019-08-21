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
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param ProblemViewed $Problem_Viewed El objeto de tipo ProblemViewed
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemViewed $Problem_Viewed) : int {
        if (empty($Problem_Viewed->problem_id) || empty($Problem_Viewed->identity_id)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Problem_Viewed (`problem_id`, `identity_id`, `view_time`) VALUES (?, ?, ?);';
        $params = [
            $Problem_Viewed->problem_id,
            $Problem_Viewed->identity_id,
            DAO::toMySQLTimestamp($Problem_Viewed->view_time),
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param ProblemViewed $Problem_Viewed El objeto de tipo ProblemViewed a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemViewed $Problem_Viewed) : int {
        $sql = 'UPDATE `Problem_Viewed` SET `view_time` = ? WHERE `problem_id` = ? AND `identity_id` = ?;';
        $params = [
            DAO::toMySQLTimestamp($Problem_Viewed->view_time),
            is_null($Problem_Viewed->problem_id) ? null : (int)$Problem_Viewed->problem_id,
            is_null($Problem_Viewed->identity_id) ? null : (int)$Problem_Viewed->identity_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemViewed} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemViewed} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?ProblemViewed Un objeto del tipo {@link ProblemViewed}. NULL si no hay tal registro.
     */
    final public static function getByPK(?int $problem_id, ?int $identity_id) : ?ProblemViewed {
        $sql = 'SELECT `Problem_Viewed`.`problem_id`, `Problem_Viewed`.`identity_id`, `Problem_Viewed`.`view_time` FROM Problem_Viewed WHERE (problem_id = ? AND identity_id = ?) LIMIT 1;';
        $params = [$problem_id, $identity_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemViewed $Problem_Viewed El objeto de tipo ProblemViewed a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemViewed $Problem_Viewed) : void {
        $sql = 'DELETE FROM `Problem_Viewed` WHERE problem_id = ? AND identity_id = ?;';
        $params = [$Problem_Viewed->problem_id, $Problem_Viewed->identity_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return ProblemViewed[] Un arreglo que contiene objetos del tipo {@link ProblemViewed}.
     *
     * @psalm-return array<int, ProblemViewed>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problem_Viewed`.`problem_id`, `Problem_Viewed`.`identity_id`, `Problem_Viewed`.`view_time` from Problem_Viewed';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param ProblemViewed $Problem_Viewed El objeto de tipo ProblemViewed a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemViewed $Problem_Viewed) : int {
        $sql = 'INSERT INTO Problem_Viewed (`problem_id`, `identity_id`, `view_time`) VALUES (?, ?, ?);';
        $params = [
            is_null($Problem_Viewed->problem_id) ? null : (int)$Problem_Viewed->problem_id,
            is_null($Problem_Viewed->identity_id) ? null : (int)$Problem_Viewed->identity_id,
            DAO::toMySQLTimestamp($Problem_Viewed->view_time),
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
