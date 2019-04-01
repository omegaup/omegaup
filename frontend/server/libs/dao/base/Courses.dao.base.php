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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Courses }.
 * @access public
 * @abstract
 *
 */
abstract class CoursesDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Courses} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Courses [$Courses] El objeto de tipo Courses
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Courses $Courses) {
        if (!is_null(self::getByPK($Courses->course_id))) {
            return CoursesDAOBase::update($Courses);
        } else {
            return CoursesDAOBase::create($Courses);
        }
    }

    /**
     * Obtener {@link Courses} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Courses} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Courses Un objeto del tipo {@link Courses}. NULL si no hay tal registro.
     */
    final public static function getByPK($course_id) {
        if (is_null($course_id)) {
            return null;
        }
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` FROM Courses WHERE (course_id = ?) LIMIT 1;';
        $params = [$course_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Courses($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Courses}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Courses}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Courses`.`course_id`, `Courses`.`name`, `Courses`.`description`, `Courses`.`alias`, `Courses`.`group_id`, `Courses`.`acl_id`, `Courses`.`start_time`, `Courses`.`finish_time`, `Courses`.`public`, `Courses`.`school_id`, `Courses`.`needs_basic_information`, `Courses`.`requests_user_information`, `Courses`.`show_scoreboard` from Courses';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Courses($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Courses [$Courses] El objeto de tipo Courses a actualizar.
      */
    final private static function update(Courses $Courses) {
        $sql = 'UPDATE `Courses` SET `name` = ?, `description` = ?, `alias` = ?, `group_id` = ?, `acl_id` = ?, `start_time` = ?, `finish_time` = ?, `public` = ?, `school_id` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `show_scoreboard` = ? WHERE `course_id` = ?;';
        $params = [
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            $Courses->group_id,
            $Courses->acl_id,
            $Courses->start_time,
            $Courses->finish_time,
            $Courses->public,
            $Courses->school_id,
            $Courses->needs_basic_information,
            $Courses->requests_user_information,
            $Courses->show_scoreboard,
            $Courses->course_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Courses suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Courses dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Courses [$Courses] El objeto de tipo Courses a crear.
     */
    final private static function create(Courses $Courses) {
        if (is_null($Courses->start_time)) {
            $Courses->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Courses->finish_time)) {
            $Courses->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Courses->public)) {
            $Courses->public = '0';
        }
        if (is_null($Courses->needs_basic_information)) {
            $Courses->needs_basic_information = '0';
        }
        if (is_null($Courses->requests_user_information)) {
            $Courses->requests_user_information = 'no';
        }
        if (is_null($Courses->show_scoreboard)) {
            $Courses->show_scoreboard = '0';
        }
        $sql = 'INSERT INTO Courses (`course_id`, `name`, `description`, `alias`, `group_id`, `acl_id`, `start_time`, `finish_time`, `public`, `school_id`, `needs_basic_information`, `requests_user_information`, `show_scoreboard`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Courses->course_id,
            $Courses->name,
            $Courses->description,
            $Courses->alias,
            $Courses->group_id,
            $Courses->acl_id,
            $Courses->start_time,
            $Courses->finish_time,
            $Courses->public,
            $Courses->school_id,
            $Courses->needs_basic_information,
            $Courses->requests_user_information,
            $Courses->show_scoreboard,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Courses->course_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Courses suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Courses [$Courses] El objeto de tipo Courses a eliminar
     */
    final public static function delete(Courses $Courses) {
        $sql = 'DELETE FROM `Courses` WHERE course_id = ?;';
        $params = [$Courses->course_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
