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
                  PrivacyStatement_Consent_Log pscl
                INNER JOIN
                  (SELECT
                    privacystatement_id
                  FROM
                    `PrivacyStatements`
                  WHERE
                    type = \'privacy_policy\'
                  ORDER BY
                    privacystatement_id DESC
                  LIMIT 1) ps
                ON
                  pscl.privacystatement_id = ps.privacystatement_id
                WHERE
                  pscl.identity_id = ?
               ';
        global $conn;
        return $conn->GetOne($sql, [$identity_id]);
    }

    public static function saveLog($identity_id) {
        $timestamp = gmdate('Y-m-d H:i:s');
        $sql = 'INSERT INTO
                  PrivacyStatement_Consent_Log (
                    `identity_id`,
                    `privacystatement_id`,
                    `timestamp`
                  )
                VALUES (
                  ?,
                  (SELECT
                     privacystatement_id
                   FROM
                     `PrivacyStatements`
                   WHERE
                     type = \'privacy_policy\'
                   ORDER BY
                     privacystatement_id DESC
                   LIMIT 1),
                  ?);';
        $params = [$identity_id, $timestamp,];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }

        return $ar;
    }
}
