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
     * @return array{dismissed: bool, dismissedBeforeAc: bool, nominated: bool, nominatedBeforeAc: bool}
     */
    public static function getNominationStatusForProblem(
        int $problemId,
        ?int $userId
    ): array {
        $response = [
            'nominated' => false,
            'dismissed' => false,
            'nominatedBeforeAc' => false,
            'dismissedBeforeAc' => false,
        ];

        if (is_null($userId)) {
            return $response;
        }

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

        /** @var null|array{contents: string} */
        $suggestion = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemId, $userId]
        );
        if (!is_null($suggestion)) {
            $response['nominated'] = true;
            /** @var array{before_ac?: mixed} */
            $suggestionContents = json_decode(
                $suggestion['contents'],
                associative: true
            );
            if (
                isset($suggestionContents['before_ac']) &&
                $suggestionContents['before_ac']
            ) {
                $response['nominated'] = false;
                $response['nominatedBeforeAc'] = true;
            }
        }

        $sql = "SELECT
                    qnn.contents
                FROM
                    QualityNominations qnn
                WHERE
                    qnn.problem_id = ? AND
                    qnn.user_id = ? AND
                    qnn.nomination = 'dismissal'
                ORDER BY
                    qnn.qualitynomination_id DESC";

        /** @var null|array{contents: string} $dismissal */
        $dismissal = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemId, $userId]
        );
        if (!is_null($dismissal)) {
            $response['dismissed'] = true;
            /** @var array $dismissalContents */
            $dismissalContents = json_decode(
                $dismissal['contents'],
                associative: true
            );
            if (
                isset($dismissalContents['before_ac']) &&
                $dismissalContents['before_ac']
            ) {
                $response['dismissed'] = false;
                $response['dismissedBeforeAc'] = true;
            }
        }

        return $response;
    }

    public static function reviewerHasQualityTagNominatedProblem(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        $sql = "
            SELECT
                COUNT(*)
            FROM
                QualityNominations qn
            INNER JOIN
                Identities i ON i.user_id = qn.user_id
            WHERE
                nomination = 'quality_tag' AND
                i.identity_id = ? AND
                qn.problem_id = ?";

        return (
            /** @var int */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                [$identity->identity_id, $problem->problem_id]
            )
        ) > 0;
    }

    /**
     * Returns the quality nomination ID and contents for a problem and reviewer.
     *
     * @return array{contents: string, qualitynomination_id: int}|null
     */
    public static function getQualityNominationContentsForProblemAndReviewer(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ) {
        $sql = "
            SELECT
                qualitynomination_id,
                contents
            FROM
                QualityNominations qn
            INNER JOIN
                Identities i ON i.user_id = qn.user_id
            WHERE
                nomination = 'quality_tag' AND
                i.identity_id = ? AND
                qn.problem_id = ?
            LIMIT 1";

        /** @var array{contents: string, qualitynomination_id: int}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identity->identity_id, $problem->problem_id]
        );
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
     * @return list<array{time: \OmegaUp\Timestamp|null, vote: int, user: array{username: string, name: string|null}}>
     */
    private static function getVotesForNomination(int $qualitynomination_id) {
        $sql = '
        SELECT
            i.username,
            i.name,
            IFNULL(qnc.vote, 0) AS vote,
            qnc.`time`
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

        /** @var array{name: null|string, time: \OmegaUp\Timestamp|null, username: string, vote: int} $vote */
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
     * @param array{qualitynomination_id: int, nomination: string, contents?: string, time: \OmegaUp\Timestamp, status: string, nominator_username: string, nominator_name: null|string, alias: string, title: string, author_username: string, author_name: null|string} $nomination
     * @return array{qualitynomination_id: int, nomination: string, contents?: array{statements?: array<string, string>, rationale?: string, reason?: string, before_ac?: bool, quality?: int, tags?: list<string>, difficulty?: int}, time: \OmegaUp\Timestamp, status: string, nominator: array{username: string, name: null|string}, author: array{username: string, name: null|string}, problem: array{alias: string, title: string}, votes: list<array{time: \OmegaUp\Timestamp|null, vote: int, user: array{username: string, name: null|string}}>}
     */
    private static function processNomination($nomination) {
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
            /** @var array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>} */
            $nomination['contents'] = json_decode(
                $nomination['contents'],
                associative: true
            );
        } else {
            unset($nomination['contents']);
        }

        return $nomination;
    }

    /**
     * Gets list of nominations.
     *
     * The list of nominations can be filtered by at most one of
     * $nominatorUserId (user id of person who made the nomination) or
     * $assigneeUserId (user id of person assigned to review the nomination).
     * If both are null, the complete list of nominations is returned.
     *
     * @param list<string> $types
     *
     * @return array{totalRows: int, nominations: list<array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}>}
     */
    public static function getNominations(
        ?int $nominatorUserId,
        ?int $assigneeUserId,
        int $page,
        int $rowcount,
        array $types = ['demotion', 'promotion'],
        string $status = 'all',
        ?string $query = null,
        ?string $column = null
    ): array {
        $sqlFrom = '
            FROM
                QualityNominations qn
            INNER JOIN
                Problems p ON p.problem_id = qn.problem_id
            INNER JOIN
                Users nominator ON nominator.user_id = qn.user_id
            INNER JOIN
                Identities nominatorIdentity ON nominatorIdentity.identity_id = nominator.main_identity_id
            INNER JOIN
                ACLs acl ON acl.acl_id = p.acl_id
            INNER JOIN
                Users author ON author.user_id = acl.owner_id
            INNER JOIN
                Identities authorIdentity ON authorIdentity.identity_id = author.main_identity_id
        ';

        $sqlCount = '
            SELECT
                COUNT(*)
        ';

        $sql = '
            SELECT
                qn.qualitynomination_id,
                qn.nomination,
                qn.`time`,
                qn.status,
                nominatorIdentity.username as nominator_username,
                nominatorIdentity.name as nominator_name,
                p.alias,
                p.title,
                authorIdentity.username as author_username,
                authorIdentity.name as author_name,
                qn.contents
        ';

        $params = [];
        $conditions = [];

        if (!is_null($assigneeUserId)) {
            $sqlFrom .= '
            INNER JOIN
                QualityNomination_Reviewers qnr
            ON
                qnr.qualitynomination_id = qn.qualitynomination_id';

            $conditions[] = ' qnr.user_id = ?';
            $params[] = $assigneeUserId;
        }

        if (!empty($types)) {
            $conditions[] =
                ' qn.nomination in ("' . implode(
                    '", "',
                    array_map(
                        fn (string $type) => \OmegaUp\MySQLConnection::getInstance()->escape(
                            $type
                        ),
                        $types
                    )
                ) . '")';
        }

        if (!is_null($nominatorUserId)) {
            $conditions[] = ' qn.user_id = ?';
            $params[] = $nominatorUserId;
        }

        if (!is_null($query) && !is_null($column)) {
            // Some columns are renamed in the query.
            if ($column == 'author_username') {
                $column = 'authorIdentity.username';
            } elseif ($column == 'nominator_username') {
                $column = 'nominatorIdentity.username';
            } elseif ($column == 'problem_alias') {
                $column = 'alias';
            }
            $sqlSearch = \OmegaUp\MySQLConnection::getInstance()->escape(
                $column
            ) . " LIKE CONCAT('%', ?, '%')";
            $params[] = $query;
        } else {
            $sqlSearch = '';
        }

        if (!empty($conditions)) {
            $sqlFrom .= ' WHERE ' . implode(
                ' AND ',
                $conditions
            );
            if (!is_null($query)) {
                $sqlFrom .= " AND {$sqlSearch} ";
            }
        } else {
            $sqlFrom .= " WHERE {$sqlSearch} ";
        }

        if ($status != 'all') {
            $sqlFrom .= " AND qn.status = '{$status}'";
        }

        $sqlOrder = ' ORDER BY qn.qualitynomination_id ASC';
        $sqlLimit = ' LIMIT ?, ?;';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount . $sqlFrom,
            $params
        ) ?? 0;

        $params[] = max(0, $page - 1) * $rowcount;
        $params[] = $rowcount;

        $nominations = [];
        /** @var array{alias: string, author_name: null|string, author_username: string, contents: string, nomination: string, nominator_name: null|string, nominator_username: string, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, title: string} $nomination */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                "${sql}{$sqlFrom}{$sqlOrder}{$sqlLimit}",
                $params
            ) as $nomination
        ) {
            $nominations[] = self::processNomination($nomination);
        }

        return [
            'totalRows' => $totalRows,
            'nominations' => $nominations,
        ];
    }

    /**
     * Gets a single nomination by ID.
     *
     * @return array{author: array{name: null|string, username: string}, contents?: array{before_ac?: bool, difficulty?: int, quality?: int, rationale?: string, reason?: string, statements?: array<string, string>, tags?: list<string>}, nomination: string, nominator: array{name: null|string, username: string}, problem: array{alias: string, title: string}, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, votes: list<array{time: \OmegaUp\Timestamp|null, user: array{name: null|string, username: string}, vote: int}>}|null
     */
    public static function getById(int $qualitynomination_id) {
        $sql = '
        SELECT
            qn.qualitynomination_id,
            qn.nomination,
            qn.contents,
            qn.`time`,
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

        /** @var array{alias: string, author_name: null|string, author_username: string, contents: string, nomination: string, nominator_name: null|string, nominator_username: string, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, title: string}|null $result */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$qualitynomination_id]
        );
        if (is_null($result)) {
            return null;
        }
        return self::processNomination($result);
    }

    /**
     * This function gets the contents of QualityNomination table
     *
     * @return list<array{contents: string}>
     */
    public static function getAllNominations(): array {
        $sql = '
            SELECT
                contents
            FROM
                QualityNominations
            WHERE
                `nomination` = "suggestion";
        ';

        /** @var list<array{contents: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * This function computes the average difficulty and quality among all problems.
     *
     * @param list<array{contents: string}> $contents
     * @return array{0: float, 1: float}
     */
    public static function calculateGlobalDifficultyAndQuality(array $contents): array {
        $qualitySum = 0;
        $qualityN = 0;
        $difficultySum = 0;
        $difficultyN = 0;

        foreach ($contents as $nomination) {
            /** @var array{quality?: mixed, difficulty?: mixed, tags?: mixed} */
            $feedback = json_decode($nomination['contents'], associative: true);
            if (isset($feedback['quality']) && is_int($feedback['quality'])) {
                $qualitySum += $feedback['quality'];
                $qualityN++;
            }
            if (
                isset($feedback['difficulty']) &&
                is_int($feedback['difficulty'])
            ) {
                $difficultySum += $feedback['difficulty'];
                $difficultyN++;
            }
        }

        return [$qualitySum / $qualityN, $difficultySum / $difficultyN];
    }

    /**
     * This function gets contents of QualityNomination table
     *
     * @return list<\OmegaUp\DAO\VO\QualityNominations>
     */
    public static function getAllDemotionsForProblem(int $problemId): array {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\QualityNominations::FIELD_NAMES,
            'QualityNominations'
        ) . '
            FROM
                QualityNominations
            WHERE
                nomination = "demotion" AND
                problem_id = ?;
        ';
        /** @var list<array{contents: string, nomination: string, problem_id: int, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );

        $nominations = [];
        foreach ($rs as $row) {
            $nominations[] = new \OmegaUp\DAO\VO\QualityNominations($row);
        }
        return $nominations;
    }

    /**
     * Gets all rationales for a problem's active demotion nominations.
     * Only considers nominations of type 'demotion' with status 'warning' or 'banned'.
     *
     * @return list<string> List of unique rationale strings
     */
    public static function getDemotionRationales(int $problemId): array {
        $sql = '
            SELECT
                contents
            FROM
                QualityNominations
            WHERE
                problem_id = ? AND
                nomination = "demotion" AND
                status IN ("warning", "banned");
        ';

        /** @var list<array{contents: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );

        $rationales = [];
        foreach ($rs as $row) {
            /** @var array{rationale?: string} */
            $contents = json_decode($row['contents'], associative: true);
            if (!empty($contents['rationale'])) {
                $rationales[] = $contents['rationale'];
            }
        }

        return array_values(array_unique($rationales));
    }
    /**
     * This function gets contents of QualityNomination table
     *
     * @return list<array{contents: string}>
     */
    public static function getAllSuggestionsPerProblem(int $problemId): array {
        $sql = '
            SELECT
                contents
            FROM
                QualityNominations
            WHERE
                nomination = "suggestion" AND
                problem_id = ?;
        ';
        /** @var list<array{contents: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problemId]
        );
    }

    /**
     * This function computes sums of difficulty, quality, and tag votes for
     * each problem and returns that in the form of a table.
     *
     * @param list<array{contents: string}> $contents
     * @return array{difficulty_n: int, difficulty_sum: int, quality_n: int, quality_sum: int, tags: array<string, int>, tags_n: int}
     */
    public static function calculateProblemSuggestionAggregates(array $contents) {
        $problemAggregates = [
            'quality_sum' => 0,
            'quality_n' => 0,
            'difficulty_sum' => 0,
            'difficulty_n' => 0,
            'tags_n' => 0,
            'tags' => [],
        ];

        foreach ($contents as $nomination) {
            /** @var array{quality?: mixed, difficulty?: mixed, tags?: mixed} */
            $feedback = json_decode($nomination['contents'], associative: true);

            if (isset($feedback['quality']) && is_int($feedback['quality'])) {
                $problemAggregates['quality_sum'] += intval(
                    $feedback['quality']
                );
                $problemAggregates['quality_n']++;
            }

            if (
                isset($feedback['difficulty']) &&
                is_int($feedback['difficulty'])
            ) {
                $problemAggregates['difficulty_sum'] += intval(
                    $feedback['difficulty']
                );
                $problemAggregates['difficulty_n']++;
            }

            if (isset($feedback['tags']) && is_array($feedback['tags'])) {
                /** @var string $tag */
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
     * Algorithm that computes the list of tags to be assigned to a problem
     * based on the number of votes each tag got for each problem.
     *
     * @param array<string, int> $tags
     * @return list<string>
     */
    public static function mostVotedTags(array $tags, float $threshold): array {
        if (empty($tags) || array_sum($tags) < 5) {
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

    /**
     * @return list<\OmegaUp\DAO\VO\QualityNominations>
     */
    final public static function getByUserAndProblem(
        int $userId,
        int $problemId,
        string $nomination,
        string $contents,
        string $status
    ): array {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\QualityNominations::FIELD_NAMES,
            'QualityNominations'
        ) . '
            FROM
                QualityNominations
            WHERE
                user_id = ? AND
                problem_id = ? AND
                nomination = ? AND
                contents = ? AND
                status = ?;
        ';

        /** @var list<array{contents: string, nomination: string, problem_id: int, qualitynomination_id: int, status: string, time: \OmegaUp\Timestamp, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId, $problemId, $nomination, $contents, $status]
        );

        $qualityNominations = [];
        foreach ($rs as $row) {
            $qualityNominations[] = new \OmegaUp\DAO\VO\QualityNominations(
                $row
            );
        }
        return $qualityNominations;
    }
}
