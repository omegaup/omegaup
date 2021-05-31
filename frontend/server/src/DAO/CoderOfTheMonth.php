<?php

namespace OmegaUp\DAO;

/**
 * CoderOfTheMonth Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CoderOfTheMonth}.
 * @access public
 * @package docs
 */
class CoderOfTheMonth extends \OmegaUp\DAO\Base\CoderOfTheMonth {
    /**
     * Gets the users that are candidates to coder of the month
     * time period.
     * category.
     *
     * @return list<array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int, username: string}>
     */
    public static function getCandidatesToCoderOfTheMonth(
        string $time,
        string $category = 'all',
        int $rowCount = 100
    ): array {
        $sql = "SELECT
            `c`.*,
            `i`.`username`,
            IFNULL(`i`.country_id, 'xx') AS country_id,
            IFNULL(
              (
                SELECT
                  `urc`.classname
                FROM
                  `User_Rank_Cutoffs` AS `urc`
                WHERE
                  `urc`.`score` <= (
                    SELECT
                      `ur`.score
                    FROM
                      `User_Rank` AS `ur`
                    WHERE
                      `ur`.`user_id` = `c`.`user_id`
                  )
                ORDER BY
                  `urc`.`percentile` ASC LIMIT 1
              ),
              'user-rank-unranked'
            ) AS classname
          FROM
            `Coder_Of_The_Month` AS `c`
          INNER JOIN
            `Identities` AS `i` ON `i`.`user_id` = `c`.`user_id`
          WHERE
            `time` = ? AND
            `category` = ?
          LIMIT ?;
        ";

        /** @var list<array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$time, $category, $rowCount]
        );
    }

    /**
     * Get all first coders of the month
     * @return list<array{time: string, username: string, country_id: string, email: string|null}>
     */
    final public static function getCodersOfTheMonth(string $category = 'all'): array {
        $date = date('Y-m-01', \OmegaUp\Time::get());
        $sql = "
          SELECT
              cm.time,
              i.username,
              IFNULL(i.country_id, 'xx') AS country_id,
              e.email
          FROM
              Coder_Of_The_Month cm
          INNER JOIN
              Users u ON u.user_id = cm.user_id
          INNER JOIN
              Identities i ON i.identity_id = u.main_identity_id
          LEFT JOIN
              Emails e ON e.user_id = u.user_id
          WHERE
              (cm.selected_by IS NOT NULL
              OR (
                  cm.`ranking` = 1 AND
                  NOT EXISTS (
                      SELECT
                          *
                      FROM
                          Coder_Of_The_Month
                      WHERE
                          time = cm.time AND
                          selected_by IS NOT NULL AND
                          category = ?
                  )
              ))
              AND cm.category = ?
              AND cm.time <= ?
          ORDER BY
              cm.time DESC;
      ";

      /** @var list<array{country_id: string, email: null|string, time: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$category, $category, $date]
        );
    }

    /**
     * Gets all coders of the month from a certain school
     *
     * @return list<array{time: string, username: string, classname: string}>
     */
    final public static function getCodersOfTheMonthFromSchool(
        int $schoolId
    ): array {
        $date = date('Y-m-01', \OmegaUp\Time::get());
        $sql = "
            SELECT DISTINCT
              cm.time,
              i.username,
              IFNULL(
                (
                  SELECT urc.classname
                  FROM User_Rank_Cutoffs urc
                  WHERE
                      urc.score <= (
                          SELECT
                              ur.score
                          FROM
                              User_Rank ur
                          WHERE
                              ur.user_id = i.user_id
                      )
                  ORDER BY
                      urc.percentile ASC
                  LIMIT 1
                ),
                'user-rank-unranked'
              ) AS classname
            FROM
              Coder_Of_The_Month cm
            INNER JOIN
              Users u ON u.user_id = cm.user_id
            INNER JOIN
              Identities i ON i.identity_id = u.main_identity_id
            LEFT JOIN
              Emails e ON e.user_id = u.user_id
            WHERE
              (cm.`ranking` = 1 OR cm.selected_by IS NOT NULL) AND
              cm.school_id = ? AND
              cm.time <= ?
            ORDER BY
              cm.time DESC;
        ";

        /** @var list<array{classname: string, time: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$schoolId, $date]
        );
    }

    /**
     * Get all coder of the months based on month
     * @return list<array{country_id: string, email: null|string, ranking: int, time: string, user_id: int, username: string}>
     */
    final public static function getMonthlyList(
        string $firstDay,
        string $category = 'all'
    ): array {
        $date = date('Y-m-01', strtotime($firstDay));
        $sql = "
          SELECT
            cm.time,
            cm.`ranking`,
            i.username,
            IFNULL(i.country_id, 'xx') AS country_id,
            e.email,
            u.user_id
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          INNER JOIN
            Identities i ON u.main_identity_id = i.identity_id
          LEFT JOIN
            Emails e ON e.email_id = u.main_email_id
          WHERE
            cm.time = ? AND
            cm.category = ?
          ORDER BY
            cm.time DESC,
            cm.`ranking` ASC
          LIMIT 100
        ";
        /** @var list<array{country_id: string, email: null|string, ranking: int, time: string, user_id: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->getAll(
            $sql,
            [$date, $category]
        );
    }

    /**
     * Get true whether user is the last Coder of the month
     */
    final public static function isLastCoderOfTheMonth(
        string $username,
        string $category = 'all'
    ): bool {
        $date = date('Y-m-01', \OmegaUp\Time::get());
        $sql = '
          SELECT
            i.username
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          INNER JOIN
            Identities i ON u.main_identity_id = i.identity_id
          WHERE
            cm.`ranking` = 1 AND
            cm.category = ? AND
            cm.time <= ?
          ORDER BY
            cm.time DESC
          LIMIT 1
        ';

        /** @var array{username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$category, $date]
        );
        if (empty($rs)) {
            return false;
        }
        return $username == $rs['username'];
    }

    /**
     * @return list<\OmegaUp\DAO\VO\CoderOfTheMonth>
     */
    final public static function getByTimeAndSelected(
        string $time,
        bool $autoselected = false,
        string $category = 'all'
    ): array {
        $clause = $autoselected ? 'IS NULL' : 'IS NOT NULL';
        $sql = "SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ? AND
                    category = ?
                AND
                    `selected_by` {$clause};";
        /** @var list<array{category: string, coder_of_the_month_id: int, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$time,$category]
        );

        $coders = [];
        foreach ($rs as $row) {
            $coders[] = new \OmegaUp\DAO\VO\CoderOfTheMonth($row);
        }
        return $coders;
    }

    /**
     * @param string $time
     * @return \OmegaUp\DAO\VO\CoderOfTheMonth[]
     */
    final public static function getByTime(
        string $time,
        string $category = 'all'
    ): array {
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ? AND
                    category = ?;';

        /** @var list<array{category: string, coder_of_the_month_id: int, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$time, $category]
        );

        $coders = [];
        foreach ($rs as $row) {
            $coders[] = new \OmegaUp\DAO\VO\CoderOfTheMonth($row);
        }
        return $coders;
    }
}
