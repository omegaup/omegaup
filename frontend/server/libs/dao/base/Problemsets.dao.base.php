<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Problemsets Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Problemsets }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link Problemsets} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(Problemsets $Problemsets) {
        if (!is_null(self::getByPK($Problemsets->problemset_id))) {
            return ProblemsetsDAOBase::update($Problemsets);
        } else {
            return ProblemsetsDAOBase::create($Problemsets);
        }
    }

    /**
     * Obtener {@link Problemsets} por llave primaria.
     *
     * Este metodo cargara un objeto {@link Problemsets} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link Problemsets Un objeto del tipo {@link Problemsets}. NULL si no hay tal registro.
     */
    final public static function getByPK($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` FROM Problemsets WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new Problemsets($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link Problemsets}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link Problemsets}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemsets`.`problemset_id`, `Problemsets`.`acl_id`, `Problemsets`.`access_mode`, `Problemsets`.`languages`, `Problemsets`.`needs_basic_information`, `Problemsets`.`requests_user_information`, `Problemsets`.`scoreboard_url`, `Problemsets`.`scoreboard_url_admin`, `Problemsets`.`type`, `Problemsets`.`contest_id`, `Problemsets`.`assignment_id`, `Problemsets`.`interview_id` from Problemsets';
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
            $allData[] = new Problemsets($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a actualizar.
      */
    final private static function update(Problemsets $Problemsets) {
        $sql = 'UPDATE `Problemsets` SET `acl_id` = ?, `access_mode` = ?, `languages` = ?, `needs_basic_information` = ?, `requests_user_information` = ?, `scoreboard_url` = ?, `scoreboard_url_admin` = ?, `type` = ?, `contest_id` = ?, `assignment_id` = ?, `interview_id` = ? WHERE `problemset_id` = ?;';
        $params = [
            $Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            $Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            $Problemsets->contest_id,
            $Problemsets->assignment_id,
            $Problemsets->interview_id,
            $Problemsets->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto Problemsets suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto Problemsets dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a crear.
     */
    final private static function create(Problemsets $Problemsets) {
        if (is_null($Problemsets->access_mode)) {
            $Problemsets->access_mode = 'public';
        }
        if (is_null($Problemsets->needs_basic_information)) {
            $Problemsets->needs_basic_information = '0';
        }
        if (is_null($Problemsets->requests_user_information)) {
            $Problemsets->requests_user_information = 'no';
        }
        if (is_null($Problemsets->type)) {
            $Problemsets->type = 'Contest';
        }
        $sql = 'INSERT INTO Problemsets (`problemset_id`, `acl_id`, `access_mode`, `languages`, `needs_basic_information`, `requests_user_information`, `scoreboard_url`, `scoreboard_url_admin`, `type`, `contest_id`, `assignment_id`, `interview_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemsets->problemset_id,
            $Problemsets->acl_id,
            $Problemsets->access_mode,
            $Problemsets->languages,
            $Problemsets->needs_basic_information,
            $Problemsets->requests_user_information,
            $Problemsets->scoreboard_url,
            $Problemsets->scoreboard_url_admin,
            $Problemsets->type,
            $Problemsets->contest_id,
            $Problemsets->assignment_id,
            $Problemsets->interview_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problemsets->problemset_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto Problemsets suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param Problemsets [$Problemsets] El objeto de tipo Problemsets a eliminar
     */
    final public static function delete(Problemsets $Problemsets) {
        $sql = 'DELETE FROM `Problemsets` WHERE problemset_id = ?;';
        $params = [$Problemsets->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
