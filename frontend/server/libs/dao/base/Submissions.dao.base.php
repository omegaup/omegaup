<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Submissions Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Submissions}.
 * @access public
 * @abstract
 *
 */
abstract class SubmissionsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Submissions}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Submissions [$Submissions] El objeto de tipo Submissions
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Submissions $Submissions) {
        if (is_null(self::getByPK($Submissions->submission_id))) {
            return SubmissionsDAOBase::create($Submissions);
        }
        return SubmissionsDAOBase::update($Submissions);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Submissions [$Submissions] El objeto de tipo Submissions a actualizar.
     */
    final public static function update(Submissions $Submissions) {
        $sql = 'UPDATE `Submissions` SET `current_run_id` = ?, `identity_id` = ?, `problem_id` = ?, `problemset_id` = ?, `guid` = ?, `language` = ?, `penalty` = ?, `time` = ?, `submit_delay` = ?, `type` = ? WHERE `submission_id` = ?;';
        $params = [
            $Submissions->current_run_id,
            $Submissions->identity_id,
            $Submissions->problem_id,
            $Submissions->problemset_id,
            $Submissions->guid,
            $Submissions->language,
            $Submissions->penalty,
            $Submissions->time,
            $Submissions->submit_delay,
            $Submissions->type,
            $Submissions->submission_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Submissions} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Submissions} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Submissions Un objeto del tipo {@link Submissions}. NULL si no hay tal registro.
     */
    final public static function getByPK($submission_id) {
        if (is_null($submission_id)) {
            return null;
        }
        $sql = 'SELECT `Submissions`.`submission_id`, `Submissions`.`current_run_id`, `Submissions`.`identity_id`, `Submissions`.`problem_id`, `Submissions`.`problemset_id`, `Submissions`.`guid`, `Submissions`.`language`, `Submissions`.`penalty`, `Submissions`.`time`, `Submissions`.`submit_delay`, `Submissions`.`type` FROM Submissions WHERE (submission_id = ?) LIMIT 1;';
        $params = [$submission_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Submissions($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Submissions suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Submissions [$Submissions] El objeto de tipo Submissions a eliminar
     */
    final public static function delete(Submissions $Submissions) {
        $sql = 'DELETE FROM `Submissions` WHERE submission_id = ?;';
        $params = [$Submissions->submission_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Submissions}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Submissions}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `Submissions`.`submission_id`, `Submissions`.`current_run_id`, `Submissions`.`identity_id`, `Submissions`.`problem_id`, `Submissions`.`problemset_id`, `Submissions`.`guid`, `Submissions`.`language`, `Submissions`.`penalty`, `Submissions`.`time`, `Submissions`.`submit_delay`, `Submissions`.`type` from Submissions';
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
            $allData[] = new Submissions($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Submissions suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Submissions [$Submissions] El objeto de tipo Submissions a crear.
     */
    final public static function create(Submissions $Submissions) {
        if (is_null($Submissions->penalty)) {
            $Submissions->penalty = '0';
        }
        if (is_null($Submissions->time)) {
            $Submissions->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Submissions->submit_delay)) {
            $Submissions->submit_delay = '0';
        }
        if (is_null($Submissions->type)) {
            $Submissions->type = 'normal';
        }
        $sql = 'INSERT INTO Submissions (`current_run_id`, `identity_id`, `problem_id`, `problemset_id`, `guid`, `language`, `penalty`, `time`, `submit_delay`, `type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Submissions->current_run_id,
            $Submissions->identity_id,
            $Submissions->problem_id,
            $Submissions->problemset_id,
            $Submissions->guid,
            $Submissions->language,
            $Submissions->penalty,
            $Submissions->time,
            $Submissions->submit_delay,
            $Submissions->type,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Submissions->submission_id = $conn->Insert_ID();

        return $ar;
    }
}
