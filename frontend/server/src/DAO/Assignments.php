<?php

namespace OmegaUp\DAO;

/**
 * Assignments Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Assignments}.
 *
 * @access public
 * @return \OmegaUp\DAO\VO\Problemsets|null
 */
class Assignments extends \OmegaUp\DAO\Base\Assignments {
    public static function getProblemset(
        int $courseId,
        string $assignmentAlias
    ): ?\OmegaUp\DAO\VO\Problemsets {
        $sql = 'SELECT
                    p.*
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets p
                ON
                    a.problemset_id = p.problemset_id
                WHERE
                    a.course_id = ?
                    AND a.alias = ?';
        $params = [$courseId, $assignmentAlias];

        /** @var array{access_mode: string, acl_id: int, assignment_id: int|null, contest_id: int|null, interview_id: int|null, languages: null|string, needs_basic_information: bool, problemset_id: int, requests_user_information: string, scoreboard_url: string, scoreboard_url_admin: string, type: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Problemsets($rs);
    }

    /**
     * @return array<string, int>
     */
    public static function getAssignmentCountsForCourse(int $courseId): array {
        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        /** @var list<array{assignment_type: string, count: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
        $counts = [];
        foreach ($rs as $row) {
            $counts[$row['assignment_type']] = intval($row['count']);
        }
        return $counts;
    }

    public static function getAssignmentForProblemset(?int $problemsetId): ?\OmegaUp\DAO\VO\Assignments {
        if (is_null($problemsetId)) {
            return null;
        }

        return self::getByProblemset($problemsetId);
    }

    final public static function getByProblemset(int $problemsetId): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT * FROM Assignments WHERE (problemset_id = ?) LIMIT 1;';
        $params = [$problemsetId];

        /** @var array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    final public static function getByAliasAndCourse(
        ?string $assignmentAlias,
        int $courseId
    ): ?\OmegaUp\DAO\VO\Assignments {
        $sql = 'SELECT
                    *
                FROM
                    Assignments
                WHERE
                    course_id = ?
                AND
                    alias = ?
                LIMIT 1;';

        /** @var array{acl_id: int, alias: string, assignment_id: int, assignment_type: string, course_id: int, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, start_time: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$courseId, $assignmentAlias]
        );
        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Assignments($row);
    }

    /**
     * @return null|array{scoreboard_url: string, scoreboard_url_admin: string}
     */
    final public static function getByIdWithScoreboardUrls(int $assignmentId) {
        $sql = '
                SELECT
                   ps.scoreboard_url,
                   ps.scoreboard_url_admin
                FROM
                    Assignments a
                INNER JOIN
                    Problemsets ps
                ON
                    ps.problemset_id = a.problemset_id
                WHERE
                    a.assignment_id = ? LIMIT 1;';
        /** @var null|array{scoreboard_url: string, scoreboard_url_admin: string} */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$assignmentId]
        );
    }

    /**
      * Update assignments order.
      */
    final public static function updateAssignmentsOrder(
        int $assignmentId,
        int $order
    ): int {
        $sql = 'UPDATE `Assignments` SET `order` = ? WHERE `assignment_id` = ?;';
        $params = [
            $order,
            $assignmentId,
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Get the course assigments sorted by order and start_time
     *
     * @return list<array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}>
     */
    final public static function getSortedCourseAssignments(
        int $courseId
    ): array {
        $sql = '
            SELECT
               `a`.`problemset_id`,
               `a`.`name`,
               `a`.`description`,
               `a`.`alias`,
               `a`.`assignment_type`,
               `a`.`start_time`,
               `a`.`finish_time`,
               `a`.`max_points`,
               `a`.`publish_time_delay`,
               `a`.`order`,
               `ps`.`scoreboard_url`,
               `ps`.`scoreboard_url_admin`
            FROM
                `Assignments` `a`
            INNER JOIN
                `Problemsets` `ps`
            ON
                `ps`.`problemset_id` = `a`.`problemset_id`
            WHERE
                course_id = ?
            ORDER BY
                `order` ASC,
                `start_time` ASC,
                `a`.`assignment_id` ASC;
        ';

        /** @var list<array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$courseId]
        );
    }

    /**
     * Since Problemsets and Assignments tables are related to each other, it
     * is necessary to unlink the assignment in Problemsets table.
     */
    public static function unlinkProblemset(
        \OmegaUp\DAO\VO\Assignments $assignment,
        \OmegaUp\DAO\VO\Problemsets $problemset
    ): void {
        $sql = '
            UPDATE
                `Problemsets`
            SET
                `assignment_id` = NULL
            WHERE
                `problemset_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$problemset->problemset_id]
        );
    }
}
