<?php

require_once('base/Problems_Tags.dao.base.php');
require_once('base/Problems_Tags.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** ProblemsTags Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemsTags }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ProblemsTagsDAO extends ProblemsTagsDAOBase {
    public static function getProblemTags(Problems $problem, $public_only) {
        $sql = '
			SELECT
				t.name, pt.public
			FROM
				Problems_Tags pt
			INNER JOIN
				Tags t on t.tag_id = pt.tag_id
			WHERE
				pt.problem_id = ?';
        $params = [$problem->problem_id];
        if ($public_only) {
            $sql .= ' AND pt.public = 1';
        }
        $sql .= ';';

        global $conn;
        return $conn->GetAll($sql, $params);
    }
}
