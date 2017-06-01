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
    final private static function addTagFilter($user_type, $user_id, $tag, &$sql, &$args) {
        $add_user_id = false;
        if ($user_type === USER_ADMIN) {
            $public_check = '';
        } elseif ($user_type === USER_NORMAL && !is_null($user_id)) {
            $public_check = '(ptp.public OR a.owner_id = ?) AND ';
            $add_user_id = true;
        } else {
            $public_check = 'ptp.public AND ';
        }
        if (is_string($tag)) {
            $sql .= ' INNER JOIN Problems_Tags ptp ON ptp.problem_id = p.problem_id';
            $sql .= ' INNER JOIN Tags t ON ptp.tag_id = t.tag_id';
            $sql .= " WHERE t.name = ? AND $public_check";
            $args[] = $tag;
            if ($add_user_id) {
                $args[] = $user_id;
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
            if ($add_user_id) {
                $args[] = $user_id;
            }
        } else {
            $sql .= ' WHERE';
        }
    }

    final public static function byUserType(
        $user_type,
        $order,
        $mode,
        $offset,
        $rowcount,
        $query,
        $user_id,
        $tag,
        &$total
    ) {
        global $conn;

        // Just in case.
        if ($mode !== 'asc' && $mode !== 'desc') {
            $mode = 'desc';
        }

        // Use BINARY mode to force case sensitive comparisons when ordering by title.
        $collation = ($order === 'title') ? 'COLLATE utf8_bin' : '';

        $select = '';
        $sql= '';
        $args = [];

        if ($user_type === USER_ADMIN) {
            $args = [$user_id];
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
                        Runs ON Runs.user_id = ? AND Runs.problem_id = Problems.problem_id
                    GROUP BY
                        Problems.problem_id
                    ) ps ON ps.problem_id = p.problem_id';

            self::addTagFilter($user_type, $user_id, $tag, $sql, $args);
            if (!is_null($query)) {
                $sql .= " title LIKE CONCAT('%', ?, '%') ";
                $args[] = $query;
            } else {
                // Finish the WHERE clause opened by addTagFilter
                $sql .= ' TRUE';
            }
        } elseif ($user_type === USER_NORMAL && !is_null($user_id)) {
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
                        MAX(r.score) AS score
                    FROM
                        Problems pi
                    INNER JOIN
                        Runs r ON r.user_id = ? AND r.problem_id = pi.problem_id
                    GROUP BY
                        pi.problem_id
                ) ps ON ps.problem_id = p.problem_id
                LEFT JOIN
                    User_Roles ur ON ur.user_id = ? AND p.acl_id = ur.acl_id AND ur.role_id = ?
                LEFT JOIN (
                    SELECT DISTINCT
                        gr.acl_id
                    FROM
                        Groups_Users gu
                    INNER JOIN
                        Group_Roles gr ON gr.group_id = gu.group_id
                    WHERE gu.user_id = ? AND gr.role_id = ?
                ) gr ON p.acl_id = gr.acl_id';
            $args[] = $user_id;
            $args[] = $user_id;
            $args[] = Authorization::ADMIN_ROLE;
            $args[] = $user_id;
            $args[] = Authorization::ADMIN_ROLE;

            self::addTagFilter($user_type, $user_id, $tag, $sql, $args);
            $sql .= '
                (p.visibility = 1 OR a.owner_id = ? OR ur.acl_id IS NOT NULL OR gr.acl_id IS NOT NULL) ';
            $args[] = $user_id;

            if (!is_null($query)) {
                $sql .= " AND p.title LIKE CONCAT('%', ?, '%')";
                $args[] = $query;
            }
        } elseif ($user_type === USER_ANONYMOUS) {
            $select = '
                    SELECT
                        0 AS score,
                        ROUND(100 / LOG2(GREATEST(p.accepted, 1) + 1), 2) AS points,
                        accepted / GREATEST(1, p.submissions)   AS ratio,
                        p.*';
            $sql = '
                    FROM
                        Problems p';

            self::addTagFilter($user_type, $user_id, $tag, $sql, $args);
            $sql .= ' p.visibility = 1 ';

            if (!is_null($query)) {
                $sql .= " AND p.title LIKE CONCAT('%', ?, '%') ";
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
        $filters = ['title', 'submissions', 'accepted', 'alias', 'visibility'];
        $problems = [];
        if (!is_null($result)) {
            foreach ($result as $row) {
                $temp = new Problems($row);
                $problem = $temp->asFilteredArray($filters);

                // score, points and ratio are not actually fields of a Problems object.
                $problem['score'] = $row['score'];
                $problem['points'] = $row['points'];
                $problem['ratio'] = $row['ratio'];
                $problem['tags'] = ProblemsDAO::getTagsForProblem($temp, true);
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

                $contest = new Problems($rs);

                return $contest;
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
            t.name
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
            $result[] = $r['name'];
        }

        return $result;
    }

    final public static function getPracticeDeadline($id) {
        global $conn;

        $sql = 'SELECT COALESCE(UNIX_TIMESTAMP(MAX(finish_time)), 0) FROM Contests c INNER JOIN Problemset_Problems pp USING(problemset_id) WHERE pp.problem_id = ?';
        return $conn->GetOne($sql, $id);
    }

    final public static function getProblemsSolved($id) {
        global $conn;

        $sql = "SELECT DISTINCT `Problems`.* FROM `Problems` INNER JOIN `Runs` ON `Problems`.problem_id = `Runs`.problem_id WHERE `Runs`.verdict = 'AC' and `Runs`.test = 0 and `Runs`.user_id = ? ORDER BY `Problems`.problem_id DESC";
        $val = [$id];
        $rs = $conn->Execute($sql, $val);

        $result = [];

        foreach ($rs as $r) {
            array_push($result, new Problems($r));
        }

        return $result;
    }

    final public static function isProblemSolved(Problems $problem, Users $user) {
        $sql = 'SELECT
            COUNT(r.run_id) as solved
        FROM
            Runs AS r
        WHERE
            r.problem_id = ? AND r.user_id = ? AND r.verdict = "AC";';

        global $conn;
        return $conn->GetRow($sql, [$problem->problem_id, $user->user_id])['solved'] > 0;
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
            p.visibility = 0 and a.owner_id = ?;';
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
                    UNION
                    SELECT
                        ur.contest_id AS problem_id, ur.user_id
                    FROM
                        User_Roles ur
                    WHERE
                        role_id = ? AND ur.contest_id = ?
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

        $params = [$problem->problem_id,
            PROBLEM_ADMIN_ROLE,
            $problem->problem_id];
        $rs = $conn->Execute($sql, $params);

        $result = [];
        foreach ($rs as $r) {
            $result[] = $r['email'];
        }

        return $result;
    }

    /**
     * Returns all problems that a user can manage.
     */
    final public static function getAllProblemsAdminedByUser(
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
            LEFT JOIN
                User_Roles ur ON ur.acl_id = p.acl_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = p.acl_id
            LEFT JOIN
                Groups_Users gu ON gu.group_id = gr.group_id
            WHERE
                a.owner_id = ? OR
                (ur.role_id = ? AND ur.user_id = ?) OR
                (gr.role_id = ? AND gu.user_id = ?)
            GROUP BY
                p.problem_id
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
            Authorization::ADMIN_ROLE,
            $user_id,
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
                a.owner_id = ?
            ORDER BY
                p.problem_id DESC
            LIMIT
                ?, ?';
        $params = [
            $user_id,
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

    final public static function getUsersInGroupWhoAttemptedProblem(
        $group_id,
        $problem_id
    ) {
        $sql = '
            SELECT
                u.username
            FROM
                Users u
            WHERE
                u.user_id
            IN (SELECT DISTINCT
                gu.user_id
            FROM
                Runs r
            JOIN
                Groups_Users gu
            ON
                r.user_id = gu.user_id
            WHERE
                gu.group_id = ?
                AND r.problem_id = ?)';
        $params = [$group_id, $problem_id];

        global $conn;
        $rs = $conn->Execute($sql, $params);

        $users = [];
        foreach ($rs as $row) {
            $users[] = $row['username'];
        }
        return $users;
    }
}
