<?php

namespace OmegaUp\DAO;

/**
 * UserReadmes Data Access Object (DAO).
 *
 * This class contains all the database manipulation needed to permanently
 * store and retrieve instances of {@link \OmegaUp\DAO\VO\UserReadmes}.
 *
 * @access public
 */
class UserReadmes extends \OmegaUp\DAO\Base\UserReadmes {
    /**
     * Get the README for a user by their user_id.
     *
     * @param int $userId The user's ID
     * @return ?\OmegaUp\DAO\VO\UserReadmes The user's README or null if it does not exist
     */
    final public static function getByUserId(int $userId): ?\OmegaUp\DAO\VO\UserReadmes {
        $sql = '
            SELECT
                `User_Readmes`.`readme_id`,
                `User_Readmes`.`user_id`,
                `User_Readmes`.`content`,
                `User_Readmes`.`is_visible`,
                `User_Readmes`.`last_edit_time`,
                `User_Readmes`.`report_count`,
                `User_Readmes`.`is_disabled`
            FROM
                `User_Readmes`
            WHERE
                `user_id` = ?
            LIMIT 1;';

        $params = [$userId];
        /** @var array{content: string, is_disabled: bool, is_visible: bool, last_edit_time: \OmegaUp\Timestamp, readme_id: int, report_count: int, user_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);

        if (empty($row)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\UserReadmes($row);
    }

    /**
     * Increment the report counter for a README.
     * This operation is atomic and safe for concurrent updates.
     *
     * @param int $readmeId The README's ID
     * @return void
     */
    final public static function incrementReportCount(int $readmeId): void {
        $sql = '
            UPDATE
                `User_Readmes`
            SET
                `report_count` = `report_count` + 1
            WHERE
                `readme_id` = ?;';

        $params = [$readmeId];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }

    /**
     * Set the disabled state of a README.
     * Allows enabling or disabling a README, typically used
     * by moderators when a README receives too many reports.
     *
     * @param int $readmeId The README's ID
     * @param bool $isDisabled True to disable, false to enable
     * @return void
     */
    final public static function setDisabled(
        int $readmeId,
        bool $isDisabled
    ): void {
        $sql = '
            UPDATE
                `User_Readmes`
            SET
                `is_disabled` = ?
            WHERE
                `readme_id` = ?;';

        $params = [
            intval($isDisabled),
            $readmeId
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
    }
}
