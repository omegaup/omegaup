<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Assignments Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Assignments}.
 * @access public
 * @abstract
 *
 */
abstract class AssignmentsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param Assignments $Assignments El objeto de tipo Assignments a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Assignments $Assignments) : int {
        $sql = 'UPDATE `Assignments` SET `course_id` = ?, `problemset_id` = ?, `acl_id` = ?, `name` = ?, `description` = ?, `alias` = ?, `publish_time_delay` = ?, `assignment_type` = ?, `start_time` = ?, `finish_time` = ?, `max_points` = ?, `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            is_null($Assignments->course_id) ? null : (int)$Assignments->course_id,
            is_null($Assignments->problemset_id) ? null : (int)$Assignments->problemset_id,
            is_null($Assignments->acl_id) ? null : (int)$Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            is_null($Assignments->publish_time_delay) ? null : (int)$Assignments->publish_time_delay,
            $Assignments->assignment_type,
            DAO::toMySQLTimestamp($Assignments->start_time),
            DAO::toMySQLTimestamp($Assignments->finish_time),
            (float)$Assignments->max_points,
            (int)$Assignments->order,
            (int)$Assignments->assignment_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Assignments} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Assignments} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Assignments Un objeto del tipo {@link Assignments}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $assignment_id) : ?Assignments {
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points`, `Assignments`.`order` FROM Assignments WHERE (assignment_id = ?) LIMIT 1;';
        $params = [$assignment_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Assignments($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Assignments suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Assignments $Assignments El objeto de tipo Assignments a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Assignments $Assignments) : void {
        $sql = 'DELETE FROM `Assignments` WHERE assignment_id = ?;';
        $params = [$Assignments->assignment_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link Assignments}.
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
     * @return Assignments[] Un arreglo que contiene objetos del tipo {@link Assignments}.
     *
     * @psalm-return array<int, Assignments>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points`, `Assignments`.`order` from Assignments';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new Assignments($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Assignments suministrado.
     *
     * @param Assignments $Assignments El objeto de tipo Assignments a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Assignments $Assignments) : int {
        $sql = 'INSERT INTO Assignments (`course_id`, `problemset_id`, `acl_id`, `name`, `description`, `alias`, `publish_time_delay`, `assignment_type`, `start_time`, `finish_time`, `max_points`, `order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Assignments->course_id) ? null : (int)$Assignments->course_id,
            is_null($Assignments->problemset_id) ? null : (int)$Assignments->problemset_id,
            is_null($Assignments->acl_id) ? null : (int)$Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            is_null($Assignments->publish_time_delay) ? null : (int)$Assignments->publish_time_delay,
            $Assignments->assignment_type,
            DAO::toMySQLTimestamp($Assignments->start_time),
            DAO::toMySQLTimestamp($Assignments->finish_time),
            (float)$Assignments->max_points,
            (int)$Assignments->order,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Assignments->assignment_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
