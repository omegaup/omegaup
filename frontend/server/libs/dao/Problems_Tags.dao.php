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

    public static function replaceAutogeneratedTags(Problems $problem, $listOfTags) {
        self::transBegin();

        try {
            self::clearAutogeneratedTags($problem);
            foreach ($listOfTags as $tag) {
                $tag_id = TagsDAO::getByName($tag)->tag_id;

                $problem_tag = new ProblemsTags();
                $problem_tag->problem_id = $problem->problem_id;
                $problem_tag->tag_id = $tag_id;
                $problem_tag->public = 1;
                $problem_tag->autogenerated = 1;
                ProblemsTagsDAOBase::save($problem_tag);
            }
            self::transEnd();
        } catch (Exception $e) {
            QualityNominationsDAO::transRollback();
            self::$log->error("Couldn't replace autogenerated tags for problem with id " . $problem_id);
        }
    }

    private static function clearAutogeneratedTags(Problems $problem) {
        $sql = 'DELETE FROM
                        Problems_Tags
                WHERE
                        problem_id = ? && autogenerated = 1;';
        $params = [$problem->problem_id];
        global $conn;
        return $conn->Execute($sql, $params);
    }
}
