<?php

namespace OmegaUp\DAO;

/**
 * Clarifications Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Clarifications}.
 *
 * @psalm-type ProblemClarification=array{answer: null|string, author: string, clarification_id: int, message: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 * @psalm-type Clarification=array{answer: null|string, author: null|string, clarification_id: int, contest_alias: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 */
class Clarifications extends \OmegaUp\DAO\Base\Clarifications {
    /**
     * @return list<Clarification>
     */
    final public static function getProblemsetClarifications(
        int $problemset_id,
        bool $admin,
        int $identityId,
        ?int $offset,
        int $rowcount
    ) {
        $sql = 'SELECT
                  c.clarification_id,
                  con.alias `contest_alias`,
                  p.alias `problem_alias`,
                  i.username `author`,
                  r.username `receiver`,
                  c.message,
                  c.answer,
                  c.`time`,
                  c.public
                FROM
                  `Clarifications` c
                INNER JOIN
                  `Identities` i ON i.identity_id = c.author_id
                LEFT JOIN
                  `Identities` r ON r.identity_id = c.receiver_id
                INNER JOIN
                  `Problems` p ON p.problem_id = c.problem_id
                INNER JOIN
                  `Problemsets` ps ON ps.problemset_id = c.problemset_id
                LEFT JOIN
                  `Contests` con ON con.contest_id = ps.contest_id
                WHERE
                  c.problemset_id = ? ';
        $val = [$problemset_id];

        if (!$admin) {
            $sql .= 'AND (c.public = 1 OR c.author_id = ? OR c.receiver_id = ?) ';
            $val[] = $identityId;
            $val[] = $identityId;
        }

        $sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = intval($offset);
            $val[] = intval($rowcount);
        }

        /** @var list<array{answer: null|string, author: string, clarification_id: int, contest_alias: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * @return list<ProblemClarification>
     */
    final public static function getProblemInProblemsetClarifications(
        \OmegaUp\DAO\VO\Problems $problem,
        int $problemsetId,
        bool $admin,
        \OmegaUp\DAO\VO\Identities $currentIdentity,
        ?int $offset,
        int $rowcount
    ) {
        $sql = '
            SELECT
                c.clarification_id,
                p.alias AS problem_alias,
                i.username AS author,
                r.username AS receiver,
                c.message,
                c.answer,
                c.`time`,
                c.public
            FROM
                Clarifications c
            INNER JOIN
                Identities i ON i.identity_id = c.author_id
            LEFT JOIN
                Identities r ON r.identity_id = c.receiver_id
            INNER JOIN
                Problems p ON p.problem_id = c.problem_id
            INNER JOIN
                Problemsets ps ON ps.problemset_id = c.problemset_id
            WHERE
                c.problemset_id = ?
                AND p.problem_id = ?
        ';

        $params = [
            $problemsetId,
            $problem->problem_id
        ];

        if (!$admin) {
            $sql .= '
                AND (
                    c.public = 1 OR c.author_id = ? OR c.receiver_id = ?
                )
            ';
            $params[] = $currentIdentity->identity_id;
            $params[] = $currentIdentity->identity_id;
        }

        $sql .= '
            ORDER BY c.answer IS NULL DESC,
            c.clarification_id DESC
        ';

        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $params[] = $offset;
            $params[] = $rowcount;
        }

        /** @var list<array{answer: null|string, author: string, clarification_id: int, message: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * @return list<Clarification>
     */
    final public static function GetProblemClarifications(
        int $problemId,
        bool $admin,
        int $identityId,
        ?int $offset,
        int $rowcount
    ) {
        $sql = '';
        if ($admin) {
            $sql = '
                SELECT
                    c.clarification_id,
                    p.alias AS problem_alias,
                    con.alias AS contest_alias,
                    i.username AS author,
                    r.username `receiver`,
                    c.message,
                    c.answer,
                    c.`time`,
                    c.public
                FROM
                    Clarifications c
                INNER JOIN
                    Identities i ON i.identity_id = c.author_id
                LEFT JOIN
                    Identities r ON r.identity_id = c.receiver_id
            ';
        } else {
            $sql = '
                SELECT
                    c.clarification_id,
                    p.alias AS problem_alias,
                    con.alias AS contest_alias,
                    "" AS author,
                    CAST(NULL AS CHAR) AS receiver,
                    c.message,
                    c.answer,
                    c.`time`,
                    c.public
                FROM Clarifications c
            ';
        }
        $sql .= '
            INNER JOIN
              `Problems` p ON p.problem_id = c.problem_id
            LEFT JOIN
                Contests con ON con.problemset_id = c.problemset_id
            WHERE
                c.problem_id = ?
        ';
        $val = [$problemId];

        if (!$admin) {
            $sql .= 'AND (c.public = 1 OR c.author_id = ?) ';
            $val[] = $identityId;
        }

        $sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = intval($offset);
            $val[] = intval($rowcount);
        }

        $result = [];
        /** @var array{answer: null|string, author: string, clarification_id: int, contest_alias: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            if (!$admin) {
                $row['author'] = null;
                $row['receiver'] = null;
            }
            $result[] = $row;
        }

        return $result;
    }
}
