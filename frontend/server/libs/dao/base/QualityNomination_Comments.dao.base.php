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
 * {@link \OmegaUp\DAO\VO\QualityNominationComments}.
 * @access public
 * @abstract
 */
abstract class QualityNominationCommentsDAOBase {
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments El objeto de tipo QualityNominationComments a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(\OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments) : int {
        $sql = 'UPDATE `QualityNomination_Comments` SET `qualitynomination_id` = ?, `user_id` = ?, `time` = ?, `vote` = ?, `contents` = ? WHERE `qualitynomination_comment_id` = ?;';
        $params = [
            is_null($QualityNomination_Comments->qualitynomination_id) ? null : (int)$QualityNomination_Comments->qualitynomination_id,
            is_null($QualityNomination_Comments->user_id) ? null : (int)$QualityNomination_Comments->user_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($QualityNomination_Comments->time),
            is_null($QualityNomination_Comments->vote) ? null : (int)$QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
            (int)$QualityNomination_Comments->qualitynomination_comment_id,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link \OmegaUp\DAO\VO\QualityNominationComments} por llave primaria.
     *
     * Este metodo cargará un objeto {@link \OmegaUp\DAO\VO\QualityNominationComments}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\QualityNominationComments Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\QualityNominationComments} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(int $qualitynomination_comment_id) : ?\OmegaUp\DAO\VO\QualityNominationComments {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` FROM QualityNomination_Comments WHERE (qualitynomination_comment_id = ?) LIMIT 1;';
        $params = [$qualitynomination_comment_id];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\QualityNominationComments($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\QualityNominationComments} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments El
     * objeto de tipo \OmegaUp\DAO\VO\QualityNominationComments a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(\OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments) : void {
        $sql = 'DELETE FROM `QualityNomination_Comments` WHERE qualitynomination_comment_id = ?;';
        $params = [$QualityNomination_Comments->qualitynomination_comment_id];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\QualityNominationComments}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return \OmegaUp\DAO\VO\QualityNominationComments[] Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\QualityNominationComments}.
     *
     * @psalm-return array<int, \OmegaUp\DAO\VO\QualityNominationComments>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `QualityNomination_Comments`.`qualitynomination_comment_id`, `QualityNomination_Comments`.`qualitynomination_id`, `QualityNomination_Comments`.`user_id`, `QualityNomination_Comments`.`time`, `QualityNomination_Comments`.`vote`, `QualityNomination_Comments`.`contents` from QualityNomination_Comments';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . \OmegaUp\MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new \OmegaUp\DAO\VO\QualityNominationComments($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\QualityNominationComments}
     * suministrado.
     *
     * @param \OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments El
     * objeto de tipo {@link \OmegaUp\DAO\VO\QualityNominationComments} a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(\OmegaUp\DAO\VO\QualityNominationComments $QualityNomination_Comments) : int {
        $sql = 'INSERT INTO QualityNomination_Comments (`qualitynomination_id`, `user_id`, `time`, `vote`, `contents`) VALUES (?, ?, ?, ?, ?);';
        $params = [
            is_null($QualityNomination_Comments->qualitynomination_id) ? null : (int)$QualityNomination_Comments->qualitynomination_id,
            is_null($QualityNomination_Comments->user_id) ? null : (int)$QualityNomination_Comments->user_id,
            \OmegaUp\DAO\DAO::toMySQLTimestamp($QualityNomination_Comments->time),
            is_null($QualityNomination_Comments->vote) ? null : (int)$QualityNomination_Comments->vote,
            $QualityNomination_Comments->contents,
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $QualityNomination_Comments->qualitynomination_comment_id = \OmegaUp\MySQLConnection::getInstance()->Insert_ID();

        return $affectedRows;
    }
}
