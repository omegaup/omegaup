<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ContestLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ContestLog}.
 * @access public
 * @abstract
 *
 */
abstract class ContestLogDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ContestLog}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(ContestLog $Contest_Log) : int {
        if (is_null($Contest_Log->public_contest_id) ||
            is_null(self::getByPK($Contest_Log->public_contest_id))
        ) {
            return ContestLogDAOBase::create($Contest_Log);
        }
        return ContestLogDAOBase::update($Contest_Log);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a actualizar.
     */
    final public static function update(ContestLog $Contest_Log) : int {
        $sql = 'UPDATE `Contest_Log` SET `contest_id` = ?, `user_id` = ?, `from_admission_mode` = ?, `to_admission_mode` = ?, `time` = ? WHERE `public_contest_id` = ?;';
        $params = [
            (int)$Contest_Log->contest_id,
            (int)$Contest_Log->user_id,
            $Contest_Log->from_admission_mode,
            $Contest_Log->to_admission_mode,
            $Contest_Log->time,
            (int)$Contest_Log->public_contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link ContestLog} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ContestLog} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link ContestLog Un objeto del tipo {@link ContestLog}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $public_contest_id) : ?ContestLog {
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_admission_mode`, `Contest_Log`.`to_admission_mode`, `Contest_Log`.`time` FROM Contest_Log WHERE (public_contest_id = ?) LIMIT 1;';
        $params = [$public_contest_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ContestLog($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ContestLog suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a eliminar
     */
    final public static function delete(ContestLog $Contest_Log) : void {
        $sql = 'DELETE FROM `Contest_Log` WHERE public_contest_id = ?;';
        $params = [$Contest_Log->public_contest_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link ContestLog}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link ContestLog}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_admission_mode`, `Contest_Log`.`to_admission_mode`, `Contest_Log`.`time` from Contest_Log';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new ContestLog($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ContestLog suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param ContestLog [$Contest_Log] El objeto de tipo ContestLog a crear.
     */
    final public static function create(ContestLog $Contest_Log) : int {
        if (is_null($Contest_Log->time)) {
            $Contest_Log->time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO Contest_Log (`contest_id`, `user_id`, `from_admission_mode`, `to_admission_mode`, `time`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$Contest_Log->contest_id,
            (int)$Contest_Log->user_id,
            $Contest_Log->from_admission_mode,
            $Contest_Log->to_admission_mode,
            $Contest_Log->time,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Contest_Log->public_contest_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
