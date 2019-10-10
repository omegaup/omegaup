<?php

namespace OmegaUp\DAO;

/**
 * PrivacyStatements Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\PrivacyStatements}.
 *
 * @access public
 */
class PrivacyStatements extends \OmegaUp\DAO\Base\PrivacyStatements {
    /**
     * @return array{privacystatement_id: int, git_object_id: string}|null
     */
    public static function getLatestPublishedStatement(
        string $statementType = 'privacy_policy'
    ) : ?array {
        $sql = 'SELECT
                  privacystatement_id,
                  git_object_id
                FROM
                  `PrivacyStatements`
                WHERE
                  type = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        /** @val array{privacystatement_id: int, git_object_id: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$statementType]
        );
    }

    public static function getId(string $gitObjectId, string $statementType) : ?int {
        $sql = 'SELECT
                  privacystatement_id
                FROM
                  `PrivacyStatements`
                WHERE
                  git_object_id = ?
                  AND type = ?
                ORDER BY
                  privacystatement_id DESC
                LIMIT 1';
        /** @var int|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$gitObjectId, $statementType]);
    }
}
