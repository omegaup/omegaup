<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** ProblemsetIdentityRequestHistory Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentityRequestHistory }.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestHistoryDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequestHistory} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (!is_null(self::getByPK($Problemset_Identity_Request_History->history_id))) {
            return ProblemsetIdentityRequestHistoryDAOBase::update($Problemset_Identity_Request_History);
        } else {
            return ProblemsetIdentityRequestHistoryDAOBase::create($Problemset_Identity_Request_History);
        }
    }

    /**
     * Obtener {@link ProblemsetIdentityRequestHistory} por llave primaria.
     *
     * Este metodo cargara un objeto {@link ProblemsetIdentityRequestHistory} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link ProblemsetIdentityRequestHistory Un objeto del tipo {@link ProblemsetIdentityRequestHistory}. NULL si no hay tal registro.
     */
    final public static function getByPK($history_id) {
        if (is_null($history_id)) {
            return null;
        }
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` FROM Problemset_Identity_Request_History WHERE (history_id = ?) LIMIT 1;';
        $params = [$history_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new ProblemsetIdentityRequestHistory($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link ProblemsetIdentityRequestHistory}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequestHistory}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `Problemset_Identity_Request_History`.`history_id`, `Problemset_Identity_Request_History`.`identity_id`, `Problemset_Identity_Request_History`.`problemset_id`, `Problemset_Identity_Request_History`.`time`, `Problemset_Identity_Request_History`.`accepted`, `Problemset_Identity_Request_History`.`admin_id` from Problemset_Identity_Request_History';
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
            $allData[] = new ProblemsetIdentityRequestHistory($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a actualizar.
      */
    final private static function update(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        $sql = 'UPDATE `Problemset_Identity_Request_History` SET `identity_id` = ?, `problemset_id` = ?, `time` = ?, `accepted` = ?, `admin_id` = ? WHERE `history_id` = ?;';
        $params = [
            $Problemset_Identity_Request_History->identity_id,
            $Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            $Problemset_Identity_Request_History->accepted,
            $Problemset_Identity_Request_History->admin_id,
            $Problemset_Identity_Request_History->history_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequestHistory suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto ProblemsetIdentityRequestHistory dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a crear.
     */
    final private static function create(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        if (is_null($Problemset_Identity_Request_History->time)) {
            $Problemset_Identity_Request_History->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO Problemset_Identity_Request_History (`history_id`, `identity_id`, `problemset_id`, `time`, `accepted`, `admin_id`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identity_Request_History->history_id,
            $Problemset_Identity_Request_History->identity_id,
            $Problemset_Identity_Request_History->problemset_id,
            $Problemset_Identity_Request_History->time,
            $Problemset_Identity_Request_History->accepted,
            $Problemset_Identity_Request_History->admin_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $Problemset_Identity_Request_History->history_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto ProblemsetIdentityRequestHistory suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param ProblemsetIdentityRequestHistory [$Problemset_Identity_Request_History] El objeto de tipo ProblemsetIdentityRequestHistory a eliminar
     */
    final public static function delete(ProblemsetIdentityRequestHistory $Problemset_Identity_Request_History) {
        $sql = 'DELETE FROM `Problemset_Identity_Request_History` WHERE history_id = ?;';
        $params = [$Problemset_Identity_Request_History->history_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
