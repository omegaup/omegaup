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
    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Interviews WHERE alias = ? LIMIT 1;';
        $params = [$alias];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        $interview = new \OmegaUp\DAO\VO\Interviews($rs);

        return $interview;
    }

    final public static function getMyInterviews($user_id) {
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

        $params = [$user_id, $user_id, \OmegaUp\Authorization::ADMIN_ROLE];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $result = [];

        foreach ($rs as $r) {
            $result[] = $r;
        }

        return $result;
    }

    public static function getInterviewForProblemset($problemset_id) {
        if (is_null($problemset_id)) {
            return null;
        }

        return self::getByProblemset($problemset_id);
    }

    public static function getByProblemset($problemset_id) {
        $sql = 'SELECT i.* from Interviews i where i.problemset_id = ?;';

        $interviews = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$problemset_id]
        );
        if (empty($interviews)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Interviews($interviews);
    }
}
