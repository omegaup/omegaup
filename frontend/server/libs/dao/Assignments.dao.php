<?php

include('base/Assignments.dao.base.php');
include('base/Assignments.vo.base.php');

/** Assignments Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Assignments }.
  * @access public
  *
  */
class AssignmentsDAO extends AssignmentsDAOBase
{
    public static function GetProblemset($assignmentAlias)
    {
        $sql = 'select p.* from Assignments a, Problemsets p where a.problemset_id = p.problemset_id and a.alias = ?;';
        $params = array($assignmentAlias);

        global $conn;
        $rs = $conn->GetRow($sql, $params);

        if (count($rs) == 0) {
            return null;
        }

        return new Problemsets($rs);
    }

    public static function getAssignmentCountsForCourse($course_id) {
        global $conn;

        $sql = 'SELECT a.assignment_type, COUNT(*) AS count
                FROM Assignments a
                WHERE a.course_id = ?
                GROUP BY a.assignment_type;';
        $rs = $conn->Execute($sql, $course_id);
        $counts = array();
        foreach ($rs as $row) {
            $counts[$row['assignment_type']] = $row['count'];
        }
        return $counts;
    }

    public static function getAssignmentForProblemset($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        try {
            $assignments = self::search(new Assignments([
                'problemset_id' => $problemset_id,
            ]));
            if (count($assignments) === 1) {
                $course = CoursesDAO::getByPK($assignments[0]->course_id);
                $assignments[0]->acl_id = $course->acl_id;
                return $assignments[0];
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return null;
    }
}
