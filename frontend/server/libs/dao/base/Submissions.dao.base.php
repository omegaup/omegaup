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
     * Actualizar registros.
     *
     * @param Submissions $Submissions El objeto de tipo Submissions a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Submissions $Submissions) : int {
        $sql = 'UPDATE `Submissions` SET `current_run_id` = ?, `identity_id` = ?, `problem_id` = ?, `problemset_id` = ?, `guid` = ?, `language` = ?, `time` = ?, `submit_delay` = ?, `type` = ? WHERE `submission_id` = ?;';
        $params = [
            is_null($Submissions->current_run_id) ? null : (int)$Submissions->current_run_id,
            is_null($Submissions->identity_id) ? null : (int)$Submissions->identity_id,
            is_null($Submissions->problem_id) ? null : (int)$Submissions->problem_id,
            is_null($Submissions->problemset_id) ? null : (int)$Submissions->problemset_id,
            $Submissions->guid,
            $Submissions->language,
            DAO::toMySQLTimestamp($Submissions->time),
            (int)$Submissions->submit_delay,
            $Submissions->type,
            (int)$Submissions->submission_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Submissions} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Submissions} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Submissions Un objeto del tipo {@link Submissions}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $submission_id) : ?Submissions {
        $sql = 'SELECT `Submissions`.`submission_id`, `Submissions`.`current_run_id`, `Submissions`.`identity_id`, `Submissions`.`problem_id`, `Submissions`.`problemset_id`, `Submissions`.`guid`, `Submissions`.`language`, `Submissions`.`time`, `Submissions`.`submit_delay`, `Submissions`.`type` FROM Submissions WHERE (submission_id = ?) LIMIT 1;';
        $params = [$submission_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Submissions($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Submissions suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Submissions $Submissions El objeto de tipo Submissions a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Submissions $Submissions) : void {
        $sql = 'DELETE FROM `Submissions` WHERE submission_id = ?;';
        $params = [$Submissions->submission_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return Submissions[] Un arreglo que contiene objetos del tipo {@link Submissions}.
     *
     * @psalm-return array<int, Submissions>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Submissions`.`submission_id`, `Submissions`.`current_run_id`, `Submissions`.`identity_id`, `Submissions`.`problem_id`, `Submissions`.`problemset_id`, `Submissions`.`guid`, `Submissions`.`language`, `Submissions`.`time`, `Submissions`.`submit_delay`, `Submissions`.`type` from Submissions';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param Submissions $Submissions El objeto de tipo Submissions a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Submissions $Submissions) : int {
        $sql = 'INSERT INTO Submissions (`current_run_id`, `identity_id`, `problem_id`, `problemset_id`, `guid`, `language`, `time`, `submit_delay`, `type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Submissions->current_run_id) ? null : (int)$Submissions->current_run_id,
            is_null($Submissions->identity_id) ? null : (int)$Submissions->identity_id,
            is_null($Submissions->problem_id) ? null : (int)$Submissions->problem_id,
            is_null($Submissions->problemset_id) ? null : (int)$Submissions->problemset_id,
            $Submissions->guid,
            $Submissions->language,
            DAO::toMySQLTimestamp($Submissions->time),
            (int)$Submissions->submit_delay,
            $Submissions->type,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Submissions->submission_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
