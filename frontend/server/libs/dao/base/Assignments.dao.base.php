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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Assignments}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Assignments [$Assignments] El objeto de tipo Assignments
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Assignments $Assignments) : int {
        if (is_null($Assignments->assignment_id) ||
            is_null(self::getByPK($Assignments->assignment_id))
        ) {
            return AssignmentsDAOBase::create($Assignments);
        }
        return AssignmentsDAOBase::update($Assignments);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Assignments [$Assignments] El objeto de tipo Assignments a actualizar.
     */
    final public static function update(Assignments $Assignments) : int {
        $sql = 'UPDATE `Assignments` SET `course_id` = ?, `problemset_id` = ?, `acl_id` = ?, `name` = ?, `description` = ?, `alias` = ?, `publish_time_delay` = ?, `assignment_type` = ?, `start_time` = ?, `finish_time` = ?, `max_points` = ?, `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            (int)$Assignments->course_id,
            (int)$Assignments->problemset_id,
            (int)$Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            is_null($Assignments->publish_time_delay) ? null : (int)$Assignments->publish_time_delay,
            $Assignments->assignment_type,
            $Assignments->start_time,
            $Assignments->finish_time,
            (float)$Assignments->max_points,
            (int)$Assignments->order,
            (int)$Assignments->assignment_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Assignments} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Assignments} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Assignments Un objeto del tipo {@link Assignments}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $assignment_id) : ?Assignments {
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points`, `Assignments`.`order` FROM Assignments WHERE (assignment_id = ?) LIMIT 1;';
        $params = [$assignment_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Assignments [$Assignments] El objeto de tipo Assignments a eliminar
     */
    final public static function delete(Assignments $Assignments) : void {
        $sql = 'DELETE FROM `Assignments` WHERE assignment_id = ?;';
        $params = [$Assignments->assignment_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Assignments}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Assignments}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Assignments`.`assignment_id`, `Assignments`.`course_id`, `Assignments`.`problemset_id`, `Assignments`.`acl_id`, `Assignments`.`name`, `Assignments`.`description`, `Assignments`.`alias`, `Assignments`.`publish_time_delay`, `Assignments`.`assignment_type`, `Assignments`.`start_time`, `Assignments`.`finish_time`, `Assignments`.`max_points`, `Assignments`.`order` from Assignments';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Assignments [$Assignments] El objeto de tipo Assignments a crear.
     */
    final public static function create(Assignments $Assignments) : int {
        if (is_null($Assignments->start_time)) {
            $Assignments->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Assignments->finish_time)) {
            $Assignments->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Assignments->max_points)) {
            $Assignments->max_points = 0.00;
        }
        if (is_null($Assignments->order)) {
            $Assignments->order = 1;
        }
        $sql = 'INSERT INTO Assignments (`course_id`, `problemset_id`, `acl_id`, `name`, `description`, `alias`, `publish_time_delay`, `assignment_type`, `start_time`, `finish_time`, `max_points`, `order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$Assignments->course_id,
            (int)$Assignments->problemset_id,
            (int)$Assignments->acl_id,
            $Assignments->name,
            $Assignments->description,
            $Assignments->alias,
            is_null($Assignments->publish_time_delay) ? null : (int)$Assignments->publish_time_delay,
            $Assignments->assignment_type,
            $Assignments->start_time,
            $Assignments->finish_time,
            (float)$Assignments->max_points,
            (int)$Assignments->order,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Assignments->assignment_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
