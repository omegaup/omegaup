<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentityRequest Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequest }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequest} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        if (!is_null(self::getByPK($Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id))) {
            return ProblemsetIdentityRequestDAOBase::update($Problemset_Identity_Request);
        } else {
            return ProblemsetIdentityRequestDAOBase::create($Problemset_Identity_Request);
        }
    }

    /**
     * Obtener {@link ProblemsetIdentityRequest} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetIdentityRequest} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentityRequest Un objeto del tipo {@link ProblemsetIdentityRequest}. NULL si no hay tal registro.
     */
    final public static function getByPK($identity_id, $problemset_id) {
        if (is_null($identity_id) || is_null($problemset_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` FROM Problemset_Identity_Request WHERE (identity_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetIdentityRequest($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetIdentityRequest}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequest}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` from Problemset_Identity_Request';
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
            $allData[] = new ProblemsetIdentityRequest($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a actualizar.
      */
    final private static function update(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        $sql = 'UPDATE `Problemset_Identity_Request` SET `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ? WHERE `identity_id` = ? AND `problemset_id` = ?;';
        $params = [
            $Problemset_Identity_Request->request_time,
            $Problemset_Identity_Request->last_update,
            $Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
            $Problemset_Identity_Request->identity_id,
            $Problemset_Identity_Request->problemset_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequest suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetIdentityRequest dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a crear.
     */
    final private static function create(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        if (is_null($Problemset_Identity_Request->request_time)) {
            $Problemset_Identity_Request->request_time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Identity_Request (`identity_id`, `problemset_id`, `request_time`, `last_update`, `accepted`, `extra_note`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identity_Request->identity_id,
            $Problemset_Identity_Request->problemset_id,
            $Problemset_Identity_Request->request_time,
            $Problemset_Identity_Request->last_update,
            $Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetIdentityRequest suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param ProblemsetIdentityRequest [$Problemset_Identity_Request] El objeto de tipo ProblemsetIdentityRequest a eliminar
     */
    final public static function delete(ProblemsetIdentityRequest $Problemset_Identity_Request) {
        $sql = 'DELETE FROM `Problemset_Identity_Request` WHERE identity_id = ? AND problemset_id = ?;';
        $params = [$Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
