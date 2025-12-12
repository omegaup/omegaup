<?php

namespace OmegaUp\DAO;

/**
 * CourseCloneLog Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CourseCloneLog}.
 *
 * @access public
 */
class CourseCloneLog extends \OmegaUp\DAO\Base\CourseCloneLog {
    /**
     * Count the number of course clone log entries for a given course_id.
     */
    final public static function countByCourseId(int $courseId): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `Course_Clone_Log`
            WHERE
                `course_id` = ?;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$courseId]
        );
        return intval($count);
    }
}
