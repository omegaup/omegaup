<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** PrivacyStatementConsentLog Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link PrivacyStatementConsentLog}.
 * @access public
 * @abstract
 *
 */
abstract class PrivacyStatementConsentLogDAOBase {
    /**
     * Actualizar registros.
     *
     * @param PrivacyStatementConsentLog $PrivacyStatement_Consent_Log El objeto de tipo PrivacyStatementConsentLog a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) : int {
        $sql = 'UPDATE `PrivacyStatement_Consent_Log` SET `identity_id` = ?, `privacystatement_id` = ?, `timestamp` = ? WHERE `privacystatement_consent_id` = ?;';
        $params = [
            is_null($PrivacyStatement_Consent_Log->identity_id) ? null : (int)$PrivacyStatement_Consent_Log->identity_id,
            is_null($PrivacyStatement_Consent_Log->privacystatement_id) ? null : (int)$PrivacyStatement_Consent_Log->privacystatement_id,
            DAO::toMySQLTimestamp($PrivacyStatement_Consent_Log->timestamp),
            (int)$PrivacyStatement_Consent_Log->privacystatement_consent_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Obtener {@link PrivacyStatementConsentLog} por llave primaria.
     *
     * Este metodo cargará un objeto {@link PrivacyStatementConsentLog} de la base
     * de datos usando sus llaves primarias.
     *
     * @return ?PrivacyStatementConsentLog Un objeto del tipo {@link PrivacyStatementConsentLog}. NULL si no hay tal registro.
     */
    final public static function getByPK(int $privacystatement_consent_id) : ?PrivacyStatementConsentLog {
        $sql = 'SELECT `PrivacyStatement_Consent_Log`.`privacystatement_consent_id`, `PrivacyStatement_Consent_Log`.`identity_id`, `PrivacyStatement_Consent_Log`.`privacystatement_id`, `PrivacyStatement_Consent_Log`.`timestamp` FROM PrivacyStatement_Consent_Log WHERE (privacystatement_consent_id = ?) LIMIT 1;';
        $params = [$privacystatement_consent_id];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new PrivacyStatementConsentLog($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto PrivacyStatementConsentLog suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link replace()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link NotFoundException}
     * será arrojada.
     *
     * @param PrivacyStatementConsentLog $PrivacyStatement_Consent_Log El objeto de tipo PrivacyStatementConsentLog a eliminar
     *
     * @throws NotFoundException Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) : void {
        $sql = 'DELETE FROM `PrivacyStatement_Consent_Log` WHERE privacystatement_consent_id = ?;';
        $params = [$PrivacyStatement_Consent_Log->privacystatement_consent_id];
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
     * y construirá un arreglo que contiene objetos de tipo {@link PrivacyStatementConsentLog}.
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
     * @return PrivacyStatementConsentLog[] Un arreglo que contiene objetos del tipo {@link PrivacyStatementConsentLog}.
     *
     * @psalm-return array<int, PrivacyStatementConsentLog>
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        ?string $orden = null,
        string $tipoDeOrden = 'ASC'
    ) : array {
        $sql = 'SELECT `PrivacyStatement_Consent_Log`.`privacystatement_consent_id`, `PrivacyStatement_Consent_Log`.`identity_id`, `PrivacyStatement_Consent_Log`.`privacystatement_id`, `PrivacyStatement_Consent_Log`.`timestamp` from PrivacyStatement_Consent_Log';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new PrivacyStatementConsentLog($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto PrivacyStatementConsentLog suministrado.
     *
     * @param PrivacyStatementConsentLog $PrivacyStatement_Consent_Log El objeto de tipo PrivacyStatementConsentLog a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function create(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) : int {
        $sql = 'INSERT INTO PrivacyStatement_Consent_Log (`identity_id`, `privacystatement_id`, `timestamp`) VALUES (?, ?, ?);';
        $params = [
            is_null($PrivacyStatement_Consent_Log->identity_id) ? null : (int)$PrivacyStatement_Consent_Log->identity_id,
            is_null($PrivacyStatement_Consent_Log->privacystatement_id) ? null : (int)$PrivacyStatement_Consent_Log->privacystatement_id,
            DAO::toMySQLTimestamp($PrivacyStatement_Consent_Log->timestamp),
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $affectedRows = $conn->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
        $PrivacyStatement_Consent_Log->privacystatement_consent_id = $conn->Insert_ID();

        return $affectedRows;
    }
}
