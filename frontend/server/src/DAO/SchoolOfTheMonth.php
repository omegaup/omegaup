<?php

namespace OmegaUp\DAO;

/**
 * SchoolOfTheMonth Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SchoolOfTheMonth}.
 * @access public
 */
class SchoolOfTheMonth extends \OmegaUp\DAO\Base\SchoolOfTheMonth {
    /**
     * Returns the list of candidates to school of the month
     *
     * @return list<array{country_id: string, name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>
     */
    public static function getCandidatesToSchoolOfTheMonth(
        int $rowcount = 100,
        string $firstDayOfMonth = null
    ): array {
        if ($firstDayOfMonth === null) {
            $currentDate = date('Y-m-d', \OmegaUp\Time::get());
            $date = new \DateTimeImmutable($currentDate);
            $firstDayOfMonth = $date->modify(
                'first day of next month'
            )->format(
                'Y-m-d'
            );
        }
        $alreadySelectedSchools = self::getByTimeAndSelected(
            $firstDayOfMonth
        );
        if (!empty($alreadySelectedSchools)) {
            return [];
        }

        $sql = '
            SELECT
                s.school_id,
                s.name,
                IFNULL(s.country_id, "xx") AS country_id,
                sotm.school_of_the_month_id,
                sotm.score,
                sotm.`ranking`
            FROM
                School_Of_The_Month sotm
            INNER JOIN
                Schools s ON s.school_id = sotm.school_id
            WHERE
                sotm.time = ? AND
                sotm.selected_by IS NULL
            ORDER BY
                sotm.`ranking` ASC
            LIMIT
                ?;';

        /** @var list<array{country_id: string, name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}> */
        return \OmegaUp\MySQLConnection::getInstance()->getAll(
            $sql,
            [ $firstDayOfMonth, $rowcount ]
        );
    }

    /**
     * Gets all the best schools based on the month
     * of a certain date.
     *
     * @return list<array{country_id: string, name: string, ranking: int, school_id: int}>
     */
    public static function getMonthlyList(
        string $firstDay
    ): array {
        $date = date('Y-m-01', strtotime($firstDay));
        $sql = '
            SELECT
                sotm.school_id,
                sotm.`ranking`,
                s.name,
                IFNULL(s.country_id, "xx") AS country_id
            FROM
                School_Of_The_Month sotm
            INNER JOIN
                Schools s ON s.school_id = sotm.school_id
            WHERE
                sotm.time = ?
            ORDER BY
                sotm.selected_by IS NULL,
                sotm.`ranking` ASC
            LIMIT 100;';

        /** @var list<array{country_id: string, name: string, ranking: int, school_id: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->getAll(
            $sql,
            [ $date ]
        );
    }

    /**
     * Returns true if the school of the month for a certain date
     * has been previously selected
     */
    public static function isSchoolOfTheMonthAlreadySelected(string $time): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                School_Of_The_Month
            WHERE
                `time` = ?;';

        return \OmegaUp\MySQLConnection::getInstance()->getOne(
            $sql,
            [$time]
        ) > 0;
    }

    /**
     * Gets the best school of each month
     *
     * @return list<array{school_id: int, name: string, country_id: string, time: string}>
     */
    public static function getSchoolsOfTheMonth(): array {
        $currentTimestamp = \OmegaUp\Time::get();
        $currentDate = date('Y-m-01', $currentTimestamp);
        $sql = '
            SELECT
                sotm.school_id,
                sotm.time,
                s.name,
                IFNULL(s.country_id, "xx") AS country_id
            FROM
                School_Of_The_Month sotm
            INNER JOIN
                Schools s ON s.school_id = sotm.school_id
            WHERE
                sotm.time <= ? AND
                (
                    sotm.selected_by IS NOT NULL
                    OR (
                        sotm.`ranking` = 1 AND
                        NOT EXISTS (
                            SELECT
                                *
                            FROM
                                School_Of_The_Month
                            WHERE
                                time = sotm.time AND selected_by IS NOT NULL
                        )
                    )
                )
            ORDER BY
                sotm.time DESC;';

        /** @var list<array{country_id: string, name: string, school_id: int, time: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->getAll(
            $sql,
            [
                $currentDate,
            ]
        );
    }

    /**
     * @return list<\OmegaUp\DAO\VO\SchoolOfTheMonth>
     */
    public static function getByTimeAndSelected(
        string $time,
        bool $autoselected = false
    ): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\SchoolOfTheMonth::FIELD_NAMES,
            'School_Of_The_Month'
        );
        $clause = $autoselected ? 'IS NULL' : 'IS NOT NULL';
        $sql = "
            SELECT
                {$fields}
            FROM
                School_Of_The_Month
            WHERE
                time = ?
            AND
                selected_by {$clause};";

        $schools = [];
        /** @var array{ranking: int, school_id: int, school_of_the_month_id: int, score: float, selected_by: int|null, time: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$time]
            ) as $row
        ) {
            $schools[] = new \OmegaUp\DAO\VO\SchoolOfTheMonth($row);
        }
        return $schools;
    }

    /**
     * @return list<\OmegaUp\DAO\VO\SchoolOfTheMonth>
     */
    public static function getByTime(
        string $time
    ): array {
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\SchoolOfTheMonth::FIELD_NAMES,
            'School_Of_The_Month'
        ) . '
            FROM
                School_Of_The_Month
            WHERE
                time = ?;';

        $schools = [];
        /** @var array{ranking: int, school_id: int, school_of_the_month_id: int, score: float, selected_by: int|null, time: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$time]
            ) as $row
        ) {
            $schools[] = new \OmegaUp\DAO\VO\SchoolOfTheMonth($row);
        }
        return $schools;
    }
}
