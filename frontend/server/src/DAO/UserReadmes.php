<?php

namespace OmegaUp\DAO;

/**
 * UserReadmes Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserReadmes}.
 *
 * @access public
 */
class UserReadmes extends \OmegaUp\DAO\Base\UserReadmes {
    /**
     * Obtener el README de un usuario por su user_id.
     *
     * @param int $userId El ID del usuario
     * @return ?\OmegaUp\DAO\VO\UserReadmes El README del usuario o null si no existe
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
     * Incrementar el contador de reportes para un README.
     * Esta operación es atómica y segura para actualizaciones concurrentes.
     *
     * @param int $readmeId El ID del README
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
     * Establecer el estado de deshabilitación de un README.
     * Permite habilitar o deshabilitar un README, típicamente usado
     * por moderadores cuando un README recibe demasiados reportes.
     *
     * @param int $readmeId El ID del README
     * @param bool $isDisabled True para deshabilitar, false para habilitar
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
