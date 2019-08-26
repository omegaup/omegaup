<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Contests Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Contests}.
 * @access public
 * @abstract
 *
 */
abstract class ContestsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Contests $Contests El objeto de tipo Contests a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Contests $Contests) : int {
        $sql = 'UPDATE `Contests` SET `problemset_id` = ?, `acl_id` = ?, `title` = ?, `description` = ?, `start_time` = ?, `finish_time` = ?, `last_updated` = ?, `window_length` = ?, `rerun_id` = ?, `admission_mode` = ?, `alias` = ?, `scoreboard` = ?, `points_decay_factor` = ?, `partial_score` = ?, `submissions_gap` = ?, `feedback` = ?, `penalty` = ?, `penalty_type` = ?, `penalty_calc_policy` = ?, `show_scoreboard_after` = ?, `urgent` = ?, `languages` = ?, `recommended` = ? WHERE `contest_id` = ?;';
        $params = [
            is_null($Contests->problemset_id) ? null : (int)$Contests->problemset_id,
            is_null($Contests->acl_id) ? null : (int)$Contests->acl_id,
            $Contests->title,
            $Contests->description,
            DAO::toMySQLTimestamp($Contests->start_time),
            DAO::toMySQLTimestamp($Contests->finish_time),
            DAO::toMySQLTimestamp($Contests->last_updated),
            is_null($Contests->window_length) ? null : (int)$Contests->window_length,
            is_null($Contests->rerun_id) ? null : (int)$Contests->rerun_id,
            $Contests->admission_mode,
            $Contests->alias,
            (int)$Contests->scoreboard,
            (float)$Contests->points_decay_factor,
            (int)$Contests->partial_score,
            (int)$Contests->submissions_gap,
            $Contests->feedback,
            (int)$Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            (int)$Contests->show_scoreboard_after,
            (int)$Contests->urgent,
            $Contests->languages,
            (int)$Contests->recommended,
            (int)$Contests->contest_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Contests} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Contests} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Contests Un objeto del tipo {@link Contests}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $contest_id) : ?Contests {
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`problemset_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`last_updated`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`admission_mode`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`urgent`, `Contests`.`languages`, `Contests`.`recommended` FROM Contests WHERE (contest_id = ?) LIMIT 1;';
        $params = [$contest_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Contests($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Contests suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Contests $Contests El objeto de tipo Contests a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Contests $Contests) : void {
        $sql = 'DELETE FROM `Contests` WHERE contest_id = ?;';
        $params = [$Contests->contest_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Contests}.
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
     * @return Contests[] Un arreglo que contiene objetos del tipo {@link Contests}.
     *
     * @psalm-return array<int, Contests>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`problemset_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`last_updated`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`admission_mode`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`urgent`, `Contests`.`languages`, `Contests`.`recommended` from Contests';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new Contests($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Contests suministrado.
     *
     * @param Contests $Contests El objeto de tipo Contests a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Contests $Contests) : int {
        $sql = 'INSERT INTO Contests (`problemset_id`, `acl_id`, `title`, `description`, `start_time`, `finish_time`, `last_updated`, `window_length`, `rerun_id`, `admission_mode`, `alias`, `scoreboard`, `points_decay_factor`, `partial_score`, `submissions_gap`, `feedback`, `penalty`, `penalty_type`, `penalty_calc_policy`, `show_scoreboard_after`, `urgent`, `languages`, `recommended`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Contests->problemset_id) ? null : (int)$Contests->problemset_id,
            is_null($Contests->acl_id) ? null : (int)$Contests->acl_id,
            $Contests->title,
            $Contests->description,
            DAO::toMySQLTimestamp($Contests->start_time),
            DAO::toMySQLTimestamp($Contests->finish_time),
            DAO::toMySQLTimestamp($Contests->last_updated),
            is_null($Contests->window_length) ? null : (int)$Contests->window_length,
            is_null($Contests->rerun_id) ? null : (int)$Contests->rerun_id,
            $Contests->admission_mode,
            $Contests->alias,
            (int)$Contests->scoreboard,
            (float)$Contests->points_decay_factor,
            (int)$Contests->partial_score,
            (int)$Contests->submissions_gap,
            $Contests->feedback,
            (int)$Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            (int)$Contests->show_scoreboard_after,
            (int)$Contests->urgent,
            $Contests->languages,
            (int)$Contests->recommended,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Contests->contest_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
