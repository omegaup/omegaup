<?php

namespace OmegaUp\DAO;

/**
 * Clarifications Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Clarifications}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Clarifications extends \OmegaUp\DAO\Base\Clarifications {
    /**
     * @return list<array{answer: null|string, author: string, clarification_id: int, message: string, problem_alias: string, public: bool, receiver: null|string, time: int}>
     */
    final public static function GetProblemsetClarifications(
        int $problemset_id,
        bool $admin,
        int $identityId,
        ?int $offset,
        int $rowcount
    ) {
        $sql = 'SELECT
                  c.clarification_id,
                  p.alias `problem_alias`,
                  i.username `author`,
                  r.username `receiver`,
                  c.message,
                  c.answer,
                  UNIX_TIMESTAMP(c.time) `time`,
                  c.public
                FROM
                  `Clarifications` c
                INNER JOIN
                  `Identities` i ON i.identity_id = c.author_id
                LEFT JOIN
                  `Identities` r ON r.identity_id = c.receiver_id
                INNER JOIN
                  `Problems` p ON p.problem_id = c.problem_id
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

        /** @var list<array{answer: null|string, author: string, clarification_id: int, message: string, problem_alias: string, public: bool, receiver: null|string, time: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * @return list<array{clarification_id: int, contest_alias: string, author: null|string, message: string, time: \OmegaUp\Timestamp, answer: null|string, public: bool}>
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
                    con.alias AS contest_alias,
                    i.username AS author,
                    c.message,
                    c.answer,
                    c.`time`,
                    c.public
                FROM
                    Clarifications c
                INNER JOIN
                    Identities i ON i.identity_id = c.author_id
            ';
        } else {
            $sql = '
                SELECT
                    c.clarification_id,
                    con.alias AS contest_alias,
                    NULL AS author,
                    c.message,
                    c.`time`,
                    c.answer,
                    c.public
                FROM Clarifications c
            ';
        }
        $sql .= '
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

        /** @var list<array{clarification_id: int, contest_alias: string, author: null|string, message: string, time: \OmegaUp\Timestamp, answer: null|string, public: bool}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }
}
