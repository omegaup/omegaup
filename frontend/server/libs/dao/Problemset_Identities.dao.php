<?php

include_once('base/Problemset_Identities.dao.base.php');
include_once('base/Problemset_Identities.vo.base.php');
/** ProblemsetIdentities Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsetIdentities }.
  * @access public
  *
  */
class ProblemsetIdentitiesDAO extends ProblemsetIdentitiesDAOBase {
    public static function CheckAndSaveFirstTimeAccess(
        $identity_id,
        $problemset_id,
        $grant_access = false,
        $share_user_information = false
    ) {
        $problemset_identity = self::getByPK($identity_id, $problemset_id);
        if (is_null($problemset_identity)) {
            if (!$grant_access) {
                // User was not authorized to do this.
                throw new ForbiddenAccessException();
            }
            $problemset_identity = new ProblemsetIdentities();
            $problemset_identity->identity_id = $identity_id;
            $problemset_identity->problemset_id = $problemset_id;
            $problemset_identity->access_time = date('Y-m-d H:i:s');
            $problemset_identity->score = 0;
            $problemset_identity->time = 0;
            $problemset_identity->is_invited = 0;
            $problemset_identity->share_user_information = $share_user_information;
            ProblemsetIdentitiesDAO::save($problemset_identity);
        } elseif (is_null($problemset_identity->access_time)) {
            // If its set to default time, update it
            $problemset_identity->access_time = date('Y-m-d H:i:s');
            $problemset_identity->share_user_information = $share_user_information;
            ProblemsetIdentitiesDAO::save($problemset_identity);
        }
        return $problemset_identity;
    }

    public static function getWithExtraInformation($problemset_id) {
        $sql = 'SELECT
                    pi.access_time,
                    i.username,
                    i.country_id,
                    IF(a.owner_id=i.identity_id, 1, NULL) as is_owner
                FROM
                    Problemset_Identities pi
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = pi.identity_id
                INNER JOIN
                    Problemsets p
                ON
                    p.problemset_id = pi.problemset_id
                INNER JOIN
                    ACLs a
                ON
                    a.acl_id = p.acl_id
                WHERE
                    p.problemset_id = ?;';

        global $conn;
        return $conn->GetAll($sql, [$problemset_id]);
    }

    final public static function getIdentitiesByProblemset($problemset_id) {
        $sql = '
            SELECT
                i.user_id,
                i.username,
                pi.access_time,
                pi.is_invited,
                e.email,
                i.country_id,
                pi.access_time
            FROM
                Identities i
            INNER JOIN
                Problemset_Identities pi
            ON
                pi.identity_id = i.identity_id
            LEFT JOIN
                Emails e
            ON
                e.user_id = i.user_id
            WHERE
                pi.problemset_id = ?;';

        global $conn;
        return $conn->GetAll($sql, [$problemset_id]);
    }

    public static function updatePrivacyStatementConsent(ProblemsetIdentities $problemset_identity) {
        $sql = 'UPDATE
                    `Problemset_Identities`
                SET
                    `privacystatement_consent_id` = ?
                WHERE
                    `identity_id` = ?
                    AND `problemset_id` = ?;';
        $params = [
            $problemset_identity->privacystatement_consent_id,
            $problemset_identity->identity_id,
            $problemset_identity->problemset_id,
        ];

        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
