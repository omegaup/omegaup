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
    /**
     * If a problem has more than this number of problems, none will be assigned.
     */
    const MAX_NUM_TOPICS = 5;
    /**
     * Confidence parameter for bayesianAverage()
     */
    const CONFIDENCE = 5;

    public static function getNominationStatusForProblem(Problems $problem, Users $user) {
        $sql = '
            SELECT
                COUNT(r.run_id) > 0 as solved,
                COUNT(qn.qualitynomination_id) > 0 as nominated
            FROM
                Problems p
            INNER JOIN
                Runs r
            ON
                r.problem_id = p.problem_id AND r.verdict = "AC"
            LEFT JOIN
                QualityNominations qn
            ON
                qn.problem_id = p.problem_id AND qn.user_id = r.user_id
            WHERE
                p.problem_id = ? AND r.user_id = ?;
        ';

        global $conn;
        return $conn->GetRow($sql, [$problem->problem_id, $user->user_id]);
    }

    /**
     * Returns the votes from all the assigned reviewers for a particular
     * nomination.
     *
     * If no votes have been cast by a reviewer, a default of 0 will be
     * returned. "drive-by" reviewers are not considered for this, only
     * assigned reviewers.
     */
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
                -- Gets the last vote per qualitynomination_id, user_id.
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
        foreach ($conn->Execute($sql, [$qualitynomination_id]) as $vote) {
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

    /**
     * Gets additional details for $nomination and structures it as an object
     * instead of as a flat array.
     */
    private static function processNomination($nomination) {
        if (is_null($nomination) || empty($nomination)) {
            return null;
        }

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

        if (isset($nomination['contents'])) {
            $nomination['contents'] = json_decode(
                $nomination['contents'],
                true /*assoc*/
            );
        }

        return $nomination;
    }

    /**
     * Gets list of nominations.
     *
     * The list of nominations can be filtered by at most one of $nominator
     * (user id of person who made the nomination) or $assignee (user id of
     * person assigned to review the nomination). If both are null, the
     * complete list of nominations is returned.
     */
    public static function getNominations(
        $nominator,
        $assignee,
        $page = 1,
        $pageSize = 1000,
        $types = ['demotion', 'promotion']
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
            nominator.user_id = qn.user_id';
        $params = [];
	$conditions = [];

        if (!is_null($assignee)) {
            $sql .= '
            INNER JOIN
                QualityNomination_Reviewers qnr
            ON
                qnr.qualitynomination_id = qn.qualitynomination_id';
            
            $conditions[] = ' qnr.user_id = ?';
            $params[] = $assignee;
        }
        if (!empty($types)) {
            global $conn;
            $connectionID = $conn->_connectionID;
            $escapeFunc = function ($type) use ($connectionID) {
                return mysqli_real_escape_string($connectionID, $type);
            };
            $conditions[] =
                ' qn.nomination in ("' . implode('", "', array_map($escapeFunc, $types)) . '")';
        }
        if (!is_null($nominator)) {
            $conditions[] = ' qn.user_id = ?';
            $params[] = $nominator;
        }
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' LIMIT ?, ?;';
        $params[] = $page * $pageSize;
        $params[] = ($page + 1) * $pageSize;

        global $conn;
        $nominations = [];
        foreach ($conn->Execute($sql, $params) as $nomination) {
            $nominations[] = self::processNomination($nomination);
        }

        return $nominations;
    }

    /**
     * Gets a single nomination by ID.
     */
    public static function getById($qualitynomination_id) {
        $sql = '
        SELECT
            qn.qualitynomination_id,
            qn.nomination,
            qn.contents,
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
        WHERE
            qn.qualitynomination_id = ?;';

        global $conn;
        return self::processNomination($conn->GetRow($sql, [$qualitynomination_id]));
    }

    /**
     * This function computes the average difficulty and quality among all problems.
     */
    public static function getGlobalDifficultyAndQuality() {
        $sql = 'SELECT `QualityNominations`.`contents` '
            . "FROM `QualityNominations` WHERE (`nomination` = 'suggestion');";

        $qualitySum = 0;
        $qualityN = 0;
        $difficultySum = 0;
        $difficultyN = 0;

        global $conn;
        $result = $conn->Execute($sql);

        foreach ($result as $nomination) {
            $feedback = (array) json_decode($nomination['contents']);
            if (isset($feedback['quality'])) {
                $qualitySum += $feedback['quality'];
                $qualityN++;
            }
            if (isset($feedback['difficulty'])) {
                $difficultySum += $feedback['difficulty'];
                $difficultyN++;
            }
        }

        return [$qualitySum / $qualityN, $difficultySum / $difficultyN];
    }

    /**
     * This function computes sums of difficulty, quality, and tag votes for
     * each problem and returns that in the form of a table.
     */
    public static function getProblemSuggestionAggregates($problemId) {
        $sql = 'SELECT `QualityNominations`.`contents` '
            . 'FROM `QualityNominations` '
            . "WHERE (`nomination` = 'suggestion') AND `QualityNominations`.`problem_id` = " . $problemId . ';';
        global $conn;
        $result = $conn->Execute($sql);

        $problemAggregates = [
            'quality_sum' => 0,
            'quality_n' => 0,
            'difficulty_sum' => 0,
            'difficulty_n' => 0,
            'tags_n' => 0,
            'tags' => [],
        ];

        foreach ($result as $nomination) {
            $feedback = (array) json_decode($nomination['contents']);

            if (isset($feedback['quality'])) {
                $problemAggregates['quality_sum'] += $feedback['quality'];
                $problemAggregates['quality_n']++;
            }

            if (isset($feedback['difficulty'])) {
                $problemAggregates['difficulty_sum'] += $feedback['difficulty'];
                $problemAggregates['difficulty_n']++;
            }

            if (isset($feedback['tags'])) {
                foreach ($feedback['tags'] as $tag) {
                    if (!isset($problemAggregates['tags'][$tag])) {
                        $problemAggregates['tags'][$tag] = 1;
                    } else {
                        $problemAggregates['tags'][$tag]++;
                    }
                    $problemAggregates['tags_n']++;
                }
            }
        }

        return $problemAggregates;
    }

    /**
     * This function aggregates users' suggestions to generate difficulty,
     * quality and subject tags for each problem in the platform.
     * This function is to be called (only) by a cronjob.
     */
    public static function aggregateFeedback() {
        list($globalQualityAverage, $globalDifficultyAverage)
                = self::getGlobalDifficultyAndQuality();

        $sql = 'SELECT DISTINCT `QualityNominations`.`problem_id` '
            . "FROM `QualityNominations` WHERE nomination = 'suggestion';";
        global $conn;
        foreach ($conn->Execute($sql) as $nomination) {
            $problemId = $nomination['problem_id'];
            $problemAggregates = self::getProblemSuggestionAggregates($problemId);

            $problem = ProblemsDAO::getByPK($problemId);
            $problem->quality = self::bayesianAverage(
                $globalQualityAverage,
                $problemAggregates['quality_sum'],
                $problemAggregates['quality_n']
            );
            $problem->difficulty = self::bayesianAverage(
                $globalDifficultyAverage,
                $problemAggregates['difficulty_sum'],
                $problemAggregates['difficulty_n']
            );

            if ($problem->quality != null || $problem->difficulty != null) {
                ProblemsDAO::save($problem);
            }
            // TODO(heduenas): Get threshold parameter from DB for each problem independently.
            $tags = self::mostVotedTags($problemAggregates['tags'], 0.25);
            if (!empty($tags)) {
                ProblemsTagsDAO::replaceAutogeneratedTags($problem, $tags);
            }
        }
    }

    private static function bayesianAverage($aprioriAverage, $sum, $n) {
        if ($n < self::CONFIDENCE) {
            return null;
        }
        return (self::CONFIDENCE * $aprioriAverage + $sum) / (self::CONFIDENCE + $n);
    }

    /**
     * Algorithm that computes the list of tags to be assigned to a problem
     * based on the number of votes each tag got for each problem.
     */
    public static function mostVotedTags($tags, $threshold) {
        if (array_sum($tags) < 5) {
            return [];
        }

        $max = max($tags);
        $mostVoted = [];
        foreach ($tags as $key => $value) {
            if ($value < $max * $threshold) {
                continue;
            }
            $mostVoted[] = $key;
            if (count($mostVoted) > self::MAX_NUM_TOPICS) {
                return [];
            }
        }
        return $mostVoted;
    }
}
