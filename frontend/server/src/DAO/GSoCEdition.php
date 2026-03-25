<?php

namespace OmegaUp\DAO;

/**
 * GSoCEdition Data Access Object (DAO).
 */
class GSoCEdition extends \OmegaUp\DAO\Base\GSoCEdition {
    /**
     * @return list<array{application_deadline: null|string, created_at: null|string, edition_id: int, is_active: bool, updated_at: null|string, year: int}>
     */
    public static function getEditions(): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GSoCEdition::FIELD_NAMES,
            'ge'
        );
        $sql = "
            SELECT
                {$fields}
            FROM
                `GSoC_Edition` `ge`
            ORDER BY
                `ge`.`year` DESC
            LIMIT 50;
        ";
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * @return array{application_deadline: null|string, created_at: null|string, edition_id: int, is_active: bool, updated_at: null|string, year: int}|null
     */
    public static function getEditionByYear(int $year): ?array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GSoCEdition::FIELD_NAMES,
            'ge'
        );
        $sql = "
            SELECT
                {$fields}
            FROM
                `GSoC_Edition` `ge`
            WHERE
                `ge`.`year` = ?
            LIMIT 1;
        ";
        return \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$year]);
    }
}
