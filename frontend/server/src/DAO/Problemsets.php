<?php

namespace OmegaUp\DAO;

/**
 * Problemsets Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Problemsets}.
 *
 * @access public
 */
class Problemsets extends \OmegaUp\DAO\Base\Problemsets {
    /**
     * @return list<array{assignment_type: null|string, container_alias: string, container_id: int, container_name: string, entity_type: 'assignment'|'contest', finish_time: \OmegaUp\Timestamp|null, item_alias: string, item_name: string, item_order: int|null}>
     */
    public static function getAdminContainersForProblemQuickAdd(
        int $identityId,
        int $currentTimestamp
    ): array {
        $adminRole = \OmegaUp\Authorization::ADMIN_ROLE;
        $taRole = \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE;

        $sql = '
            SELECT
                admin_problemsets.entity_type,
                admin_problemsets.container_alias,
                admin_problemsets.container_name,
                admin_problemsets.item_alias,
                admin_problemsets.item_name,
                admin_problemsets.assignment_type,
                admin_problemsets.finish_time,
                admin_problemsets.container_id,
                admin_problemsets.item_order
            FROM (
                SELECT DISTINCT
                    "assignment" AS entity_type,
                    c.alias AS container_alias,
                    c.name AS container_name,
                    a.alias AS item_alias,
                    a.name AS item_name,
                    a.assignment_type,
                    a.finish_time,
                    c.course_id AS container_id,
                    a.`order` AS item_order
                FROM
                    Courses c
                INNER JOIN
                    Assignments a ON a.course_id = c.course_id
                INNER JOIN
                    ACLs acl ON acl.acl_id = c.acl_id
                INNER JOIN
                    Identities owner ON owner.user_id = acl.owner_id
                LEFT JOIN
                    User_Roles ur ON ur.acl_id = c.acl_id
                LEFT JOIN
                    Identities uri
                ON
                    uri.user_id = ur.user_id
                    AND uri.identity_id = ?
                LEFT JOIN
                    Group_Roles gr ON gr.acl_id = c.acl_id
                LEFT JOIN
                    Groups_Identities gi
                ON
                    gi.group_id = gr.group_id
                    AND gi.identity_id = ?
                WHERE
                    c.archived = 0
                    AND (
                        owner.identity_id = ?
                        OR (ur.role_id IN (?, ?) AND uri.identity_id IS NOT NULL)
                        OR (gr.role_id IN (?, ?) AND gi.identity_id IS NOT NULL)
                    )
                    AND (
                        a.finish_time IS NULL
                        OR a.finish_time > FROM_UNIXTIME(?)
                    )

                UNION ALL

                SELECT DISTINCT
                    "contest" AS entity_type,
                    contest.alias AS container_alias,
                    contest.title AS container_name,
                    contest.alias AS item_alias,
                    contest.title AS item_name,
                    NULL AS assignment_type,
                    contest.finish_time,
                    contest.contest_id AS container_id,
                    NULL AS item_order
                FROM
                    Contests contest
                INNER JOIN
                    ACLs acl ON acl.acl_id = contest.acl_id
                INNER JOIN
                    Identities owner ON owner.user_id = acl.owner_id
                LEFT JOIN
                    User_Roles ur ON ur.acl_id = contest.acl_id
                LEFT JOIN
                    Identities uri
                ON
                    uri.user_id = ur.user_id
                    AND uri.identity_id = ?
                LEFT JOIN
                    Group_Roles gr ON gr.acl_id = contest.acl_id
                LEFT JOIN
                    Groups_Identities gi
                ON
                    gi.group_id = gr.group_id
                    AND gi.identity_id = ?
                WHERE
                    contest.archived = 0
                    AND contest.finish_time > FROM_UNIXTIME(?)
                    AND (
                        owner.identity_id = ?
                        OR (ur.role_id = ? AND uri.identity_id IS NOT NULL)
                        OR (gr.role_id = ? AND gi.identity_id IS NOT NULL)
                    )
            ) AS admin_problemsets
            ORDER BY
                admin_problemsets.entity_type ASC,
                admin_problemsets.container_id DESC,
                admin_problemsets.item_order ASC,
                admin_problemsets.item_name ASC;';

        /** @var list<array{assignment_type: null|string, container_alias: string, container_id: int, container_name: string, entity_type: 'assignment'|'contest', finish_time: \OmegaUp\Timestamp|null, item_alias: string, item_name: string, item_order: int|null}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                $identityId,
                $identityId,
                $identityId,
                $adminRole,
                $taRole,
                $adminRole,
                $taRole,
                $currentTimestamp,
                $identityId,
                $identityId,
                $currentTimestamp,
                $identityId,
                $adminRole,
                $adminRole,
            ]
        );
    }

    /**
     * @return null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments
     */
    public static function getProblemsetContainer(?int $problemsetId) {
        if (is_null($problemsetId)) {
            return null;
        }

        // Whenever I see a problemset I say it's used by a contest
        // and 99% of the time I'm right!
        $contest = \OmegaUp\DAO\Contests::getContestForProblemset(
            $problemsetId
        );
        if (!is_null($contest)) {
            return $contest;
        }

        $assignment = \OmegaUp\DAO\Assignments::getAssignmentForProblemset(
            $problemsetId
        );
        if (!is_null($assignment)) {
            return $assignment;
        }

        return null;
    }

    /**
     * Check whether a submission is before the deadline.
     * If the deadline is null, the submission could be made, otherwise, no
     * one, including admins, can submit after the deadline.
     *
     * @param \OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments $problemsetContainer
     */
    public static function isLateSubmission(
        $problemsetContainer,
        ?\OmegaUp\DAO\VO\ProblemsetIdentities $problemsetIdentity
    ): bool {
        if (empty($problemsetContainer->finish_time)) {
            return false;
        }
        if (
            !is_null($problemsetIdentity) &&
            !is_null($problemsetIdentity->end_time)
        ) {
            return (
                \OmegaUp\Time::get() > $problemsetIdentity->end_time->time
            );
        }
        /** @var \OmegaUp\Timestamp $problemsetContainer->finish_time */
        return \OmegaUp\Time::get() > $problemsetContainer->finish_time->time;
    }

    /**
     * @param \OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments $problemsetContainer
     */
    public static function isSubmissionWindowOpen($problemsetContainer): bool {
        /** @var \OmegaUp\Timestamp $problemsetContainer->start_time */
        return \OmegaUp\Time::get() >= $problemsetContainer->start_time->time;
    }

    /**
     * @return array{assignment: null|string, contest_alias: null|string, course: null|string, type: string}|null
     */
    public static function getWithTypeByPK(int $problemsetId): ?array {
        $sql = 'SELECT
                    type,
                    c.alias AS contest_alias,
                    a.alias AS assignment,
                    cu.alias AS course
                FROM
                    Problemsets p
                LEFT JOIN
                    Assignments a
                ON
                    p.problemset_id = a.problemset_id
                LEFT JOIN
                    Courses cu
                ON
                    a.course_id = cu.course_id
                LEFT JOIN
                    Contests c
                ON
                    p.problemset_id = c.problemset_id
                WHERE
                    p.problemset_id = ?
                LIMIT
                    1;';
        $params = [$problemsetId];

        /** @var array{assignment: null|string, contest_alias: null|string, course: null|string, type: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            $params
        );
    }

    /**
     * Checks whether users have made submissions with any of their associated
     * identities and are currently logged in with one of them.
     * In this case a flag is turned on and a message will be displayed in arena
     *
     * @param \OmegaUp\DAO\VO\Users $user
     */
    public static function shouldShowFirstAssociatedIdentityRunWarning(
        \OmegaUp\DAO\VO\Users $user
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            INNER JOIN
                Users u
            ON
                u.user_id = i.user_id
            WHERE
                u.user_id = ?
                AND u.main_identity_id != i.identity_id
            LIMIT
                1;';

        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$user->user_id]
        ) == '0';
    }

    public static function hasSubmissions(
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): bool {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Submissions s
                WHERE
                    s.problemset_id = ?
                LIMIT
                    1;';

        /** @var int */
        $hasSubmissions = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$problemset->problemset_id]
        );
        return $hasSubmissions > 0;
    }
}
