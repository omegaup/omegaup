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
     * Actualizar registros.
     *
     * @param Courses $Courses El objeto de tipo Courses a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(Courses $Courses) : int {
        $sql = 'UPDATE `Courses` SET `name` = ?, `description` = ?, `alias` = ?, `group_id` = ?, `acl_id` = ?, `start_time` = ?, `finish_time` = ?, `public` = ?, `school_id` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `show_scoreboard` = ? WHERE `course_id` = ?;';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            is_null($Courses->group_id) ? null : (int)$Courses->group_id,
            is_null($Courses->acl_id) ? null : (int)$Courses->acl_id,
            DAO::toMySQLTimestamp($Courses->start_time),
            DAO::toMySQLTimestamp($Courses->finish_time),
            (int)$Courses->public,
            is_null($Courses->school_id) ? null : (int)$Courses->school_id,
            (int)$Courses->needs_basic_information,
            $Courses->requests_user_information,
            (int)$Courses->show_scoreboard,
            (int)$Courses->course_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link Courses} por llave primaria.
     *
     * Este metodo cargará un objeto {@link Courses} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?Courses Un objeto del tipo {@link Courses}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $course_id) : ?Courses {
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` FROM Courses WHERE (course_id = ?) LIMIT 1;';
        $params = [$course_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
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
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param Courses $Courses El objeto de tipo Courses a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(Courses $Courses) : void {
        $sql = 'DELETE FROM `Courses` WHERE course_id = ?;';
        $params = [$Courses->course_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return Courses[] Un arreglo que contiene objetos del tipo {@link Courses}.
     *
     * @psalm-return array<int, Courses>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` from Courses';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
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
     * @param Courses $Courses El objeto de tipo Courses a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(Courses $Courses) : int {
        $sql = 'INSERT INTO Courses (`name`, `description`, `alias`, `group_id`, `acl_id`, `start_time`, `finish_time`, `public`, `school_id`, `needs_basic_information`, `requests_user_information`, `show_scoreboard`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            is_null($Courses->group_id) ? null : (int)$Courses->group_id,
            is_null($Courses->acl_id) ? null : (int)$Courses->acl_id,
            DAO::toMySQLTimestamp($Courses->start_time),
            DAO::toMySQLTimestamp($Courses->finish_time),
            (int)$Courses->public,
            is_null($Courses->school_id) ? null : (int)$Courses->school_id,
            (int)$Courses->needs_basic_information,
            $Courses->requests_user_information,
            (int)$Courses->show_scoreboard,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $Courses->course_id = MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
