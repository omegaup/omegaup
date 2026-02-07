<?php

namespace OmegaUp\DAO;

/**
 * GSoC Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * relacionados con Google Summer of Code.
 *
 * @access public
 */
class GSoC {
    /**
     * Get all GSoC editions
     *
     * @return list<array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}>
     */
    public static function getEditions(): array {
        $sql = '
            SELECT
                edition_id,
                year,
                is_active,
                application_deadline,
                created_at,
                updated_at
            FROM
                GSoC_Edition
            ORDER BY
                year DESC;
        ';

        /** @var list<array{edition_id: int, year: int, is_active: int, application_deadline: string|null, created_at: string, updated_at: string}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, []);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'edition_id' => intval($row['edition_id']),
                'year' => intval($row['year']),
                'is_active' => boolval($row['is_active']),
                'application_deadline' => $row['application_deadline'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }
        return $result;
    }

    /**
     * Get GSoC edition by year
     *
     * @param int $year
     * @return array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}|null
     */
    public static function getEditionByYear(int $year): ?array {
        $sql = '
            SELECT
                edition_id,
                year,
                is_active,
                application_deadline,
                created_at,
                updated_at
            FROM
                GSoC_Edition
            WHERE
                year = ?
            LIMIT 1;
        ';

        /** @var array{edition_id: int, year: int, is_active: int, application_deadline: string|null, created_at: string, updated_at: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$year]);
        if (empty($row)) {
            return null;
        }

        return [
            'edition_id' => intval($row['edition_id']),
            'year' => intval($row['year']),
            'is_active' => boolval($row['is_active']),
            'application_deadline' => $row['application_deadline'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    /**
     * Get GSoC edition by ID
     *
     * @param int $editionId
     * @return array{edition_id: int, year: int, is_active: bool, application_deadline: string|null, created_at: string, updated_at: string}|null
     */
    public static function getEditionById(int $editionId): ?array {
        $sql = '
            SELECT
                edition_id,
                year,
                is_active,
                application_deadline,
                created_at,
                updated_at
            FROM
                GSoC_Edition
            WHERE
                edition_id = ?
            LIMIT 1;
        ';

        /** @var array{edition_id: int, year: int, is_active: int, application_deadline: string|null, created_at: string, updated_at: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$editionId]
        );
        if (empty($row)) {
            return null;
        }

        return [
            'edition_id' => intval($row['edition_id']),
            'year' => intval($row['year']),
            'is_active' => boolval($row['is_active']),
            'application_deadline' => $row['application_deadline'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    /**
     * Create a new GSoC edition
     *
     * @param int $year
     * @param bool $isActive
     * @param string|null $applicationDeadline
     * @return int The edition_id of the newly created edition
     */
    public static function createEdition(
        int $year,
        bool $isActive = false,
        ?string $applicationDeadline = null
    ): int {
        $sql = '
            INSERT INTO
                GSoC_Edition (year, is_active, application_deadline)
            VALUES
                (?, ?, ?);
        ';

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$year, $isActive ? 1 : 0, $applicationDeadline]
        );

        return intval(\OmegaUp\MySQLConnection::getInstance()->Insert_ID());
    }

    /**
     * Update a GSoC edition
     *
     * @param int $editionId
     * @param int|null $year
     * @param bool|null $isActive
     * @param string|null $applicationDeadline
     * @return int Number of affected rows
     */
    public static function updateEdition(
        int $editionId,
        ?int $year = null,
        ?bool $isActive = null,
        ?string $applicationDeadline = null
    ): int {
        $fields = [];
        $params = [];

        if (!is_null($year)) {
            $fields[] = 'year = ?';
            $params[] = $year;
        }
        if (!is_null($isActive)) {
            $fields[] = 'is_active = ?';
            $params[] = $isActive ? 1 : 0;
        }
        if (!is_null($applicationDeadline)) {
            $fields[] = 'application_deadline = ?';
            $params[] = $applicationDeadline;
        }

        if (empty($fields)) {
            return 0;
        }

        $params[] = $editionId;
        $sql = '
            UPDATE
                GSoC_Edition
            SET
                ' . implode(', ', $fields) . '
            WHERE
                edition_id = ?;
        ';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Get all GSoC ideas, optionally filtered by edition
     *
     * @param int|null $editionId
     * @param string|null $status
     * @return list<array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}>
     */
    public static function getIdeas(
        ?int $editionId = null,
        ?string $status = null
    ): array {
        $sql = '
            SELECT
                i.idea_id,
                i.edition_id,
                i.title,
                i.brief_description,
                i.expected_results,
                i.preferred_skills,
                i.possible_mentors,
                i.estimated_hours,
                i.skill_level,
                i.status,
                i.blog_link,
                i.contributor_username,
                i.created_at,
                i.updated_at
            FROM
                GSoC_Idea i
            INNER JOIN
                GSoC_Edition e ON i.edition_id = e.edition_id
            WHERE
                1 = 1
        ';

        $params = [];
        if (!is_null($editionId)) {
            $sql .= ' AND i.edition_id = ?';
            $params[] = $editionId;
        }
        if (!is_null($status)) {
            $sql .= ' AND i.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY e.year DESC, i.created_at DESC;';

        /** @var list<array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    /**
     * Get GSoC idea by ID
     *
     * @param int $ideaId
     * @return array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}|null
     */
    public static function getIdeaById(int $ideaId): ?array {
        $sql = '
            SELECT
                idea_id,
                edition_id,
                title,
                brief_description,
                expected_results,
                preferred_skills,
                possible_mentors,
                estimated_hours,
                skill_level,
                status,
                blog_link,
                contributor_username,
                created_at,
                updated_at
            FROM
                GSoC_Idea
            WHERE
                idea_id = ?
            LIMIT 1;
        ';

        /** @var array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$ideaId]);
        if (empty($row)) {
            return null;
        }
        return $row;
    }

    /**
     * Create a new GSoC idea
     *
     * @param int $editionId
     * @param string $title
     * @param string|null $briefDescription
     * @param string|null $expectedResults
     * @param string|null $preferredSkills
     * @param string|null $possibleMentors
     * @param int|null $estimatedHours
     * @param string|null $skillLevel
     * @param string $status
     * @param string|null $blogLink
     * @param string|null $contributorUsername
     * @return int The idea_id of the newly created idea
     */
    public static function createIdea(
        int $editionId,
        string $title,
        ?string $briefDescription = null,
        ?string $expectedResults = null,
        ?string $preferredSkills = null,
        ?string $possibleMentors = null,
        ?int $estimatedHours = null,
        ?string $skillLevel = null,
        string $status = 'Proposed',
        ?string $blogLink = null,
        ?string $contributorUsername = null
    ): int {
        $sql = '
            INSERT INTO
                GSoC_Idea (
                    edition_id,
                    title,
                    brief_description,
                    expected_results,
                    preferred_skills,
                    possible_mentors,
                    estimated_hours,
                    skill_level,
                    status,
                    blog_link,
                    contributor_username
                )
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
        ';

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [
                $editionId,
                $title,
                $briefDescription,
                $expectedResults,
                $preferredSkills,
                $possibleMentors,
                $estimatedHours,
                $skillLevel,
                $status,
                $blogLink,
                $contributorUsername,
            ]
        );

        return intval(\OmegaUp\MySQLConnection::getInstance()->Insert_ID());
    }

    /**
     * Update a GSoC idea
     *
     * @param int $ideaId
     * @param int|null $editionId
     * @param string|null $title
     * @param string|null $briefDescription
     * @param string|null $expectedResults
     * @param string|null $preferredSkills
     * @param string|null $possibleMentors
     * @param int|null $estimatedHours
     * @param string|null $skillLevel
     * @param string|null $status
     * @param string|null $blogLink
     * @param string|null $contributorUsername
     * @return int Number of affected rows
     */
    public static function updateIdea(
        int $ideaId,
        ?int $editionId = null,
        ?string $title = null,
        ?string $briefDescription = null,
        ?string $expectedResults = null,
        ?string $preferredSkills = null,
        ?string $possibleMentors = null,
        ?int $estimatedHours = null,
        ?string $skillLevel = null,
        ?string $status = null,
        ?string $blogLink = null,
        ?string $contributorUsername = null
    ): int {
        $fields = [];
        $params = [];

        if (!is_null($editionId)) {
            $fields[] = 'edition_id = ?';
            $params[] = $editionId;
        }
        if (!is_null($title)) {
            $fields[] = 'title = ?';
            $params[] = $title;
        }
        if (!is_null($briefDescription)) {
            $fields[] = 'brief_description = ?';
            $params[] = $briefDescription;
        }
        if (!is_null($expectedResults)) {
            $fields[] = 'expected_results = ?';
            $params[] = $expectedResults;
        }
        if (!is_null($preferredSkills)) {
            $fields[] = 'preferred_skills = ?';
            $params[] = $preferredSkills;
        }
        if (!is_null($possibleMentors)) {
            $fields[] = 'possible_mentors = ?';
            $params[] = $possibleMentors;
        }
        if (!is_null($estimatedHours)) {
            $fields[] = 'estimated_hours = ?';
            $params[] = $estimatedHours;
        }
        if (!is_null($skillLevel)) {
            $fields[] = 'skill_level = ?';
            $params[] = $skillLevel;
        }
        if (!is_null($status)) {
            $fields[] = 'status = ?';
            $params[] = $status;
        }
        if (!is_null($blogLink)) {
            $fields[] = 'blog_link = ?';
            $params[] = $blogLink;
        }
        if (!is_null($contributorUsername)) {
            $fields[] = 'contributor_username = ?';
            $params[] = $contributorUsername;
        }

        if (empty($fields)) {
            return 0;
        }

        $params[] = $ideaId;
        $sql = '
            UPDATE
                GSoC_Idea
            SET
                ' . implode(', ', $fields) . '
            WHERE
                idea_id = ?;
        ';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Delete a GSoC idea
     *
     * @param int $ideaId
     * @return int Number of affected rows
     */
    public static function deleteIdea(int $ideaId): int {
        $sql = '
            DELETE FROM
                GSoC_Idea
            WHERE
                idea_id = ?;
        ';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$ideaId]);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
}
