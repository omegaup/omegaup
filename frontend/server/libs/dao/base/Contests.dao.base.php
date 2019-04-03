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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Contests }.
 * @access public
 * @abstract
 *
 */
abstract class ContestsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Contests} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Contests [$Contests] El objeto de tipo Contests
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Contests $Contests) {
        if (!is_null(self::getByPK($Contests->contest_id))) {
            return ContestsDAOBase::update($Contests);
        } else {
            return ContestsDAOBase::create($Contests);
        }
    }

    /**
     * Obtener {@link Contests} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Contests} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Contests Un objeto del tipo {@link Contests}. NULL si no hay tal registro.
     */
    final public static function getByPK($contest_id) {
        if (is_null($contest_id)) {
            return null;
        }
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`problemset_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`last_updated`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`admission_mode`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`urgent`, `Contests`.`languages`, `Contests`.`recommended` FROM Contests WHERE (contest_id = ?) LIMIT 1;';
        $params = [$contest_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Contests($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Contests}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Contests}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Contests`.`contest_id`, `Contests`.`problemset_id`, `Contests`.`acl_id`, `Contests`.`title`, `Contests`.`description`, `Contests`.`start_time`, `Contests`.`finish_time`, `Contests`.`last_updated`, `Contests`.`window_length`, `Contests`.`rerun_id`, `Contests`.`admission_mode`, `Contests`.`alias`, `Contests`.`scoreboard`, `Contests`.`points_decay_factor`, `Contests`.`partial_score`, `Contests`.`submissions_gap`, `Contests`.`feedback`, `Contests`.`penalty`, `Contests`.`penalty_type`, `Contests`.`penalty_calc_policy`, `Contests`.`show_scoreboard_after`, `Contests`.`urgent`, `Contests`.`languages`, `Contests`.`recommended` from Contests';
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
            $allData[] = new Contests($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Contests [$Contests] El objeto de tipo Contests a actualizar.
      */
    final private static function update(Contests $Contests) {
        $sql = 'UPDATE `Contests` SET `problemset_id` = ?, `acl_id` = ?, `title` = ?, `description` = ?, `start_time` = ?, `finish_time` = ?, `last_updated` = ?, `window_length` = ?, `rerun_id` = ?, `admission_mode` = ?, `alias` = ?, `scoreboard` = ?, `points_decay_factor` = ?, `partial_score` = ?, `submissions_gap` = ?, `feedback` = ?, `penalty` = ?, `penalty_type` = ?, `penalty_calc_policy` = ?, `show_scoreboard_after` = ?, `urgent` = ?, `languages` = ?, `recommended` = ? WHERE `contest_id` = ?;';
        $params = [
            $Contests->problemset_id,
            $Contests->acl_id,
            $Contests->title,
            $Contests->description,
            $Contests->start_time,
            $Contests->finish_time,
            $Contests->last_updated,
            $Contests->window_length,
            $Contests->rerun_id,
            $Contests->admission_mode,
            $Contests->alias,
            $Contests->scoreboard,
            $Contests->points_decay_factor,
            $Contests->partial_score,
            $Contests->submissions_gap,
            $Contests->feedback,
            $Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            $Contests->show_scoreboard_after,
            $Contests->urgent,
            $Contests->languages,
            $Contests->recommended,
            $Contests->contest_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Contests suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Contests dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Contests [$Contests] El objeto de tipo Contests a crear.
     */
    final private static function create(Contests $Contests) {
        if (is_null($Contests->start_time)) {
            $Contests->start_time = '2000-01-01 06:00:00';
        }
        if (is_null($Contests->finish_time)) {
            $Contests->finish_time = '2000-01-01 06:00:00';
        }
        if (is_null($Contests->last_updated)) {
            $Contests->last_updated = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Contests->admission_mode)) {
            $Contests->admission_mode = 'private';
        }
        if (is_null($Contests->scoreboard)) {
            $Contests->scoreboard = '1';
        }
        if (is_null($Contests->points_decay_factor)) {
            $Contests->points_decay_factor = '0';
        }
        if (is_null($Contests->partial_score)) {
            $Contests->partial_score = '1';
        }
        if (is_null($Contests->submissions_gap)) {
            $Contests->submissions_gap = '60';
        }
        if (is_null($Contests->penalty)) {
            $Contests->penalty = '1';
        }
        if (is_null($Contests->show_scoreboard_after)) {
            $Contests->show_scoreboard_after = '1';
        }
        if (is_null($Contests->urgent)) {
            $Contests->urgent = '0';
        }
        if (is_null($Contests->recommended)) {
            $Contests->recommended = '0';
        }
        $sql = 'INSERT INTO Contests (`contest_id`, `problemset_id`, `acl_id`, `title`, `description`, `start_time`, `finish_time`, `last_updated`, `window_length`, `rerun_id`, `admission_mode`, `alias`, `scoreboard`, `points_decay_factor`, `partial_score`, `submissions_gap`, `feedback`, `penalty`, `penalty_type`, `penalty_calc_policy`, `show_scoreboard_after`, `urgent`, `languages`, `recommended`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Contests->contest_id,
            $Contests->problemset_id,
            $Contests->acl_id,
            $Contests->title,
            $Contests->description,
            $Contests->start_time,
            $Contests->finish_time,
            $Contests->last_updated,
            $Contests->window_length,
            $Contests->rerun_id,
            $Contests->admission_mode,
            $Contests->alias,
            $Contests->scoreboard,
            $Contests->points_decay_factor,
            $Contests->partial_score,
            $Contests->submissions_gap,
            $Contests->feedback,
            $Contests->penalty,
            $Contests->penalty_type,
            $Contests->penalty_calc_policy,
            $Contests->show_scoreboard_after,
            $Contests->urgent,
            $Contests->languages,
            $Contests->recommended,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Contests->contest_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Contests suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Contests [$Contests] El objeto de tipo Contests a eliminar
     */
    final public static function delete(Contests $Contests) {
        $sql = 'DELETE FROM `Contests` WHERE contest_id = ?;';
        $params = [$Contests->contest_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
