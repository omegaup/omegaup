<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Courses Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link Courses}.
 * @access public
 * @abstract
 *
 */
abstract class CoursesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Courses}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Courses [$Courses] El objeto de tipo Courses
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(Courses $Courses) : int {
        if (is_null($Courses->course_id) ||
            is_null(self::getByPK($Courses->course_id))
        ) {
            return CoursesDAOBase::create($Courses);
        }
        return CoursesDAOBase::update($Courses);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param Courses [$Courses] El objeto de tipo Courses a actualizar.
     */
    final public static function update(Courses $Courses) : int {
        $sql = 'UPDATE `Courses` SET `name` = ?, `description` = ?, `alias` = ?, `group_id` = ?, `acl_id` = ?, `start_time` = ?, `finish_time` = ?, `public` = ?, `school_id` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `show_scoreboard` = ? WHERE `course_id` = ?;';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            (int)$Courses->group_id,
            (int)$Courses->acl_id,
            $Courses->start_time,
            $Courses->finish_time,
            (int)$Courses->public,
            is_null($Courses->school_id) ? null : (int)$Courses->school_id,
            (int)$Courses->needs_basic_information,
            $Courses->requests_user_information,
            (int)$Courses->show_scoreboard,
            (int)$Courses->course_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link Courses} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Courses} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link Courses Un objeto del tipo {@link Courses}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $course_id) : ?Courses {
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` FROM Courses WHERE (course_id = ?) LIMIT 1;';
        $params = [$course_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new Courses($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto Courses suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param Courses [$Courses] El objeto de tipo Courses a eliminar
     */
    final public static function delete(Courses $Courses) : void {
        $sql = 'DELETE FROM `Courses` WHERE course_id = ?;';
        $params = [$Courses->course_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link Courses}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link Courses}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` from Courses';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new Courses($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Courses suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param Courses [$Courses] El objeto de tipo Courses a crear.
     */
    final public static function create(Courses $Courses) : int {
        if (is_null($Courses->start_time)) {
            $Courses->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Courses->finish_time)) {
            $Courses->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Courses->public)) {
            $Courses->public = false;
        }
        if (is_null($Courses->needs_basic_information)) {
            $Courses->needs_basic_information = false;
        }
        if (is_null($Courses->requests_user_information)) {
            $Courses->requests_user_information = 'no';
        }
        if (is_null($Courses->show_scoreboard)) {
            $Courses->show_scoreboard = false;
        }
        $sql = 'INSERT INTO Courses (`name`, `description`, `alias`, `group_id`, `acl_id`, `start_time`, `finish_time`, `public`, `school_id`, `needs_basic_information`, `requests_user_information`, `show_scoreboard`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            (int)$Courses->group_id,
            (int)$Courses->acl_id,
            $Courses->start_time,
            $Courses->finish_time,
            (int)$Courses->public,
            is_null($Courses->school_id) ? null : (int)$Courses->school_id,
            (int)$Courses->needs_basic_information,
            $Courses->requests_user_information,
            (int)$Courses->show_scoreboard,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Courses->course_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
