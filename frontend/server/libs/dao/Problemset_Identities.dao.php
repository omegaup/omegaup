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
        $identityId,
        $problemsetId,
        $windowLength,
        $grantAccess = false,
        $shareUserInformation = false
    ) {
        $problemsetIdentity = self::getByPK($identityId, $problemsetId);
        if (is_null($problemsetIdentity)) {
            if (!$grantAccess) {
                // User was not authorized to do this.
                throw new ForbiddenAccessException();
            }
            $problemsetIdentity = new ProblemsetIdentities();
            $problemsetIdentity->identity_id = $identityId;
            $problemsetIdentity->problemset_id = $problemsetId;
            $problemsetIdentity->access_time = date('Y-m-d H:i:s', Time::get());
            $problemsetIdentity->end_time = date('Y-m-d H:i:s', Time::get() + $windowLength * 60);
            $problemsetIdentity->score = 0;
            $problemsetIdentity->time = 0;
            $problemsetIdentity->is_invited = 0;
            $problemsetIdentity->share_user_information = $shareUserInformation;
            self::save($problemsetIdentity);
        } elseif (is_null($problemsetIdentity->access_time)) {
            // If its set to default time, update it
            $problemsetIdentity->access_time = date('Y-m-d H:i:s');
            $problemsetIdentity->end_time = date('Y-m-d H:i:s', Time::get() + $windowLength * 60);
            $problemsetIdentity->share_user_information = $shareUserInformation;
            self::save($problemsetIdentity);
        }
        return $problemsetIdentity;
    }

    public static function getWithExtraInformation($problemset_id) {
        $sql = 'SELECT
                    pi.access_time,
                    UNIX_TIMESTAMP(pi.end_time) as end_time,
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
                pi.identity_id,
                pi.access_time,
                pi.end_time,
                pi.is_invited,
                e.email,
                i.country_id
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

    public static function recalculateEndTimeForProblemsetIdentities($problemsetId, $windowLength) {
        $problemsetIdentities = self::getIdentitiesByProblemset($problemsetId);
        global $conn;

        ContestsDAO::transBegin();

        try {
            foreach ($problemsetIdentities as $problemsetIdentity) {
                if (is_null($problemsetIdentity['end_time'])) {
                    continue;
                }
                // Window length is in minutes
                $endTime = date('Y-m-d H:i:s', strtotime($problemsetIdentity['access_time']) + $windowLength * 60);
                $sql = 'UPDATE
                            `Problemset_Identities`
                        SET
                            `end_time` = ?
                        WHERE
                            `identity_id` = ?
                            AND `problemset_id` = ?;';
                $params = [
                    $endTime,
                    $problemsetIdentity['identity_id'],
                    $problemsetId,
                ];
                $conn->Execute($sql, $params);
            }
            ContestsDAO::transEnd();
        } catch (Exception $e) {
            ContestsDAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }
    }

    public static function updateEndTimeForIdentity($contestAlias, $username, $endTime) {
        $sql = 'UPDATE
                    `Problemset_Identities`
                INNER JOIN
                    `Identities` ON `Problemset_Identities`.`identity_id` = `Identities`.`identity_id`
                INNER JOIN
                    `Contests` ON `Problemset_Identities`.`problemset_id` = `Contests`.`problemset_id`
                SET
                    `end_time` = ?
                WHERE
                    `Identities`.`username` = ?
                    AND `Contests`.`alias` = ?;';
        $params = [
            $endTime,
            $username,
            $contestAlias,
        ];

        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
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
