<?php

require_once('base/Problems_Tags.dao.base.php');

/**
 * ProblemsTags Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\ProblemsTags}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class ProblemsTagsDAO extends ProblemsTagsDAOBase {
    public static function getProblemTags(\OmegaUp\DAO\VO\Problems $problem, $public_only, $includeAutogenerated = false) {
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
        if (!$includeAutogenerated) {
            $sql .= ' AND pt.autogenerated = 0';
        }
        $sql .= ';';

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    public static function replaceAutogeneratedTags(\OmegaUp\DAO\VO\Problems $problem, $listOfTags) {
        self::transBegin();
        try {
            self::clearAutogeneratedTags($problem);
            foreach ($listOfTags as $tag) {
                $tagId = TagsDAO::getByName($tag)->tag_id;

                ProblemsTagsDAO::replace(new \OmegaUp\DAO\VO\ProblemsTags([
                    'problem_id' => $problem->problem_id,
                    'tag_id' => $tagId,
                    'public' => true,
                    'autogenerated' => true,
                ]));
            }
        } catch (Exception $e) {
            self::transRollback();
            self::$log->error("Couldn't replace autogenerated tags for problem {$problem->alias}");
        }
    }

    private static function clearAutogeneratedTags(\OmegaUp\DAO\VO\Problems $problem) {
        $sql = 'DELETE FROM
                        Problems_Tags
                WHERE
                        problem_id = ? && autogenerated = 1;';
        $params = [$problem->problem_id];
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    public static function clearRestrictedTags(\OmegaUp\DAO\VO\Problems $problem) {
        $placeholders = join(
            ',',
            array_fill(0, count(ProblemController::RESTRICTED_TAG_NAMES), '?')
        );
        $params = array_merge(ProblemController::RESTRICTED_TAG_NAMES, [$problem->problem_id]);
        $sql = "
            DELETE FROM
                `Problems_Tags`
            WHERE
                tag_id IN (
                    SELECT
                        tag_id
                    FROM
                        Tags
                    WHERE
                        name IN ($placeholders)
                ) AND problem_id = ?;";
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }
}
