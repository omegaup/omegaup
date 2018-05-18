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
    public static function hasAcceptedLatestPrivacyPolicy($identity_id) {
        $sql = 'SELECT
                  COUNT(1)
                FROM
                  `PrivacyStatement_Consent_Log` pscl
                WHERE
                  pscl.identity_id = ?
                  AND pscl.privacystatement_id = (
                    SELECT
                      MAX(privacystatement_id)
                    FROM
                      PrivacyStatements ps
                    WHERE
                      ps.type = \'privacy_policy\'
                    )
               ';
        global $conn;
        return $conn->GetOne($sql, [$identity_id]) > 0;
    }

    public static function saveLog($identity_id) {
        $sql = 'INSERT INTO
                  PrivacyStatement_Consent_Log (
                    `identity_id`,
                    `privacystatement_id`
                  )
                SELECT
                  ?,
                  privacystatement_id
                FROM
                  `PrivacyStatements`
                WHERE
                  type = \'privacy_policy\'
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1;';
        $params = [$identity_id];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
