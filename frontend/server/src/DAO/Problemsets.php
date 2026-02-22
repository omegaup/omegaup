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
     * @return null|\OmegaUp\DAO\VO\Contests|\OmegaUp\DAO\VO\Assignments
     */
    public static function getProblemsetContainer(?int $problemsetId) {
        if ($problemsetId === null) {
            return null;
        }

        // Whenever I see a problemset I say it's used by a contest
        // and 99% of the time I'm right!
        $contest = \OmegaUp\DAO\Contests::getContestForProblemset(
            $problemsetId
        );
        if ($contest !== null) {
            return $contest;
        }

        $assignment = \OmegaUp\DAO\Assignments::getAssignmentForProblemset(
            $problemsetId
        );
        if ($assignment !== null) {
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
            $problemsetIdentity !== null &&
            $problemsetIdentity->end_time !== null
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
