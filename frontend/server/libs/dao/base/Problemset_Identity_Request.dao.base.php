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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link ProblemsetIdentityRequest}.
 * @access public
 * @abstract
 *
 */
abstract class ProblemsetIdentityRequestDAOBase {
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link ProblemsetIdentityRequest}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws Exception si la operacion fallo.
     *
     * @param ProblemsetIdentityRequest $Problemset_Identity_Request El objeto de tipo ProblemsetIdentityRequest
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(ProblemsetIdentityRequest $Problemset_Identity_Request) : int {
        if (empty($Problemset_Identity_Request->identity_id) || empty($Problemset_Identity_Request->problemset_id)) {
            throw new NotFoundException('recordNotFound');
        }
        $sql = 'REPLACE INTO Problemset_Identity_Request (`identity_id`, `problemset_id`, `request_time`, `last_update`, `accepted`, `extra_note`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            $Problemset_Identity_Request->identity_id,
            $Problemset_Identity_Request->problemset_id,
            DAO::toMySQLTimestamp($Problemset_Identity_Request->request_time),
            DAO::toMySQLTimestamp($Problemset_Identity_Request->last_update),
            !is_null($Problemset_Identity_Request->accepted) ? intval($Problemset_Identity_Request->accepted) : null,
            $Problemset_Identity_Request->extra_note,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Actualizar registros.
     *
     * @param ProblemsetIdentityRequest $Problemset_Identity_Request El objeto de tipo ProblemsetIdentityRequest a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(ProblemsetIdentityRequest $Problemset_Identity_Request) : int {
        $sql = 'UPDATE `Problemset_Identity_Request` SET `request_time` = ?, `last_update` = ?, `accepted` = ?, `extra_note` = ? WHERE `identity_id` = ? AND `problemset_id` = ?;';
        $params = [
            DAO::toMySQLTimestamp($Problemset_Identity_Request->request_time),
            DAO::toMySQLTimestamp($Problemset_Identity_Request->last_update),
            is_null($Problemset_Identity_Request->accepted) ? null : (int)$Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
            is_null($Problemset_Identity_Request->identity_id) ? null : (int)$Problemset_Identity_Request->identity_id,
            is_null($Problemset_Identity_Request->problemset_id) ? null : (int)$Problemset_Identity_Request->problemset_id,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        return MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Obtener {@link ProblemsetIdentityRequest} por llave primaria.
     *
     * Este metodo cargará un objeto {@link ProblemsetIdentityRequest} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?ProblemsetIdentityRequest Un objeto del tipo {@link ProblemsetIdentityRequest}. NULL si no hay tal registro.
     */
    final public static function getByPK(?int $identity_id, ?int $problemset_id) : ?ProblemsetIdentityRequest {
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` FROM Problemset_Identity_Request WHERE (identity_id = ? AND problemset_id = ?) LIMIT 1;';
        $params = [$identity_id, $problemset_id];
        $row = MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new ProblemsetIdentityRequest($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto ProblemsetIdentityRequest suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param ProblemsetIdentityRequest $Problemset_Identity_Request El objeto de tipo ProblemsetIdentityRequest a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(ProblemsetIdentityRequest $Problemset_Identity_Request) : void {
        $sql = 'DELETE FROM `Problemset_Identity_Request` WHERE identity_id = ? AND problemset_id = ?;';
        $params = [$Problemset_Identity_Request->identity_id, $Problemset_Identity_Request->problemset_id];

        MySQLConnection::getInstance()->Execute($sql, $params);
        if (MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link ProblemsetIdentityRequest}.
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
     * @return ProblemsetIdentityRequest[] Un arreglo que contiene objetos del tipo {@link ProblemsetIdentityRequest}.
     *
     * @psalm-return array<int, ProblemsetIdentityRequest>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `Problemset_Identity_Request`.`identity_id`, `Problemset_Identity_Request`.`problemset_id`, `Problemset_Identity_Request`.`request_time`, `Problemset_Identity_Request`.`last_update`, `Problemset_Identity_Request`.`accepted`, `Problemset_Identity_Request`.`extra_note` from Problemset_Identity_Request';
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . MySQLConnection::getInstance()->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql) as $row) {
            $allData[] = new ProblemsetIdentityRequest($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto ProblemsetIdentityRequest suministrado.
     *
     * @param ProblemsetIdentityRequest $Problemset_Identity_Request El objeto de tipo ProblemsetIdentityRequest a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(ProblemsetIdentityRequest $Problemset_Identity_Request) : int {
        $sql = 'INSERT INTO Problemset_Identity_Request (`identity_id`, `problemset_id`, `request_time`, `last_update`, `accepted`, `extra_note`) VALUES (?, ?, ?, ?, ?, ?);';
        $params = [
            is_null($Problemset_Identity_Request->identity_id) ? null : (int)$Problemset_Identity_Request->identity_id,
            is_null($Problemset_Identity_Request->problemset_id) ? null : (int)$Problemset_Identity_Request->problemset_id,
            DAO::toMySQLTimestamp($Problemset_Identity_Request->request_time),
            DAO::toMySQLTimestamp($Problemset_Identity_Request->last_update),
            is_null($Problemset_Identity_Request->accepted) ? null : (int)$Problemset_Identity_Request->accepted,
            $Problemset_Identity_Request->extra_note,
        ];
        MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }

        return $affectedRows;
    }
}
