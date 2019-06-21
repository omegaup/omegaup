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
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link PrivacyStatementConsentLog}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param PrivacyStatementConsentLog [$PrivacyStatement_Consent_Log] El objeto de tipo PrivacyStatementConsentLog
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) {
        if (is_null(self::getByPK($PrivacyStatement_Consent_Log->privacystatement_consent_id))) {
            return PrivacyStatementConsentLogDAOBase::create($PrivacyStatement_Consent_Log);
        }
        return PrivacyStatementConsentLogDAOBase::update($PrivacyStatement_Consent_Log);
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param PrivacyStatementConsentLog [$PrivacyStatement_Consent_Log] El objeto de tipo PrivacyStatementConsentLog a actualizar.
     */
    final public static function update(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) {
        $sql = 'UPDATE `PrivacyStatement_Consent_Log` SET `identity_id` = ?, `privacystatement_id` = ?, `timestamp` = ? WHERE `privacystatement_consent_id` = ?;';
        $params = [
            is_null($PrivacyStatement_Consent_Log->identity_id) ? null : (int)$PrivacyStatement_Consent_Log->identity_id,
            is_null($PrivacyStatement_Consent_Log->privacystatement_id) ? null : (int)$PrivacyStatement_Consent_Log->privacystatement_id,
            $PrivacyStatement_Consent_Log->timestamp,
            is_null($PrivacyStatement_Consent_Log->privacystatement_consent_id) ? null : (int)$PrivacyStatement_Consent_Log->privacystatement_consent_id,
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
     * @static
     * @return @link PrivacyStatementConsentLog Un objeto del tipo {@link PrivacyStatementConsentLog}. NULL si no hay tal registro.
     */
    final public static function getByPK($privacystatement_consent_id) {
        if (is_null($privacystatement_consent_id)) {
            return null;
        }
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
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param PrivacyStatementConsentLog [$PrivacyStatement_Consent_Log] El objeto de tipo PrivacyStatementConsentLog a eliminar
     */
    final public static function delete(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) {
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
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link PrivacyStatementConsentLog}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
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
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param PrivacyStatementConsentLog [$PrivacyStatement_Consent_Log] El objeto de tipo PrivacyStatementConsentLog a crear.
     */
    final public static function create(PrivacyStatementConsentLog $PrivacyStatement_Consent_Log) {
        if (is_null($PrivacyStatement_Consent_Log->timestamp)) {
            $PrivacyStatement_Consent_Log->timestamp = gmdate('Y-m-d H:i:s', Time::get());
        }
        $sql = 'INSERT INTO PrivacyStatement_Consent_Log (`identity_id`, `privacystatement_id`, `timestamp`) VALUES (?, ?, ?);';
        $params = [
            is_null($PrivacyStatement_Consent_Log->identity_id) ? null : (int)$PrivacyStatement_Consent_Log->identity_id,
            is_null($PrivacyStatement_Consent_Log->privacystatement_id) ? null : (int)$PrivacyStatement_Consent_Log->privacystatement_id,
            $PrivacyStatement_Consent_Log->timestamp,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
        $PrivacyStatement_Consent_Log->privacystatement_consent_id = $conn->Insert_ID();

        return $ar;
    }
}
