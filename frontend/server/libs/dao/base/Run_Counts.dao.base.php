<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** RunCounts Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link RunCounts}.
 * @access public
 * @abstract
 *
 */
abstract class RunCountsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link RunCounts}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param RunCounts $Run_Counts El objeto de tipo RunCounts
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(RunCounts $Run_Counts) : int {
        if (empty($Run_Counts->date)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Run_Counts (`date`, `total`, `ac_count`) VALUES (?, ?, ?);';
        $params = [
            $Run_Counts->date,
            intval($Run_Counts->total),
            intval($Run_Counts->ac_count),
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param RunCounts $Run_Counts El objeto de tipo RunCounts a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(RunCounts $Run_Counts) : int {
        $sql = 'UPDATE `Run_Counts` SET `total` = ?, `ac_count` = ? WHERE `date` = ?;';
        $params = [
            (int)$Run_Counts->total,
            (int)$Run_Counts->ac_count,
            $Run_Counts->date,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link RunCounts} por llave primaria.
     *
     * Este metodo cargará un objeto {@link RunCounts} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?RunCounts Un objeto del tipo {@link RunCounts}. NULL si no hay tal registro.
     */
    final public static function getByPK(?string $date) : ?RunCounts {
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` FROM Run_Counts WHERE (date = ?) LIMIT 1;';
        $params = [$date];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new RunCounts($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto RunCounts suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param RunCounts $Run_Counts El objeto de tipo RunCounts a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(RunCounts $Run_Counts) : void {
        $sql = 'DELETE FROM `Run_Counts` WHERE date = ?;';
        $params = [$Run_Counts->date];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link RunCounts}.
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
     * @return RunCounts[] Un arreglo que contiene objetos del tipo {@link RunCounts}.
     *
     * @psalm-return array<int, RunCounts>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` from Run_Counts';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new RunCounts($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto RunCounts suministrado.
     *
     * @param RunCounts $Run_Counts El objeto de tipo RunCounts a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(RunCounts $Run_Counts) : int {
        $sql = 'INSERT INTO Run_Counts (`date`, `total`, `ac_count`) VALUES (?, ?, ?);';
        $params = [
            $Run_Counts->date,
            (int)$Run_Counts->total,
            (int)$Run_Counts->ac_count,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
