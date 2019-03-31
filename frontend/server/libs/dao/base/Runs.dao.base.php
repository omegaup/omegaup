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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }.
 * @access public
 * @abstract
 *
 */
abstract class RunsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Runs} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Runs [$Runs] El objeto de tipo Runs
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Runs $Runs) {
        if (!is_null(self::getByPK($Runs->run_id))) {
            return RunsDAOBase::update($Runs);
        } else {
            return RunsDAOBase::create($Runs);
        }
    }

    /**
     * Obtener {@link Runs} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Runs} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Runs Un objeto del tipo {@link Runs}. NULL si no hay tal registro.
     */
    final public static function getByPK($run_id) {
        if (is_null($run_id)) {
            return null;
        }
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`identity_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`judged_by`, `Runs`.`type` FROM Runs WHERE (run_id = ?) LIMIT 1;';
        $params = [$run_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Runs($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Runs}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Runs}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Runs`.`run_id`, `Runs`.`identity_id`, `Runs`.`problem_id`, `Runs`.`problemset_id`, `Runs`.`guid`, `Runs`.`language`, `Runs`.`status`, `Runs`.`verdict`, `Runs`.`runtime`, `Runs`.`penalty`, `Runs`.`memory`, `Runs`.`score`, `Runs`.`contest_score`, `Runs`.`time`, `Runs`.`submit_delay`, `Runs`.`judged_by`, `Runs`.`type` from Runs';
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
            $allData[] = new Runs($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Runs [$Runs] El objeto de tipo Runs a actualizar.
      */
    final private static function update(Runs $Runs) {
        $sql = 'UPDATE `Runs` SET `identity_id` = ?, `problem_id` = ?, `problemset_id` = ?, `guid` = ?, `language` = ?, `status` = ?, `verdict` = ?, `runtime` = ?, `penalty` = ?, `memory` = ?, `score` = ?, `contest_score` = ?, `time` = ?, `submit_delay` = ?, `judged_by` = ?, `type` = ? WHERE `run_id` = ?;';
        $params = [
            $Runs->identity_id,
            $Runs->problem_id,
            $Runs->problemset_id,
            $Runs->guid,
            $Runs->language,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->submit_delay,
            $Runs->judged_by,
            $Runs->type,
            $Runs->run_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Runs suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Runs dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Runs [$Runs] El objeto de tipo Runs a crear.
     */
    final private static function create(Runs $Runs) {
        if (is_null($Runs->status)) {
            $Runs->status = 'new';
        }
        if (is_null($Runs->runtime)) {
            $Runs->runtime = '0';
        }
        if (is_null($Runs->penalty)) {
            $Runs->penalty = '0';
        }
        if (is_null($Runs->memory)) {
            $Runs->memory = '0';
        }
        if (is_null($Runs->score)) {
            $Runs->score = '0';
        }
        if (is_null($Runs->time)) {
            $Runs->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($Runs->submit_delay)) {
            $Runs->submit_delay = '0';
        }
        if (is_null($Runs->type)) {
            $Runs->type = 'normal';
        }
        $sql = 'INSERT INTO Runs (`run_id`, `identity_id`, `problem_id`, `problemset_id`, `guid`, `language`, `status`, `verdict`, `runtime`, `penalty`, `memory`, `score`, `contest_score`, `time`, `submit_delay`, `judged_by`, `type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Runs->run_id,
            $Runs->identity_id,
            $Runs->problem_id,
            $Runs->problemset_id,
            $Runs->guid,
            $Runs->language,
            $Runs->status,
            $Runs->verdict,
            $Runs->runtime,
            $Runs->penalty,
            $Runs->memory,
            $Runs->score,
            $Runs->contest_score,
            $Runs->time,
            $Runs->submit_delay,
            $Runs->judged_by,
            $Runs->type,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Runs->run_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Runs suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Runs [$Runs] El objeto de tipo Runs a eliminar
     */
    final public static function delete(Runs $Runs) {
        $sql = 'DELETE FROM `Runs` WHERE run_id = ?;';
        $params = [$Runs->run_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
