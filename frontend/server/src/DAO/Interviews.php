<?php

namespace OmegaUp\DAO;

/**
 * Interviews Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Interviews}.
 *
 * @access public
 */
class Interviews extends \OmegaUp\DAO\Base\Interviews {
    final public static function getByAlias(
        string $alias
    ): ?\OmegaUp\DAO\VO\Interviews {
        $sql = 'SELECT * FROM Interviews WHERE alias = ? LIMIT 1;';
        $params = [$alias];

        /** @var array{acl_id: int, alias: string, description: string, interview_id: int, problemset_id: int, title: string, window_length: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Interviews($rs);
    }

    /**
     * @return list<array{acl_id: int, alias: string, description: string, interview_id: int, problemset_id: int, title: string, window_length: int}>
     */
    final public static function getMyInterviews(int $userId): array {
        $sql = '
            SELECT
                i.*
            FROM
                Interviews AS i
            INNER JOIN
                ACLs AS a
            ON
                a.acl_id = i.acl_id
            WHERE
                a.owner_id = ?
                OR (SELECT COUNT(*) FROM User_Roles WHERE user_id = ? AND role_id = ? AND acl_id = a.acl_id) > 0;';

        $params = [$userId, $userId, \OmegaUp\Authorization::ADMIN_ROLE];

        /** @var list<array{acl_id: int, alias: string, description: string, interview_id: int, problemset_id: int, title: string, window_length: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    public static function getInterviewForProblemset(
        ?int $problemsetId
    ): ?\OmegaUp\DAO\VO\Interviews {
        if (is_null($problemsetId)) {
            return null;
        }
        return self::getByProblemset($problemsetId);
    }

    public static function getByProblemset(
        int $problemsetId
    ): ?\OmegaUp\DAO\VO\Interviews {
        $sql = 'SELECT i.* from Interviews i where i.problemset_id = ?;';

        $interviews = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemsetId]
        );
        if (empty($interviews)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Interviews($interviews);
    }
}
