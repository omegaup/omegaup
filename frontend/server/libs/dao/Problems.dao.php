<?php

require_once('base/Problems.dao.base.php');
require_once('base/Problems.vo.base.php');
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Problems Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ProblemsDAO extends ProblemsDAOBase {
    final private static function addTagFilter($identity_type, $identity_id, $tag, &$sql, &$args) {
        $add_identity_id = false;
        if ($identity_type === IDENTITY_ADMIN) {
            $public_check = '';
        } elseif ($identity_type === IDENTITY_NORMAL && !is_null($identity_id)) {
            $public_check = '(ptp.public OR id.identity_id = ?) AND ';
            $add_identity_id = true;
        } else {
            $public_check = 'ptp.public AND ';
        }
        if (is_string($tag)) {
            $sql .= ' INNER JOIN Problems_Tags ptp ON ptp.problem_id = p.problem_id';
            $sql .= ' INNER JOIN Tags t ON ptp.tag_id = t.tag_id';
            $sql .= " WHERE t.name = ? AND $public_check";
            $args[] = $tag;
            if ($add_identity_id) {
                $args[] = $identity_id;
            }
        } elseif (is_array($tag)) {
            // Look for problems matching ALL tags.
            $placeholders = array_fill(0, count($tag), '?');
            $placeholders = join(',', $placeholders);
            $sql .= "
                INNER JOIN (
                    SELECT
                        pt.problem_id,
                        BIT_AND(pt.public) as public
                    FROM
                        Problems_Tags pt
                    WHERE pt.tag_id IN (
                        SELECT t.tag_id
                        FROM Tags t
                        WHERE t.name in ($placeholders)
                    )
                    GROUP BY
                        pt.problem_id
                    HAVING
                        (COUNT(pt.tag_id) = ?)
                ) ptp ON ptp.problem_id = p.problem_id WHERE $public_check";
            $args = array_merge($args, $tag);
            $args[] = count($tag);
            if ($add_identity_id) {
                $args[] = $identity_id;
            }
        } else {
            $sql .= ' WHERE';
        }
    }

    final public static function byIdentityType(
        $identity_type,
        $language,
        $order,
        $mode,
        $offset,
        $rowcount,
        $query,
        $identity_id,
        $user_id,
        $tag,
        $min_visibility,
        &$total
    ) {
        global $conn;

        // Just in case.
        if ($mode !== 'asc' && $mode !== 'desc') {
            $mode = 'desc';
        }

        $language_join = '';
        if (!is_null($language)) {
            $language_join = '
                INNER JOIN
                    Problems_Languages ON Problems_Languages.problem_id = p.problem_id
                INNER JOIN
                    Languages ON Problems_Languages.language_id = Languages.language_id
                    AND Languages.name = \'' . $language . '\'';
        }

        // Use BINARY mode to force case sensitive comparisons when ordering by title.
        $collation = ($order === 'title') ? 'COLLATE utf8_bin' : '';

        $select = '';
        $sql= '';
        $args = [];

        if ($identity_type === IDENTITY_ADMIN) {
            $args = [$identity_id];
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(accepted, 1) + 1), 2)   AS points,
                    accepted / GREATEST(1, submissions)     AS ratio,
                    ROUND(100 * COALESCE(ps.score, 0))      AS score,
                    p.*';
            $sql = '
                FROM
                    Problems p
                LEFT JOIN (
                    SELECT
                        Problems.problem_id,
                        MAX(Runs.score) AS score
                    FROM
                        Problems
                    INNER JOIN
                        Runs ON Runs.problem_id = Problems.problem_id
                    INNER JOIN
                        Identities ON Identities.identity_id = ? AND Runs.identity_id = Identities.identity_id
                    GROUP BY
                        Problems.problem_id
                    ) ps ON ps.problem_id = p.problem_id' . $language_join;

            self::addTagFilter($identity_type, $identity_id, $tag, $sql, $args);
            if (!is_null($query)) {
                $sql .= " (p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%')) ";
                $args[] = $query;
                $args[] = $query;
            } else {
                // Finish the WHERE clause opened by addTagFilter
                $sql .= ' p.visibility > ?';
                $args[] = ProblemController::VISIBILITY_DELETED;
            }
        } elseif ($identity_type === IDENTITY_NORMAL && !is_null($identity_id)) {
            $select = '
                SELECT
                    ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                    p.accepted / GREATEST(1, p.submissions)     AS ratio,
                    ROUND(100 * COALESCE(ps.score, 0), 2)   AS score,
                    p.*';
            $sql = '
                FROM
                    Problems p
                INNER JOIN
                    ACLs a
                ON
                    a.acl_id = p.acl_id
                LEFT JOIN (
                    SELECT
                        pi.problem_id,
                        r.identity_id,
                        MAX(r.score) AS score
                    FROM
                        Problems pi
                    INNER JOIN
                        Runs r ON r.problem_id = pi.problem_id
                    INNER JOIN
                        Identities i ON i.identity_id = ? AND r.identity_id = i.identity_id
                    GROUP BY
                        pi.problem_id
                ) ps ON ps.problem_id = p.problem_id
                LEFT JOIN
                    User_Roles ur ON ur.user_id = ? AND p.acl_id = ur.acl_id AND ur.role_id = ?
                LEFT JOIN
                    Identities id ON id.identity_id = ? AND a.owner_id = id.user_id
                LEFT JOIN (
                    SELECT DISTINCT
                        gr.acl_id
                    FROM
                        Groups_Identities gi
                    INNER JOIN
                        Group_Roles gr ON gr.group_id = gi.group_id
                    WHERE gi.identity_id = ? AND gr.role_id = ?
                ) gr ON p.acl_id = gr.acl_id' . $language_join;
            $args[] = $identity_id;
            $args[] = $user_id;
            $args[] = Authorization::ADMIN_ROLE;
            $args[] = $identity_id;
            $args[] = $identity_id;
            $args[] = Authorization::ADMIN_ROLE;

            self::addTagFilter($identity_type, $identity_id, $tag, $sql, $args);
            $sql .= '
                (p.visibility >= ? OR id.identity_id = ? OR ur.acl_id IS NOT NULL OR gr.acl_id IS NOT NULL) AND p.visibility > ?';
            $args[] = max(ProblemController::VISIBILITY_PUBLIC, $min_visibility);
            $args[] = $identity_id;
            $args[] = ProblemController::VISIBILITY_DELETED;

            if (!is_null($query)) {
                $sql .= " AND (p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%'))";
                $args[] = $query;
                $args[] = $query;
            }
        } elseif ($identity_type === IDENTITY_ANONYMOUS) {
            $select = '
                    SELECT
                        0 AS score,
                        ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                        accepted / GREATEST(1, p.submissions)   AS ratio,
                        p.*';
            $sql = '
                    FROM
                        Problems p' . $language_join;

            self::addTagFilter($identity_type, $identity_id, $tag, $sql, $args);
            $sql .= ' p.visibility >= ? ';
            $args[] = max(ProblemController::VISIBILITY_PUBLIC, $min_visibility);

            if (!is_null($query)) {
                $sql .= " AND (p.title LIKE CONCAT('%', ?, '%') OR p.alias LIKE CONCAT('%', ?, '%'))";
                $args[] = $query;
                $args[] = $query;
            }
        }

        $total = $conn->GetOne("SELECT COUNT(*) $sql", $args);

        // Reset the offset to 0 if out of bounds.
        if ($offset < 0 || $offset > $total) {
            $offset = 0;
        }

        if ($order == 'problem_id') {
            $sql .= " ORDER BY p.problem_id $collation $mode";
        } elseif ($order == 'points' && $mode == 'desc') {
            $sql .= ' ORDER BY `points` DESC, `accepted` ASC, `submissions` DESC';
        } else {
            $sql .= " ORDER BY `$order` $collation $mode";
        }
        $sql .= ' LIMIT ?, ?';
        $args[] = $offset;
        $args[] = $rowcount;

        $result = $conn->Execute("$select $sql", $args);

        // Only these fields (plus score, points and ratio) will be returned.
        $filters = ['title','quality', 'difficulty', 'alias', 'visibility', 'quality_histogram', 'difficulty_histogram'];
        $problems = [];
        $hiddenTags = $identity_type !== IDENTITY_ANONYMOUS ? UsersDao::getHideTags($identity_id) : false;
        if (!is_null($result)) {
            foreach ($result as $row) {
                $temp = new Problems($row);
                $problem = $temp->asFilteredArray($filters);

                // score, points and ratio are not actually fields of a Problems object.
                $problem['score'] = $row['score'];
                $problem['points'] = $row['points'];
                $problem['ratio'] = $row['ratio'];
                $problem['tags'] = $hiddenTags ? [] : ProblemsDAO::getTagsForProblem($temp, true);
                array_push($problems, $problem);
            }
        }
        return $problems;
    }

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Problems WHERE (alias = ? ) LIMIT 1;';
        $params = [  $alias ];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs)==0) {
                return null;
        }

        return new Problems($rs);
    }

    final public static function searchByAlias($alias) {
        global $conn;
        $quoted = $conn->Quote($alias);

        if (strpos($quoted, "'") !== false) {
            $quoted = substr($quoted, 1, strlen($quoted) - 2);
        }

        $sql = "SELECT * FROM Problems WHERE (alias LIKE '%$quoted%' OR title LIKE '%$quoted%') LIMIT 0,10;";
        $rs = $conn->Execute($sql);

        $result = [];

        foreach ($rs as $r) {
            array_push($result, new Problems($r));
        }

        return $result;
    }

    final public static function getTagsForProblem($problem, $public) {
        global $conn;

        $sql = 'SELECT
            t.name,
            pt.autogenerated
        FROM
            Problems_Tags pt
        INNER JOIN
            Tags t ON t.tag_id = pt.tag_id
        WHERE
            pt.problem_id = ?';
        if ($public) {
            $sql .= ' AND pt.public = 1';
        }
        $sql .= ';';

        $rs = $conn->Execute($sql, $problem->problem_id);
        $result = [];

        foreach ($rs as $r) {
            $result[] = ['name' => $r['name'], 'autogenerated' => $r['autogenerated']];
        }

        return $result;
    }

    final public static function getPracticeDeadline($id) {
        global $conn;

        $sql = 'SELECT COALESCE(UNIX_TIMESTAMP(MAX(finish_time)), 0) FROM Contests c INNER JOIN Problemset_Problems pp USING(problemset_id) WHERE pp.problem_id = ?';
        return $conn->GetOne($sql, $id);
    }

    final public static function getProblemsSolved($identity_id) {
        global $conn;

        $sql = "SELECT DISTINCT `Problems`.* FROM `Problems` INNER JOIN `Runs` ON `Problems`.problem_id = `Runs`.problem_id WHERE `Runs`.verdict = 'AC' and `Runs`.type = 'normal' and `Runs`.identity_id = ? ORDER BY `Problems`.problem_id DESC";
        $val = [$identity_id];
        $rs = $conn->Execute($sql, $val);

        $result = [];

        foreach ($rs as $r) {
            array_push($result, new Problems($r));
        }

        return $result;
    }

    final public static function getProblemsUnsolvedByIdentity(
        $identity_id
    ) {
        $sql = "
            SELECT DISTINCT
                p.*
            FROM
                Identities i
            INNER JOIN
                Runs r
            ON
                r.identity_id = i.identity_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = r.problem_id
            WHERE
                i.identity_id = ?
            AND
                (SELECT
                    COUNT(*)
                 FROM
                    Runs r2
                 WHERE
                    r2.identity_id = i.identity_id AND
                    r2.problem_id = p.problem_id AND
                    r2.verdict = 'AC'
                ) = 0";

        $params = [$identity_id];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $problems = [];
        foreach ($rs as $r) {
            array_push($problems, new Problems($r));
        }
        return $problems;
    }

    final public static function getSolvedProblemsByUsersOfCourse($course_alias) {
        global $conn;

        $sql = "
            SELECT
                rp.alias,
                rp.title,
                i.username
            FROM
                Courses c
            INNER JOIN
                Groups_Identities gi
            ON
                c.group_id = gi.group_id
            INNER JOIN
                Identities i
            ON
                gi.identity_id = i.identity_id
            INNER JOIN
                (
                SELECT
                    p.problem_id,
                    p.alias,
                    p.title,
                    r.identity_id
                FROM
                    Runs r
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = r.problem_id
                WHERE
                    r.verdict = 'AC'
                    AND p.visibility = ?
                GROUP BY
                    p.problem_id, r.identity_id
                ) rp
            ON
                rp.identity_id = i.identity_id
            WHERE
                c.alias = ?
                AND gi.accept_teacher = 'yes'
            ORDER BY
                i.username ASC,
                rp.problem_id DESC;";

        return $conn->GetAll($sql, [ProblemController::VISIBILITY_PUBLIC, $course_alias]);
    }

    final public static function getUnsolvedProblemsByUsersOfCourse($course_alias) {
        $sql = "
            SELECT
                rp.alias,
                rp.title,
                i.username
            FROM
                Identities i
            INNER JOIN
                Groups_Identities gi
            ON
                gi.identity_id = i.identity_id
            INNER JOIN
                Courses c
            ON
                c.group_id = gi.group_id
            INNER JOIN
                (
                SELECT
                    pp.problem_id,
                    pp.alias,
                    pp.title,
                    r.identity_id,
                    MAX(r.score) AS max_score
                FROM
                    Runs r
                INNER JOIN
                    Problems pp
                ON
                    pp.problem_id = r.problem_id
                WHERE
                    pp.visibility = ?
                GROUP BY
                    pp.problem_id, r.identity_id
                HAVING
                    max_score < 1
                ) rp
            ON
                rp.identity_id = i.identity_id
            INNER JOIN
                Problems p
            ON
                rp.problem_id = p.problem_id
            WHERE
                c.alias = ?
                AND gi.accept_teacher = 'yes'
            ORDER BY
                i.username ASC,
                rp.problem_id DESC;";

        global $conn;
        return $conn->GetAll($sql, [ProblemController::VISIBILITY_PUBLIC, $course_alias]);
    }

    final public static function isProblemSolved(Problems $problem, $identity_id) {
        $sql = 'SELECT
            COUNT(r.run_id) as solved
        FROM
            Runs AS r
        WHERE
            r.problem_id = ? AND r.identity_id = ? AND r.verdict = "AC";';

        global $conn;
        return $conn->GetRow($sql, [$problem->problem_id, $identity_id])['solved'] > 0;
    }

    public static function getPrivateCount(Users $user) {
        $sql = 'SELECT
            COUNT(*) as Total
        FROM
            Problems AS p
        INNER JOIN
            ACLs AS a
        ON
            a.acl_id = p.acl_id
        WHERE
            p.visibility <= 0 and a.owner_id = ?;';
        $params = [$user->user_id];

        global $conn;
        $rs = $conn->GetRow($sql, $params);

        if (!array_key_exists('Total', $rs)) {
            return 0;
        }

        return $rs['Total'];
    }

    public static function getExplicitAdminEmails(Problems $problem) {
        global $conn;
        $sql = '
            SELECT DISTINCT
                e.email
            FROM
                (
                    SELECT
                        p.problem_id, a.owner_id AS user_id
                    FROM
                        Problems AS p
                    INNER JOIN
                        ACLs AS a
                    ON
                        a.acl_id = p.acl_id
                    WHERE p.problem_id = ?
                ) AS a
            INNER JOIN
                Users u
            ON
                u.user_id = a.user_id
            INNER JOIN
                Emails e
            ON
                e.user_id = u.main_email_id;
        ';

        $params = [$problem->problem_id];
        $rs = $conn->Execute($sql, $params);

        $result = [];
        foreach ($rs as $r) {
            $result[] = $r['email'];
        }

        return $result;
    }

    public static function getAdminUser(Problems $problem) {
        global $conn;
        $sql = '
            SELECT DISTINCT
                e.email,
                u.name
            FROM
                ACLs a
            INNER JOIN
                Users u
            ON
                a.owner_id = u.user_id
            INNER JOIN
                Emails e
            ON
                e.email_id = u.main_email_id
            WHERE
               a.acl_id = ?
            LIMIT
               1;
        ';
        $params = [$problem->acl_id];
        $rs = $conn->Execute($sql, $params);
        if (count($rs)==0) {
                return null;
        }

        return [
            'name' => $rs->fields['name'],
            'email' => $rs->fields['email']
        ];
    }

    /**
     * Returns all problems that an identity can manage.
     */
    final public static function getAllProblemsAdminedByIdentity(
        $identity_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                p.*
            FROM
                Problems AS p
            INNER JOIN
                ACLs AS a ON a.acl_id = p.acl_id
            INNER JOIN
                Identities AS ai ON a.owner_id = ai.user_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = p.acl_id
            LEFT JOIN
                Identities uri ON ur.user_id = uri.user_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = p.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                (ai.identity_id = ? OR
                (ur.role_id = ? AND uri.identity_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)) AND
                p.visibility > ?
            GROUP BY
                p.problem_id
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $identity_id,
            Authorization::ADMIN_ROLE,
            $identity_id,
            Authorization::ADMIN_ROLE,
            $identity_id,
            ProblemController::VISIBILITY_DELETED,
            $offset,
            $pageSize,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new Problems($row));
        }
        return $problems;
    }

    /**
     * Returns all problems owned by a user.
     */
    final public static function getAllProblemsOwnedByUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $offset = ($page - 1) * $pageSize;
        $sql = '
            SELECT
                p.*
            FROM
                Problems AS p
            INNER JOIN
                ACLs AS a ON a.acl_id = p.acl_id
            WHERE
                a.owner_id = ? AND
                p.visibility > ?
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
            ProblemController::VISIBILITY_DELETED,
            $offset,
            $pageSize,
        ];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new Problems($row));
        }
        return $problems;
    }

    /**
     * Return all problems, except deleted
     */
    final public static function getAllProblems($page, $cols_per_page, $order, $order_type) {
        $sql = 'SELECT * from Problems where `visibility` > ? ';
        global $conn;
        if (!is_null($order)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $order) . '` ' . ($order_type == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($page)) {
            $sql .= ' LIMIT ' . (($page - 1) * $cols_per_page) . ', ' . (int)$cols_per_page;
        }
        $rs = $conn->Execute($sql, [ProblemController::VISIBILITY_DELETED]);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new Problems($row);
        }
        return $allData;
    }

    final public static function getIdentitiesInGroupWhoAttemptedProblem(
        $group_id,
        $problem_id
    ) {
        $sql = '
            SELECT
                i.username
            FROM
                Identities i
            WHERE
                i.identity_id
            IN (SELECT DISTINCT
                gi.identity_id
            FROM
                Runs r
            INNER JOIN
                Groups_Identities gi
            ON
                r.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?
                AND r.problem_id = ?)';
        $params = [$group_id, $problem_id];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $identities = [];
        foreach ($rs as $row) {
            $identities[] = $row['username'];
        }
        return $identities;
    }

    final public static function isVisible(Problems $problem) {
        return ((int) $problem->visibility) >= 1;
    }

    public static function deleteProblem($problem_id) {
        $sql = 'UPDATE
                    `Problems`
                SET
                    `visibility` = ?
                WHERE
                    `problem_id` = ?;';
        $params = [
            ProblemController::VISIBILITY_DELETED,
            $problem_id,
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    public static function hasBeenUsedInCoursesOrContests(Problems $problem) {
        global $conn;

        $sql = 'SELECT
                    COUNT(1)
                FROM
                    `Runs`
                WHERE
                    `problemset_id` IS NOT NULL
                    AND `problem_id` = ?';

        return $conn->GetOne($sql, $problem->problem_id);
    }

    final public static function getByContest($contest_id) {
        $sql = 'SELECT
                    p.*
                FROM
                    Problems p
                INNER JOIN
                    Problemset_Problems pp
                ON
                    p.problem_id = pp.problem_id
                INNER JOIN
                    Contests c
                ON
                    c.problemset_id = pp.problemset_id
                WHERE
                    c.contest_id = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$contest_id]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new Problems($row));
        }
        return $problems;
    }

    final public static function getByTitle($title) {
        $sql = 'SELECT
                    *
                FROM
                    Problems
                WHERE
                    title = ?;';

        global $conn;
        $rs = $conn->Execute($sql, [$title]);

        $problems = [];
        foreach ($rs as $row) {
            array_push($problems, new Problems($row));
        }
        return $problems;
    }
}
