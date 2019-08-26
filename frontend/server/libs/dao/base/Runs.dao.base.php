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
     * Actualizar registros.
     *
     * @param Runs $Runs El objeto de tipo Runs a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Runs $Runs) : int {
        $sql = 'UPDATE `Runs` SET `submission_id` = ?, `version` = ?, `status` = ?, `verdict` = ?, `runtime` = ?, `penalty` = ?, `memory` = ?, `score` = ?, `contest_score` = ?, `time` = ?, `judged_by` = ? WHERE `run_id` = ?;';
        $params = [
            is_null($Runs->submission_id) ? null : (int)$Runs->submission_id,
            $Runs->version,
            $Runs->status,
            $Runs->verdict,
            (int)$Runs->runtime,
            (int)$Runs->penalty,
            (int)$Runs->memory,
            (float)$Runs->score,
            is_null($Runs->contest_score) ? null : (float)$Runs->contest_score,
            DAO::toMySQLTimestamp($Runs->time),
            $Runs->judged_by,
            (int)$Runs->run_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Runs} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Runs} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $run_id) : ?Runs {
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`submission_id`, `Runs`.`version`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`judged_by` FROM Runs WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Runs($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Runs suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Runs $Runs El objeto de tipo Runs a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Runs $Runs) : void {
        $sql = 'DELETE FROM `Runs` WHERE run_id = ?;';
        $params = [$Runs->run_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return Runs[] Un arreglo que contiene objetos del tipo {@link Runs}.
     *
     * @psalm-return array<int, Runs>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`submission_id`, `Runs`.`version`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`judged_by` from Runs';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param Runs $Runs El objeto de tipo Runs a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Runs $Runs) : int {
        $sql = 'INSERT INTO Runs (`submission_id`, `version`, `status`, `verdict`, `runtime`, `penalty`, `memory`, `score`, `contest_score`, `time`, `judged_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Runs->submission_id) ? null : (int)$Runs->submission_id,
            $Runs->version,
            $Runs->status,
            $Runs->verdict,
            (int)$Runs->runtime,
            (int)$Runs->penalty,
            (int)$Runs->memory,
            (float)$Runs->score,
            is_null($Runs->contest_score) ? null : (float)$Runs->contest_score,
            DAO::toMySQLTimestamp($Runs->time),
            $Runs->judged_by,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Runs->run_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
