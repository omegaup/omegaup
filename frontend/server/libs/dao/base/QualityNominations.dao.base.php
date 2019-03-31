<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominations Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominations }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominations} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominations $QualityNominations) {
        if (!is_null(self::getByPK($QualityNominations->qualitynomination_id))) {
            return QualityNominationsDAOBase::update($QualityNominations);
        } else {
            return QualityNominationsDAOBase::create($QualityNominations);
        }
    }

    /**
     * Obtener {@link QualityNominations} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominations} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominations Un objeto del tipo {@link QualityNominations}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_id) {
        if (is_null($qualitynomination_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` FROM QualityNominations WHERE (qualitynomination_id = ?) LIMIT 1;';
        $params = [$qualitynomination_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominations($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominations}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominations}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` from QualityNominations';
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
            $allData[] = new QualityNominations($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a actualizar.
      */
    final private static function update(QualityNominations $QualityNominations) {
        $sql = 'UPDATE `QualityNominations` SET `user_id` = ?, `problem_id` = ?, `nomination` = ?, `contents` = ?, `time` = ?, `status` = ? WHERE `qualitynomination_id` = ?;';
        $params = [
            $QualityNominations->user_id,
            $QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            $QualityNominations->time,
            $QualityNominations->status,
            $QualityNominations->qualitynomination_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominations suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominations dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a crear.
     */
    final private static function create(QualityNominations $QualityNominations) {
        if (is_null($QualityNominations->nomination)) {
            $QualityNominations->nomination = 'suggestion';
        }
        if (is_null($QualityNominations->time)) {
            $QualityNominations->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($QualityNominations->status)) {
            $QualityNominations->status = 'open';
        }
        $sql = 'INSERT INTO QualityNominations (`qualitynomination_id`, `user_id`, `problem_id`, `nomination`, `contents`, `time`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $params = [
            $QualityNominations->qualitynomination_id,
            $QualityNominations->user_id,
            $QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            $QualityNominations->time,
            $QualityNominations->status,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $QualityNominations->qualitynomination_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominations suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a eliminar
     */
    final public static function delete(QualityNominations $QualityNominations) {
        $sql = 'DELETE FROM `QualityNominations` WHERE qualitynomination_id = ?;';
        $params = [$QualityNominations->qualitynomination_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
