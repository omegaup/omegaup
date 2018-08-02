<?php

require_once('base/Problems_Languages.dao.base.php');
require_once('base/Problems_Languages.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** ProblemsLanguages Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsLanguages }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ProblemsLanguagesDAO extends ProblemsLanguagesDAOBase {
    final public static function deleteProblemLanguages(ProblemsLanguages $problems_languages) {
        $sql = 'DELETE FROM `Problems_Languages` WHERE problem_id = ?;';
        $params = [$problems_languages->problem_id];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    final public static function getByProblemId($problemId) {
        $sql = 'SELECT
                    *
                FROM
                    Problems_Languages
                WHERE
                    problem_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$problemId]);

        $problemsLanguages = [];
        foreach ($rs as $row) {
            array_push($problemsLanguages, new ProblemsLanguages($row));
        }
        return $problemsLanguages;
    }
}
