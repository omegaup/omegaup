<?php

namespace OmegaUp\DAO;

/**
 * UserCodeTemplates Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserCodeTemplates}.
 *
 * @access public
 */
class UserCodeTemplates extends \OmegaUp\DAO\Base\UserCodeTemplates {
    /**
     * Get all templates for a specific user
     *
     * @return list<\OmegaUp\DAO\VO\UserCodeTemplates>
     */
    final public static function getByUserId(int $userId): array {
        $sql = 'SELECT
                    *
                FROM
                    User_Code_Templates
                WHERE
                    user_id = ?
                ORDER BY
                    language ASC, template_name ASC;';
        /** @var list<array{code: string, created_at: \OmegaUp\Timestamp, language: string, template_id: int, template_name: string, updated_at: \OmegaUp\Timestamp, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$userId]);
        $templates = [];
        foreach ($rs as $row) {
            $templates[] = new \OmegaUp\DAO\VO\UserCodeTemplates($row);
        }
        return $templates;
    }

    /**
     * Get all templates for a specific user and language
     *
     * @return list<\OmegaUp\DAO\VO\UserCodeTemplates>
     */
    final public static function getByUserIdAndLanguage(
        int $userId,
        string $language
    ): array {
        $sql = 'SELECT
                    *
                FROM
                    User_Code_Templates
                WHERE
                    user_id = ? AND language = ?
                ORDER BY
                    template_name ASC;';
        /** @var list<array{code: string, created_at: \OmegaUp\Timestamp, language: string, template_id: int, template_name: string, updated_at: \OmegaUp\Timestamp, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$userId, $language]
        );
        $templates = [];
        foreach ($rs as $row) {
            $templates[] = new \OmegaUp\DAO\VO\UserCodeTemplates($row);
        }
        return $templates;
    }

    /**
     * Get a specific template by user, language, and name
     */
    final public static function getByUserLanguageAndName(
        int $userId,
        string $language,
        string $templateName
    ): ?\OmegaUp\DAO\VO\UserCodeTemplates {
        $sql = 'SELECT
                    *
                FROM
                    User_Code_Templates
                WHERE
                    user_id = ? AND language = ? AND template_name = ?;';
        /** @var array{code: string, created_at: \OmegaUp\Timestamp, language: string, template_id: int, template_name: string, updated_at: \OmegaUp\Timestamp, user_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$userId, $language, $templateName]
        );
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\UserCodeTemplates($rs);
    }
}
