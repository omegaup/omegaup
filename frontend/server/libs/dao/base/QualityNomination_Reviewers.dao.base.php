<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** QualityNominationReviewers Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link QualityNominationReviewers}.
 * @access public
 * @abstract
 *
 */
abstract class QualityNominationReviewersDAOBase {
    /**
     * Obtener {@link QualityNominationReviewers} por llave primaria.
     *
     * Este metodo cargará un objeto {@link QualityNominationReviewers} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link QualityNominationReviewers Un objeto del tipo {@link QualityNominationReviewers}. NULL si no hay tal registro.
     */
    final public static function getByPK($qualitynomination_id, $user_id) {
        if (is_null($qualitynomination_id) || is_null($user_id)) {
            return null;
        }
        $sql = 'SELECT `QualityNomination_Reviewers`.`qualitynomination_id`, `QualityNomination_Reviewers`.`user_id` FROM QualityNomination_Reviewers WHERE (qualitynomination_id = ? AND user_id = ?) LIMIT 1;';
        $params = [$qualitynomination_id, $user_id];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new QualityNominationReviewers($rs);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto QualityNominationReviewers suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param QualityNominationReviewers [$QualityNomination_Reviewers] El objeto de tipo QualityNominationReviewers a eliminar
     */
    final public static function delete(QualityNominationReviewers $QualityNomination_Reviewers) {
        $sql = 'DELETE FROM `QualityNomination_Reviewers` WHERE qualitynomination_id = ? AND user_id = ?;';
        $params = [$QualityNomination_Reviewers->qualitynomination_id, $QualityNomination_Reviewers->user_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link QualityNominationReviewers}.
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
     * @return Array Un arreglo que contiene objetos del tipo {@link QualityNominationReviewers}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT `QualityNomination_Reviewers`.`qualitynomination_id`, `QualityNomination_Reviewers`.`user_id` from QualityNomination_Reviewers';
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
            $allData[] = new QualityNominationReviewers($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto QualityNominationReviewers suministrado.
     *
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param QualityNominationReviewers [$QualityNomination_Reviewers] El objeto de tipo QualityNominationReviewers a crear.
     */
    final public static function create(QualityNominationReviewers $QualityNomination_Reviewers) {
        $sql = 'INSERT INTO QualityNomination_Reviewers (`qualitynomination_id`, `user_id`) VALUES (?, ?);';
        $params = [
            $QualityNomination_Reviewers->qualitynomination_id,
            $QualityNomination_Reviewers->user_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
