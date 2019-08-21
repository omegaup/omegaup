<?php

include_once('base/PrivacyStatements.dao.base.php');
include_once('base/PrivacyStatements.vo.base.php');
/** PrivacyStatements Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link PrivacyStatements }.
  * @access public
  *
  */
class PrivacyStatementsDAO extends PrivacyStatementsDAOBase {
    public static function getLatestPublishedStatement($statement_type = 'privacy_policy') {
        $sql = 'SELECT
                  privacystatement_id,
                  git_object_id
                FROM
                  `PrivacyStatements`
                WHERE
                  type = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        return \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$statement_type]);
    }

    public static function getId($git_object_id, $statement_type) {
        $sql = 'SELECT
                  privacystatement_id
                FROM
                  `PrivacyStatements`
                WHERE
                  git_object_id = ?
                  AND type = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$git_object_id, $statement_type]);
    }
}
