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
     * Actualizar registros.
     *
     * @param ContestLog $Contest_Log El objeto de tipo ContestLog a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ContestLog $Contest_Log) : int {
        $sql = 'UPDATE `Contest_Log` SET `contest_id` = ?, `user_id` = ?, `from_admission_mode` = ?, `to_admission_mode` = ?, `time` = ? WHERE `public_contest_id` = ?;';
        $params = [
            is_null($Contest_Log->contest_id) ? null : (int)$Contest_Log->contest_id,
            is_null($Contest_Log->user_id) ? null : (int)$Contest_Log->user_id,
            $Contest_Log->from_admission_mode,
            $Contest_Log->to_admission_mode,
            DAO::toMySQLTimestamp($Contest_Log->time),
            (int)$Contest_Log->public_contest_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link ContestLog} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ContestLog} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?ContestLog Un objeto del tipo {@link ContestLog}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $public_contest_id) : ?ContestLog {
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_admission_mode`, `Contest_Log`.`to_admission_mode`, `Contest_Log`.`time` FROM Contest_Log WHERE (public_contest_id = ?) LIMIT 1;';
        $params = [$public_contest_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ContestLog $Contest_Log El objeto de tipo ContestLog a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ContestLog $Contest_Log) : void {
        $sql = 'DELETE FROM `Contest_Log` WHERE public_contest_id = ?;';
        $params = [$Contest_Log->public_contest_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return ContestLog[] Un arreglo que contiene objetos del tipo {@link ContestLog}.
     *
     * @psalm-return array<int, ContestLog>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Contest_Log`.`public_contest_id`, `Contest_Log`.`contest_id`, `Contest_Log`.`user_id`, `Contest_Log`.`from_admission_mode`, `Contest_Log`.`to_admission_mode`, `Contest_Log`.`time` from Contest_Log';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param ContestLog $Contest_Log El objeto de tipo ContestLog a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ContestLog $Contest_Log) : int {
        $sql = 'INSERT INTO Contest_Log (`contest_id`, `user_id`, `from_admission_mode`, `to_admission_mode`, `time`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($Contest_Log->contest_id) ? null : (int)$Contest_Log->contest_id,
            is_null($Contest_Log->user_id) ? null : (int)$Contest_Log->user_id,
            $Contest_Log->from_admission_mode,
            $Contest_Log->to_admission_mode,
            DAO::toMySQLTimestamp($Contest_Log->time),
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Contest_Log->public_contest_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
