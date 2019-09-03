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
    final public static function GetProblemsetClarifications($problemset_id, $admin, $identity_id, $offset, $rowcount) {
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
            $val[] = $identity_id;
            $val[] = $identity_id;
        }

        $sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = (int)$offset;
            $val[] = (int)$rowcount;
        }

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    final public static function GetProblemClarifications($problem_id, $admin, $identity_id, $offset, $rowcount) {
        $sql = '';
        if ($admin) {
            $sql = 'SELECT c.clarification_id, con.alias contest_alias, i.username author, ' .
                   'c.message, c.answer, UNIX_TIMESTAMP(c.time) `time`, c.public ' .
                   'FROM Clarifications c ' .
                   'INNER JOIN Identities i ON i.identity_id = c.author_id ';
        } else {
            $sql = 'SELECT c.clarification_id, con.alias contest_alias, c.message, ' .
                   'UNIX_TIMESTAMP(c.time) `time`, c.answer, c.public ' .
                   'FROM Clarifications c ';
        }
        $sql .= 'LEFT JOIN Contests con ON con.problemset_id = c.problemset_id '.
                'WHERE ' .
                'c.problem_id = ? ';
        $val = [$problem_id];

        if (!$admin) {
            $sql .= 'AND (c.public = 1 OR c.author_id = ?) ';
            $val[] = $identity_id;
        }

        $sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = (int)$offset;
            $val[] = (int)$rowcount;
        }

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }
}
