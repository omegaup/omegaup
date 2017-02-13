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
class AssignmentsDAO extends AssignmentsDAOBase {
    public static function GetProblemset($assignmentAlias) {
        $sql = 'select p.* from Assignments a, Problemsets p where a.problemset_id = p.problemset_id and a.alias = ?;';
        $params = [$assignmentAlias];

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
        $counts = [];
        foreach ($rs as $row) {
            $counts[$row['assignment_type']] = $row['count'];
        }
        return $counts;
    }

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Assignments WHERE (alias = ?) LIMIT 1;';
        $params = [$alias];

        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return new Assignments($row);
    }
}
