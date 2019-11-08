<?php

namespace OmegaUp\DAO;

/**
 * QualityNominations Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\QualityNominations}.
 *
 * @access public
 */
class QualityNominations extends \OmegaUp\DAO\Base\QualityNominations {
    /**
     * If a problem has more than this number of problems, none will be assigned.
     */
    const MAX_NUM_TOPICS = 5;
    /**
     * Confidence parameter for bayesianAverage()
     */
    const CONFIDENCE = 5;

    public static function getNominationStatusForProblem(
        \OmegaUp\DAO\VO\Problems $problem,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $response = [
            'nominated' => false,
            'dismissed' => false,
            'nominatedBeforeAC' => false,
            'dismissedBeforeAC' => false,
        ];

        $sql = "SELECT
                    qnn.contents
                FROM
                    QualityNominations qnn
                WHERE
                    qnn.problem_id = ? AND
                    qnn.user_id = ? AND
                    qnn.nomination = 'suggestion'
                ORDER BY
                    qnn.qualitynomination_id DESC";

        /** @var null|array{contents: string} $suggestion */
        $suggestion = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problem->problem_id, $identity->user_id]
        );
        if (!is_null($suggestion)) {
            $response['nominated'] = true;
            /** @var array $suggestionContents */
            $suggestionContents = json_decode($suggestion['contents'], true);
            if (
                isset($suggestionContents['before_ac']) &&
                $suggestionContents['before_ac']
            ) {
                $response['nominated'] = false;
                $response['nominatedBeforeAC'] = true;
            }
        }

        $sql = "SELECT
                    qnn.contents
                FROM
                    QualityNominations qnn
                LEFT JOIN
                    Identities i
                ON
                    qnn.user_id = i.user_id
                WHERE
                    qnn.problem_id = ? AND
                    qnn.user_id = ? AND
                    qnn.nomination = 'dismissal'
                ORDER BY
                    qnn.qualitynomination_id DESC";

        /** @var null|array{contents: string} $dismissal */
        $dismissal = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problem->problem_id, $identity->user_id]
        );
        if (!is_null($dismissal)) {
            $response['dismissed'] = true;
            /** @var array $dismissalContents */
            $dismissalContents = json_decode(
                $dismissal['contents'],
                true /*assoc*/
            );
            if (
                isset($dismissalContents['before_ac']) &&
                $dismissalContents['before_ac']
            ) {
                $response['dismissed'] = false;
                $response['dismissedBeforeAC'] = true;
            }
        }

        return $response;
    }

    /**
     * Returns the votes from all the assigned reviewers for a particular
     * nomination.
     *
     * If no votes have been cast by a reviewer, a default of 0 will be
     * returned. "drive-by" reviewers are not considered for this, only
     * assigned reviewers.
     *
     * @param int $qualitynomination_id
     * @return array{time: int, vote: int, user: array{username: string, name: string}}[] $votes
     */
    private static function getVotesForNomination(int $qualitynomination_id) {
        $sql = '
        SELECT
            i.username,
            i.name,
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
            Identities i
        ON
            i.user_id = qnr.user_id
        WHERE
            qnr.qualitynomination_id = ?
        ORDER BY
            i.username;';

        $votes = [];

        /** @var array{time: int, vote: int, username: string, name: string} $vote */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$qualitynomination_id]
            ) as $vote
        ) {
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
     *
     * @param null|array{qualitynomination_id: int, nomination: string, contents: string, time: int, status: string, nominator_username: string, nominator_name: string, alias: string, title: string, author_username: string, author_name: string} $nomination
     * @return array{qualitynomination_id: int, nomination: string, contents: mixed, time: int, status: string, nominator: array{username: string, name: string}, author: array{username: string, name: string}, problem: array{alias: string, title: string}, votes: array{time: int, vote: int, user: array{username: string, name: string}}[]}|null
     */
    private static function processNomination($nomination) {
        if (is_null($nomination) || empty($nomination)) {
            return null;
        }

        $nomination['time'] = intval($nomination['time']);

        $nomination['nominator'] = [
            'username' => $nomination['nominator_username'],
            'name' => $nomination['nominator_name'],
        ];
        unset($nomination['nominator_username']);
        unset($nomination['nominator_name']);

        $nomination['author'] = [
            'username' => $nomination['author_username'],
            'name' => $nomination['author_name'],
        ];
        unset($nomination['author_username']);
        unset($nomination['author_name']);

        $nomination['problem'] = [
            'alias' => $nomination['alias'],
            'title' => $nomination['title'],
        ];
        unset($nomination['alias']);
        unset($nomination['title']);

        $nomination['votes'] = self::getVotesForNomination(
            intval($nomination['qualitynomination_id'])
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
            nominatorIdentity.username as nominator_username,
            nominatorIdentity.name as nominator_name,
            p.alias,
            p.title,
            authorIdentity.username as author_username,
            authorIdentity.name as author_name
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
            Identities nominatorIdentity
        ON
            nominatorIdentity.identity_id = nominator.main_identity_id
        INNER JOIN
            ACLs acl
        ON
            acl.acl_id = p.acl_id
        INNER JOIN
            Users author
        ON
            author.user_id = acl.owner_id
        INNER JOIN
            Identities authorIdentity
        ON
            authorIdentity.identity_id = author.main_identity_id';
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
            $escapeFunc = function ($type) {
                return \OmegaUp\MySQLConnection::getInstance()->escape($type);
            };
            $conditions[] =
                ' qn.nomination in ("' . implode(
                    '", "',
                    array_map(
                        $escapeFunc,
                        $types
                    )
                ) . '")';
        }
        if (!is_null($nominator)) {
            $conditions[] = ' qn.user_id = ?';
            $params[] = $nominator;
        }
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' LIMIT ?, ?;';
        $params[] = intval($page * $pageSize);
        $params[] = intval(($page + 1) * $pageSize);

        $nominations = [];
        /** @var array{qualitynomination_id: int, nomination: string, contents: string, time: int, status: string, nominator_username: string, nominator_name: string, alias: string, title: string, author_username: string, author_name: string} $nomination */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $nomination
        ) {
            $nominations[] = self::processNomination($nomination);
        }

        return $nominations;
    }

    /**
     * Gets a single nomination by ID.
     *
     * @return array{qualitynomination_id: int, nomination: string, contents: mixed, time: int, status: string, nominator: array{username: string, name: string}, author: array{username: string, name: string}, problem: array{alias: string, title: string}, votes: array{time: int, vote: int, user: array{username: string, name: string}}[]}|null
     */
    public static function getById(int $qualitynomination_id) {
        $sql = '
        SELECT
            qn.qualitynomination_id,
            qn.nomination,
            qn.contents,
            UNIX_TIMESTAMP(qn.time) as time,
            qn.status,
            nominatorIdentity.username as nominator_username,
            nominatorIdentity.name as nominator_name,
            p.alias,
            p.title,
            authorIdentity.username as author_username,
            authorIdentity.name as author_name
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
            Identities nominatorIdentity
        ON
            nominatorIdentity.identity_id = nominator.main_identity_id
        INNER JOIN
            ACLs acl
        ON
            acl.acl_id = p.acl_id
        INNER JOIN
            Users author
        ON
            author.user_id = acl.owner_id
        INNER JOIN
            Identities authorIdentity
        ON
            authorIdentity.identity_id = author.main_identity_id
        WHERE
            qn.qualitynomination_id = ?;';

        /** @var null|array{qualitynomination_id: int, nomination: string, contents: string, time: int, status: string, nominator_username: string, nominator_name: string, alias: string, title: string, author_username: string, author_name: string} $result */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$qualitynomination_id]
        );
        return self::processNomination($result);
    }

    /**
     * This function gets the contents of QualityNomination table
     */
    public static function getAllNominations() {
        $sql = 'SELECT `QualityNominations`.`contents` '
            . "FROM `QualityNominations` WHERE (`nomination` = 'suggestion');";

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * This function computes the average difficulty and quality among all problems.
     */
    public static function calculateGlobalDifficultyAndQuality($contents) {
        $qualitySum = 0;
        $qualityN = 0;
        $difficultySum = 0;
        $difficultyN = 0;

        foreach ($contents as $nomination) {
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
     * This function gets contents of QualityNomination table
     */
    public static function getAllSuggestionsPerProblem($problemId) {
        $sql = 'SELECT `QualityNominations`.`contents` '
            . 'FROM `QualityNominations` '
            . "WHERE (`nomination` = 'suggestion') AND `QualityNominations`.`problem_id` = " . $problemId . ';';
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * This function computes sums of difficulty, quality, and tag votes for
     * each problem and returns that in the form of a table.
     */
    public static function calculateProblemSuggestionAggregates($contents) {
        $problemAggregates = [
            'quality_sum' => 0,
            'quality_n' => 0,
            'difficulty_sum' => 0,
            'difficulty_n' => 0,
            'tags_n' => 0,
            'tags' => [],
        ];

        foreach ($contents as $nomination) {
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
        $globalContents = self::getAllNominations();
        list($globalQualityAverage, $globalDifficultyAverage)
          = self::calculateGlobalDifficultyAndQuality($globalContents);

        $sql = 'SELECT DISTINCT `QualityNominations`.`problem_id` '
            . "FROM `QualityNominations` WHERE nomination = 'suggestion';";
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql
            ) as $nomination
        ) {
            $problemId = $nomination['problem_id'];
            $contents = self::getAllSuggestionsPerProblem($problemId);
            $problemAggregates = self::calculateProblemSuggestionAggregates(
                $contents
            );

            $problem = \OmegaUp\DAO\Problems::getByPK($problemId);
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

            if (!is_null($problem->quality) || !is_null($problem->difficulty)) {
                \OmegaUp\DAO\Problems::update($problem);
            }
            // TODO(heduenas): Get threshold parameter from DB for each problem independently.
            $tags = self::mostVotedTags($problemAggregates['tags'], 0.25);
            if (!empty($tags)) {
                \OmegaUp\DAO\ProblemsTags::replaceAutogeneratedTags(
                    $problem,
                    $tags
                );
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

    final public static function getByUserAndProblem(
        $userId,
        $problemId,
        $nomination,
        $contents,
        $status
    ) {
        $sql = 'SELECT
                    *
                FROM
                    QualityNominations
                WHERE
                    user_id = ?
                AND
                    problem_id = ?
                AND
                    nomination = ?
                AND
                    contents = ?
                AND
                    status = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId, $problemId, $nomination, $contents, $status]
        );

        $qualityNominations = [];
        foreach ($rs as $row) {
            array_push(
                $qualityNominations,
                new \OmegaUp\DAO\VO\QualityNominations(
                    $row
                )
            );
        }
        return $qualityNominations;
    }
}
