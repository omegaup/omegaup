<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Runs Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Runs}.
 * @access public
 * @abstract
 *
 */
abstract class RunsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Runs}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Runs [$Runs] El objeto de tipo Runs
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Runs $Runs) {
        if (is_null(self::getByPK($Runs->run_id))) {
            return RunsDAOBase::create($Runs);
        }
        return RunsDAOBase::update($Runs);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Runs [$Runs] El objeto de tipo Runs a actualizar.
     */
    final public static function update(Runs $Runs) {
        $sql = 'UPDATE `Runs` SET `submission_id` = ?, `version` = ?, `status` = ?, `verdict` = ?, `runtime` = ?, `penalty` = ?, `memory` = ?, `score` = ?, `contest_score` = ?, `time` = ?, `judged_by` = ? WHERE `run_id` = ?;';
        $params = [
            $Runs->submission_id,
            $Runs->version,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->judged_by,
            $Runs->run_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Runs} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Runs} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
     */
    final public static function getByPK($run_id) {
        if (is_null($run_id)) {
            return null;
        }
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`submission_id`, `Runs`.`version`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`judged_by` FROM Runs WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Runs($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Runs suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Runs [$Runs] El objeto de tipo Runs a eliminar
     */
    final public static function delete(Runs $Runs) {
        $sql = 'DELETE FROM `Runs` WHERE run_id = ?;';
        $params = [$Runs->run_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Runs}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Runs}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`submission_id`, `Runs`.`version`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`judged_by` from Runs';
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
            $allData[] = new Runs($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Runs suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Runs [$Runs] El objeto de tipo Runs a crear.
     */
    final public static function create(Runs $Runs) {
        if (is_null($Runs->status)) {
            $Runs->status = 'new';
        }
        if (is_null($Runs->runtime)) {
            $Runs->runtime = '0';
        }
        if (is_null($Runs->penalty)) {
            $Runs->penalty = '0';
        }
        if (is_null($Runs->memory)) {
            $Runs->memory = '0';
        }
        if (is_null($Runs->score)) {
            $Runs->score = '0';
        }
        if (is_null($Runs->time)) {
            $Runs->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Runs (`submission_id`, `version`, `status`, `verdict`, `runtime`, `penalty`, `memory`, `score`, `contest_score`, `time`, `judged_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Runs->submission_id,
            $Runs->version,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->judged_by,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Runs->run_id = $conn->Insert_ID();

        return $ar;
    }
}
