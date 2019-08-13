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
     * Actualizar registros.
     *
     * @param QualityNominations $QualityNominations El objeto de tipo QualityNominations a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(QualityNominations $QualityNominations) : int {
        $sql = 'UPDATE `QualityNominations` SET `user_id` = ?, `problem_id` = ?, `nomination` = ?, `contents` = ?, `time` = ?, `status` = ? WHERE `qualitynomination_id` = ?;';
        $params = [
            (int)$QualityNominations->user_id,
            (int)$QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            DAO::toMySQLTimestamp($QualityNominations->time),
            $QualityNominations->status,
            (int)$QualityNominations->qualitynomination_id,
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
     * @return ?QualityNominations Un objeto del tipo {@link QualityNominations}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $qualitynomination_id) : ?QualityNominations {
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` FROM QualityNominations WHERE (qualitynomination_id = ?) LIMIT 1;';
        $params = [$qualitynomination_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new QualityNominations($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto QualityNominations suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param QualityNominations $QualityNominations El objeto de tipo QualityNominations a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(QualityNominations $QualityNominations) : void {
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
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param ?string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return array Un arreglo que contiene objetos del tipo {@link QualityNominations}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `QualityNominations`.`qualitynomination_id`, `QualityNominations`.`user_id`, `QualityNominations`.`problem_id`, `QualityNominations`.`nomination`, `QualityNominations`.`contents`, `QualityNominations`.`time`, `QualityNominations`.`status` from QualityNominations';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
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
     * @param QualityNominations $QualityNominations El objeto de tipo QualityNominations a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(QualityNominations $QualityNominations) : int {
        if (is_null($QualityNominations->nomination)) {
            $QualityNominations->nomination = 'suggestion';
        }
        if (is_null($QualityNominations->time)) {
            $QualityNominations->time = Time::get();
        }
        if (is_null($QualityNominations->status)) {
            $QualityNominations->status = 'open';
        }
        $sql = 'INSERT INTO QualityNominations (`user_id`, `problem_id`, `nomination`, `contents`, `time`, `status`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            (int)$QualityNominations->user_id,
            (int)$QualityNominations->problem_id,
            $QualityNominations->nomination,
            $QualityNominations->contents,
            DAO::toMySQLTimestamp($QualityNominations->time),
            $QualityNominations->status,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $QualityNominations->qualitynomination_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
