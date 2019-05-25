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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link QualityNominations}.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominations}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(QualityNominations $QualityNominations) {
        if (is_null(self::getByPK($QualityNominations->qualitynomination_id))) {
            return QualityNominationsDAOBase::create($QualityNominations);
        }
        return QualityNominationsDAOBase::update($QualityNominations);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a actualizar.
     */
    final public static function update(QualityNominations $QualityNominations) {
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
     * Obtener {@link QualityNominations} por llave primaria.
     *
     * Este metodo cargará un objeto {@link QualityNominations} de la base
     * de datos usando sus llaves primarias.
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
        if (empty($rs)) {
            return null;
        }
        return new QualityNominations($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto QualityNominations suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
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

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link QualityNominations}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominations}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` from QualityNominations';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new QualityNominations($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominations suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param QualityNominations [$QualityNominations] El objeto de tipo QualityNominations a crear.
     */
    final public static function create(QualityNominations $QualityNominations) {
        if (is_null($QualityNominations->nomination)) {
            $QualityNominations->nomination = 'suggestion';
        }
        if (is_null($QualityNominations->time)) {
            $QualityNominations->time = gmdate('Y-m-d H:i:s');
        }
        if (is_null($QualityNominations->status)) {
            $QualityNominations->status = 'open';
        }
        $sql = 'INSERT INTO QualityNominations (`user_id`, `problem_id`, `nomination`, `contents`, `time`, `status`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
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
}
