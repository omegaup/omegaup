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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link QualityNominationComments}.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationCommentsDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link QualityNominationComments}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(QualityNominationComments $QualityNomination_Comments) : int {
        if (is_null($QualityNomination_Comments->qualitynomination_comment_id) ||
            is_null(self::getByPK($QualityNomination_Comments->qualitynomination_comment_id))
        ) {
            return QualityNominationCommentsDAOBase::create($QualityNomination_Comments);
        }
        return QualityNominationCommentsDAOBase::update($QualityNomination_Comments);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a actualizar.
     */
    final public static function update(QualityNominationComments $QualityNomination_Comments) : int {
        $sql = 'UPDATE `QualityNomination_Comments` SET `qualitynomination_id` = ?, `user_id` = ?, `time` = ?, `vote` = ?, `contents` = ? WHERE `qualitynomination_comment_id` = ?;';
        $params = [
            (int)$QualityNomination_Comments->qualitynomination_id,
            (int)$QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            (int)$QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
            (int)$QualityNomination_Comments->qualitynomination_comment_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link QualityNominationComments} por llave primaria.
     *
     * Este metodo cargará un objeto {@link QualityNominationComments} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationComments Un objeto del tipo {@link QualityNominationComments}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $qualitynomination_comment_id) : ?QualityNominationComments {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` FROM QualityNomination_Comments WHERE (qualitynomination_comment_id = ?) LIMIT 1;';
        $params = [$qualitynomination_comment_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new QualityNominationComments($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto QualityNominationComments suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a eliminar
     */
    final public static function delete(QualityNominationComments $QualityNomination_Comments) : void {
        $sql = 'DELETE FROM `QualityNomination_Comments` WHERE qualitynomination_comment_id = ?;';
        $params = [$QualityNomination_Comments->qualitynomination_comment_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link QualityNominationComments}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationComments}.
     */
    final public static function getAll(
        ?int $pagina = null,
        ?int $filasPorPagina = null,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` from QualityNomination_Comments';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new QualityNominationComments($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationComments suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param QualityNominationComments [$QualityNomination_Comments] El objeto de tipo QualityNominationComments a crear.
     */
    final public static function create(QualityNominationComments $QualityNomination_Comments) : int {
        if (is_null($QualityNomination_Comments->time)) {
            $QualityNomination_Comments->time = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO QualityNomination_Comments (`qualitynomination_id`, `user_id`, `time`, `vote`, `contents`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            (int)$QualityNomination_Comments->qualitynomination_id,
            (int)$QualityNomination_Comments->user_id,
            $QualityNomination_Comments->time,
            (int)$QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $QualityNomination_Comments->qualitynomination_comment_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
