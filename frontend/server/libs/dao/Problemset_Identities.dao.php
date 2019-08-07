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
    public static function checkProblemsetOpened(
        int $identityId,
        int $problemsetId
    ) : bool {
        return !is_null(self::getByPK($identityId, $problemsetId));
    }

    public static function checkAndSaveFirstTimeAccess(
        Identities $identity,
        Object $container,
        bool $grantAccess = false,
        bool $shareUserInformation = false
    ) : ProblemsetIdentities {
        $currentTime = Time::get();
        $problemsetIdentity  = self::getByPK(
            $identity->identity_id,
            $container->problemset_id
        );
        $isNewProblemsetIdentity = is_null($problemsetIdentity);
        if ($isNewProblemsetIdentity) {
            if (!$grantAccess) {
                // User was not authorized to do this.
                throw new ForbiddenAccessException();
            }
            $problemsetIdentity = new ProblemsetIdentities([
                'identity_id' => $identity->identity_id,
                'problemset_id' => $container->problemset_id,
                'score' => 0,
                'time' => 0,
                'is_invited' => 0,
            ]);
        }
        if (is_null($problemsetIdentity->access_time)) {
            // If its set to default time, update it
            $problemsetIdentity->access_time = gmdate('Y-m-d H:i:s', $currentTime);
            if (!empty($container->window_length)) {
                $container->finish_time = min(
                    $currentTime + $container->window_length * 60,
                    $container->finish_time
                );
            }
            $problemsetIdentity->end_time = gmdate('Y-m-d H:i:s', $container->finish_time);
            $problemsetIdentity->share_user_information = $shareUserInformation;
            if ($isNewProblemsetIdentity) {
                self::create($problemsetIdentity);
            } else {
                self::update($problemsetIdentity);
            }
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

    /**
     * When end time is recalculated, it never can be greater than
     * contest finish time
     *
     */
    public static function recalculateEndTimeForProblemsetIdentities(
        Contests $contest
    ) : int {
        $sql = 'UPDATE
                    `Problemset_Identities`
                INNER JOIN
                    `Contests`
                ON
                    Problemset_Identities.problemset_id = Contests.problemset_id
                SET
                    `end_time` = LEAST(
                        `finish_time`,
                        DATE_ADD(`access_time`, INTERVAL ? MINUTE)
                     )
                WHERE
                    Problemset_Identities.`problemset_id` = ?
                    AND `access_time` IS NOT NULL;';

        global $conn;
        $conn->Execute($sql, [$contest->window_length, $contest->problemset_id]);

        return $conn->Affected_Rows();
    }

    public static function recalculateEndTimeAsFinishTime(
        Contests $contest
    ) : int {
        $sql = 'UPDATE
                    `Problemset_Identities`
                SET
                    `end_time` = ?
                WHERE
                    `problemset_id` = ?;';

        global $conn;
        $conn->Execute($sql, [$contest->finish_time, $contest->problemset_id]);

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
