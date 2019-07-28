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
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(RunCounts $Run_Counts) : int {
        if (is_null($Run_Counts->date) ||
            is_null(self::getByPK($Run_Counts->date))
        ) {
            return RunCountsDAOBase::create($Run_Counts);
        }
        return RunCountsDAOBase::update($Run_Counts);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a actualizar.
     */
    final public static function update(RunCounts $Run_Counts) : int {
        $sql = 'UPDATE `Run_Counts` SET `total` = ?, `ac_count` = ? WHERE `date` = ?;';
        $params = [
            (int)$Run_Counts->total,
            (int)$Run_Counts->ac_count,
            $Run_Counts->date,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link RunCounts} por llave primaria.
     *
     * Este metodo cargará un objeto {@link RunCounts} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link RunCounts Un objeto del tipo {@link RunCounts}. NULL si no hay tal registro.
     */
    final public static function getByPK(string $date) : ?RunCounts {
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` FROM Run_Counts WHERE (date = ?) LIMIT 1;';
        $params = [$date];
        global $conn;
        $row = $conn->GetRow($sql, $params);
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a eliminar
     */
    final public static function delete(RunCounts $Run_Counts) : void {
        $sql = 'DELETE FROM `Run_Counts` WHERE date = ?;';
        $params = [$Run_Counts->date];
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
     * y construirá un arreglo que contiene objetos de tipo {@link RunCounts}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link RunCounts}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Run_Counts`.`date`, `Run_Counts`.`total`, `Run_Counts`.`ac_count` from Run_Counts';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param RunCounts [$Run_Counts] El objeto de tipo RunCounts a crear.
     */
    final public static function create(RunCounts $Run_Counts) : int {
        if (is_null($Run_Counts->total)) {
            $Run_Counts->total = 0;
        }
        if (is_null($Run_Counts->ac_count)) {
            $Run_Counts->ac_count = 0;
        }
        $sql = 'INSERT INTO Run_Counts (`date`, `total`, `ac_count`) VALUES (?, ?, ?);';
        $params = [
            $Run_Counts->date,
            (int)$Run_Counts->total,
            (int)$Run_Counts->ac_count,
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
