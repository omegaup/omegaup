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
    public static function getProblemsetContainer($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        // Whenever I see a problemset I say it's used by a contest
        // and 99% of the time I'm right!
        $contest = \OmegaUp\DAO\Contests::getContestForProblemset(
            $problemset_id
        );
        if (!is_null($contest)) {
            return $contest;
        }

        $assignment = \OmegaUp\DAO\Assignments::getAssignmentForProblemset(
            $problemset_id
        );
        if (!is_null($assignment)) {
            return $assignment;
        }

        $interview = \OmegaUp\DAO\Interviews::getInterviewForProblemset(
            $problemset_id
        );
        if (!is_null($interview)) {
            return $interview;
        }

        return null;
    }

    /**
     *  Check whether a submission is before the deadline.
     *  No one, including admins, can submit after the deadline.
     */
    public static function isLateSubmission(
        object $container,
        ?\OmegaUp\DAO\VO\ProblemsetIdentities $problemsetIdentity
    ): bool {
        if (is_null($problemsetIdentity)) {
            return isset($container->finish_time) &&
                   (\OmegaUp\Time::get() > $container->finish_time);
        }
        return \OmegaUp\Time::get() > $problemsetIdentity->end_time;
    }

    public static function isSubmissionWindowOpen(object $container): bool {
        return isset($container->start_time) &&
                \OmegaUp\Time::get() >= $container->start_time;
    }

    public static function getWithTypeByPK($problemset_id) {
        $sql = 'SELECT
                    type,
                    c.alias AS contest_alias,
                    a.alias AS assignment,
                    cu.alias AS course,
                    i.alias AS interview_alias
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
                LEFT JOIN
                    Interviews i
                ON
                    p.problemset_id = i.problemset_id
                WHERE
                    p.problemset_id = ?
                LIMIT
                    1;';
        $params = [$problemset_id];

        $problemset = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            $params
        );
        if (empty($problemset)) {
            return null;
        }

        return $problemset;
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
}
