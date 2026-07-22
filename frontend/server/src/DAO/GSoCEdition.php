<?php

namespace OmegaUp\DAO;

/**
 * GSoCEdition Data Access Object (DAO).
 */
class GSoCEdition extends \OmegaUp\DAO\Base\GSoCEdition {
    /**
     * @return array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}
     */
    private static function toPublicArray(
        \OmegaUp\DAO\VO\GSoCEdition $edition
    ): array {
        return [
            'edition_id' => intval($edition->edition_id),
            'year' => intval($edition->year),
            'is_active' => boolval($edition->is_active),
            'application_deadline' => is_null($edition->application_deadline)
                ? null
                : \OmegaUp\DAO\DAO::toMySQLTimestamp($edition->application_deadline),
            'created_at' => \OmegaUp\DAO\DAO::toMySQLTimestamp($edition->created_at),
            'updated_at' => \OmegaUp\DAO\DAO::toMySQLTimestamp($edition->updated_at),
        ];
    }

    /**
     * @return list<array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}>
     */
    public static function getEditions(): array {
        $result = [];
        foreach (self::getAll() as $edition) {
            $result[] = self::toPublicArray($edition);
        }
        usort(
            $result,
            /**
             * @param array{year: int} $a
             * @param array{year: int} $b
             */
            function (array $a, array $b): int {
                return $b['year'] <=> $a['year'];
            }
        );
        return $result;
    }

    /**
     * @return array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}|null
     */
    public static function getEditionByYear(int $year): ?array {
        $sql = '
            SELECT
                edition_id
            FROM
                GSoC_Edition
            WHERE
                year = ?
            LIMIT 1;
        ';
        /** @var array{edition_id: int|string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$year]);
        if (empty($row)) {
            return null;
        }
        $edition = self::getByPK(intval($row['edition_id']));
        if (is_null($edition)) {
            return null;
        }
        return self::toPublicArray($edition);
    }
}
