<?php

include_once('base/PrivacyStatement_Consent_Log.dao.base.php');
include_once('base/PrivacyStatement_Consent_Log.vo.base.php');
/** PrivacyStatementConsentLog Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link PrivacyStatementConsentLog }.
  * @access public
  *
  */
class PrivacyStatementConsentLogDAO extends PrivacyStatementConsentLogDAOBase {
    public static function saveLog($identity_id, $type, $acl_id, $share_user_information) {
        $sql = 'INSERT INTO
                  PrivacyStatement_Consent_Log (
                    `identity_id`,
                    `privacystatement_id`,
                    `acl_id`,
                    `share_user_information`
                  )
                SELECT
                  ?,
                  privacystatement_id,
                  ?,
                  ?
                FROM
                  `PrivacyStatements`
                WHERE
                  type = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1;';
        $params = [$identity_id, $acl_id, $share_user_information, $type];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
