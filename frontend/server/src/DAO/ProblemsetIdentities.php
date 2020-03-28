<?php

namespace OmegaUp\DAO;

/**
 * ProblemsetIdentities Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsetIdentities}.
 *
 * @access public
 */
class ProblemsetIdentities extends \OmegaUp\DAO\Base\ProblemsetIdentities {
    public static function checkProblemsetOpened(
        int $identityId,
        int $problemsetId
    ): bool {
        return !is_null(self::getByPK($identityId, $problemsetId));
    }

    public static function checkAndSaveFirstTimeAccess(
        \OmegaUp\DAO\VO\Identities $identity,
        object $container,
        bool $grantAccess = false,
        bool $shareUserInformation = false
    ): \OmegaUp\DAO\VO\ProblemsetIdentities {
        $currentTime = \OmegaUp\Time::get();
        $problemsetIdentity  = self::getByPK(
            $identity->identity_id,
            intval($container->problemset_id)
        );
        if (is_null($problemsetIdentity)) {
            // Identities that were added through a group are still considered
            // to be granted access.
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                intval($container->problemset_id)
            );
            $isInvited = (
                !is_null($problemset) &&
                \OmegaUp\DAO\GroupRoles::isContestant(
                    intval($identity->identity_id),
                    intval($problemset->acl_id)
                )
            );
            if (!$grantAccess && !$isInvited) {
                // User was not authorized to do this.
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $problemsetIdentity = new \OmegaUp\DAO\VO\ProblemsetIdentities([
                'identity_id' => $identity->identity_id,
                'problemset_id' => intval($container->problemset_id),
                'score' => 0,
                'time' => 0,
                'is_invited' => $isInvited,
                'share_user_information' => $shareUserInformation,
            ]);
        }
        if (is_null($problemsetIdentity->access_time)) {
            // If its set to default time, update it
            $problemsetIdentity->access_time = $currentTime;
            $finishTime = intval($container->finish_time);
            if (!empty($container->window_length)) {
                $finishTime = min(
                    $currentTime + intval($container->window_length) * 60,
                    $finishTime
                );
            }
            $problemsetIdentity->end_time = $finishTime;
            $problemsetIdentity->share_user_information = $shareUserInformation;
            \OmegaUp\DAO\ProblemsetIdentities::replace($problemsetIdentity);
        }
        return $problemsetIdentity;
    }

    /**
     * @return list<array{access_time: int|null, country_id: null|string, end_time: int|null, is_owner: int|null, username: string}>
     */
    public static function getWithExtraInformation(int $problemsetId): array {
        $sql = 'SELECT
                    UNIX_TIMESTAMP(pi.access_time) as access_time,
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

        /** @var list<array{access_time: int|null, country_id: null|string, end_time: int|null, is_owner: int|null, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * @return list<array{access_time: \OmegaUp\Timestamp|null, country_id: null|string, email: null|string, end_time: \OmegaUp\Timestamp|null, identity_id: int, is_invited: bool, user_id: int|null, username: string}>
     */
    final public static function getIdentitiesByProblemset(int $problemsetId): array {
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

        /** @var list<array{access_time: \OmegaUp\Timestamp|null, country_id: null|string, email: null|string, end_time: \OmegaUp\Timestamp|null, identity_id: int, is_invited: bool, user_id: int|null, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemsetId]
        );
    }

    /**
     * When end time is recalculated, it never can be greater than
     * contest finish time
     *
     */
    public static function recalculateEndTimeForProblemsetIdentities(
        \OmegaUp\DAO\VO\Contests $contest
    ): int {
        $sql = 'UPDATE
                    `Problemset_Identities`
                SET
                    `end_time` = LEAST(
                        FROM_UNIXTIME(?),
                        DATE_ADD(`access_time`, INTERVAL ? MINUTE)
                     )
                WHERE
                    Problemset_Identities.`problemset_id` = ?
                    AND `access_time` IS NOT NULL;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [
            $contest->finish_time,
            $contest->window_length,
            $contest->problemset_id
        ]);

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @param \OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments $container
     */
    public static function recalculateEndTimeAsFinishTime(
        $container
    ): int {
        $sql = 'UPDATE
                    `Problemset_Identities`
                SET
                    `end_time` = FROM_UNIXTIME(?)
                WHERE
                    `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [
                $container->finish_time,
                $container->problemset_id
            ]
        );

        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function updatePrivacyStatementConsent(
        \OmegaUp\DAO\VO\ProblemsetIdentities $problemsetIdentity
    ): int {
        $sql = 'UPDATE
                    `Problemset_Identities`
                SET
                    `privacystatement_consent_id` = ?
                WHERE
                    `identity_id` = ?
                    AND `problemset_id` = ?;';
        $params = [
            $problemsetIdentity->privacystatement_consent_id,
            $problemsetIdentity->identity_id,
            $problemsetIdentity->problemset_id,
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
