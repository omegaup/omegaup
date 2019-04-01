<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominationComments Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominationComments }.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationCommentsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominationComments} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save(QualityNominationComments $QualityNomination_Comments) {
        if (!is_null(self::getByPK($QualityNomination_Comments->qualitynomination_comment_id))) {
            return QualityNominationCommentsDAOBase::update($QualityNomination_Comments);
        } else {
            return QualityNominationCommentsDAOBase::create($QualityNomination_Comments);
        }
    }

    /**
     * Obtener {@link QualityNominationComments} por llave primaria.
     *
     * Este metodo cargara un objeto {@link QualityNominationComments} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationComments Un objeto del tipo {@link QualityNominationComments}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_comment_id) {
        if (is_null($qualitynomination_comment_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` FROM QualityNomination_Comments WHERE (qualitynomination_comment_id = ?) LIMIT 1;';
        $params = [$qualitynomination_comment_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new QualityNominationComments($rs);
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link QualityNominationComments}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationComments}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` from QualityNomination_Comments';
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
            $allData[] = new QualityNominationComments($row);
        }
        return $allData;
    }

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a actualizar.
      */
    final private static function update(QualityNominationComments $QualityNomination_Comments) {
        $sql = 'UPDATE `QualityNomination_Comments` SET `qualitynomination_id` = ?, `user_id` = ?, `time` = ?, `vote` = ?, `contents` = ? WHERE `qualitynomination_comment_id` = ?;';
        $params = [
            $QualityNomination_Comments->qualitynomination_id,
            $QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            $QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
            $QualityNomination_Comments->qualitynomination_comment_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationComments suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto QualityNominationComments dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a crear.
     */
    final private static function create(QualityNominationComments $QualityNomination_Comments) {
        if (is_null($QualityNomination_Comments->time)) {
            $QualityNomination_Comments->time = gmdate('Y-m-d H:i:s');
        }
        $sql = 'INSERT INTO QualityNomination_Comments (`qualitynomination_comment_id`, `qualitynomination_id`, `user_id`, `time`, `vote`, `contents`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $QualityNomination_Comments->qualitynomination_comment_id,
            $QualityNomination_Comments->qualitynomination_id,
            $QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            $QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $QualityNomination_Comments->qualitynomination_comment_id = $conn->Insert_ID();

        return $ar;
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto QualityNominationComments suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a eliminar
     */
    final public static function delete(QualityNominationComments $QualityNomination_Comments) {
        $sql = 'DELETE FROM `QualityNomination_Comments` WHERE qualitynomination_comment_id = ?;';
        $params = [$QualityNomination_Comments->qualitynomination_comment_id];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
}
