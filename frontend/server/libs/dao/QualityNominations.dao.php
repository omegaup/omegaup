<?php

include_once('base/QualityNominations.dao.base.php');
include_once('base/QualityNominations.vo.base.php');
/** QualityNominations Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link QualityNominations }.
  * @access public
  *
  */
class QualityNominationsDAO extends QualityNominationsDAOBase {
    public static function getNominationStatusForProblem(Problems $problem, Users $user) {
        $sql = '
        SELECT
            COUNT(r.run_id) > 0 as solved,
            COUNT(qn.quality_nomination_id) > 0 as nominated
        FROM
            QualityNominations qn
        INNER JOIN
            Runs AS r
        ON
            r.user_id = qn.user_id AND r.verdict = "AC"
        WHERE
            qn.problem_id = ? AND qn.user_id = ?;';

        global $conn;
        return $conn->GetRow($sql, [$problem->problem_id, $user->user_id]);
    }

    private static function getVotesForNomination($qualitynomination_id) {
        $sql = '
        SELECT
            u.username,
            u.name,
            COALESCE(qnc.vote, 0) AS vote,
            UNIX_TIMESTAMP(qnc.time) AS time
        FROM
            QualityNomination_Reviewers qnr
        LEFT JOIN
            QualityNomination_Comments qnc
        ON
            qnc.qualitynomination_id = qnr.qualitynomination_id AND
            qnc.user_id = qnr.user_id AND
            qnc.qualitynomination_comment_id = (
                SELECT
                    MAX(qualitynomination_comment_id)
                FROM
                    QualityNomination_Comments
                WHERE
                    qualitynomination_id = qnr.qualitynomination_id AND
                    user_id = qnr.user_id
                GROUP BY
                    user_id
            )
        INNER JOIN
            Users u
        ON
            u.user_id = qnr.user_id
        WHERE
            qnr.qualitynomination_id = ?
        ORDER BY
            u.username;';
        global $conn;

        $votes = [];
        foreach ($conn->GetAll($sql, [$qualitynomination_id]) as $vote) {
            if (is_string($vote['time'])) {
                $vote['time'] = (int)$vote['time'];
            }
            $vote['vote'] = (int)$vote['vote'];
            $vote['user'] = [
                'username' => $vote['username'],
                'name' => $vote['name'],
            ];
            unset($vote['username']);
            unset($vote['name']);

            $votes[] = $vote;
        }
        return $votes;
    }

    public static function getAllNominationsAssignedToUser(
        $user_id,
        $page = 1,
        $pageSize = 1000
    ) {
        $page = max(0, $page - 1);
        $sql = '
        SELECT
            qn.qualitynomination_id,
            qn.nomination,
            UNIX_TIMESTAMP(qn.time) as time,
            qn.status,
            nominator.username,
            nominator.name,
            p.alias,
            p.title
        FROM
            QualityNominations qn
        INNER JOIN
            Problems p
        ON
            p.problem_id = qn.problem_id
        INNER JOIN
            Users nominator
        ON
            nominator.user_id = qn.user_id
        INNER JOIN
            QualityNomination_Reviewers qnr
        ON
            qnr.qualitynomination_id = qn.qualitynomination_id';
        $params = [];

        if (!is_null($user_id)) {
            $sql .= ' WHERE qnr.user_id = ?';
            $params[] = $user_id;
        }
        $sql .= ' LIMIT ?, ?;';
        $params[] = $page * $pageSize;
        $params[] = ($page + 1) * $pageSize;

        global $conn;
        $nominations = [];
        foreach ($conn->GetAll($sql, $params) as $nomination) {
            $nomination['time'] = (int)$nomination['time'];
            $nomination['nominator'] = [
                'username' => $nomination['username'],
                'name' => $nomination['name'],
            ];
            unset($nomination['username']);
            unset($nomination['name']);
            $nomination['problem'] = [
                'alias' => $nomination['alias'],
                'title' => $nomination['title'],
            ];
            unset($nomination['alias']);
            unset($nomination['title']);

            $nomination['votes'] = self::getVotesForNomination(
                $nomination['qualitynomination_id']
            );
            unset($nomination['qualitynomination_id']);

            $nominations[] = $nomination;
        }

        return $nominations;
    }
}
