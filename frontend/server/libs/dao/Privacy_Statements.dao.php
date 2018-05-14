<?php

include_once('base/Privacy_Statements.dao.base.php');
include_once('base/Privacy_Statements.vo.base.php');
/** PrivacyStatements Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link PrivacyStatements }.
  * @access public
  *
  */
class PrivacyStatementsDAO extends PrivacyStatementsDAOBase {
    public static function getLastPrivacyPolicyPublished() {
        $sql = 'SELECT
                  privacystatement_id
                FROM
                  `Privacy_Statements`
                WHERE
                  type = \'privacy_policy\'
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        global $conn;
        return $conn->GetOne($sql);
    }
}
