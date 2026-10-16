<?php

namespace OmegaUp\DAO;

/**
 * GSoCIdea Data Access Object (DAO).
 */
class GSoCIdea extends \OmegaUp\DAO\Base\GSoCIdea {
    /**
     * @param array{
     *     idea_id: int|string,
     *     edition_id: int|string|null,
     *     title: string,
     *     brief_description: string|null,
     *     expected_results: string|null,
     *     preferred_skills: string|null,
     *     possible_mentors: string|null,
     *     estimated_hours: int|string|null,
     *     skill_level: string|null,
     *     status: string|null,
     *     blog_link: string|null,
     *     contributor_username: string|null,
     *     created_at: string,
     *     updated_at: string
     * } $row
     * @return array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}
     */
    private static function toPublicArray(array $row): array {
        return [
            'idea_id' => intval($row['idea_id']),
            'edition_id' => intval($row['edition_id'] ?? 0),
            'title' => $row['title'],
            'brief_description' => $row['brief_description'],
            'expected_results' => $row['expected_results'],
            'preferred_skills' => $row['preferred_skills'],
            'possible_mentors' => $row['possible_mentors'],
            'estimated_hours' => is_null($row['estimated_hours']) ? null : intval($row['estimated_hours']),
            'skill_level' => $row['skill_level'],
            'status' => is_null($row['status']) ? 'Proposed' : $row['status'],
            'blog_link' => $row['blog_link'],
            'contributor_username' => $row['contributor_username'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    /**
     * @return list<array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}>
     */
    public static function getIdeas(
        ?int $editionId = null,
        ?string $status = null
    ): array {
        $sql = '
            SELECT
                i.idea_id,
                ie.edition_id,
                i.title,
                i.brief_description,
                i.expected_results,
                i.preferred_skills,
                i.possible_mentors,
                i.estimated_hours,
                i.skill_level,
                ie.status,
                i.blog_link,
                i.contributor_username,
                i.created_at,
                i.updated_at
            FROM
                GSoC_Idea i
            INNER JOIN
                GSoC_Idea_Edition ie ON ie.idea_id = i.idea_id
            INNER JOIN
                GSoC_Edition e ON e.edition_id = ie.edition_id
            WHERE
                1 = 1
        ';

        $params = [];
        if (!is_null($editionId)) {
            $sql .= ' AND ie.edition_id = ?';
            $params[] = $editionId;
        }
        if (!is_null($status)) {
            $sql .= ' AND ie.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY e.year DESC, i.created_at DESC;';

        /** @var list<array{idea_id: int|string, edition_id: int|string|null, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|string|null, skill_level: string|null, status: string|null, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $result = [];
        foreach ($rows as $row) {
            $result[] = self::toPublicArray($row);
        }
        return $result;
    }

    /**
     * @return array{idea_id: int, edition_id: int, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|null, skill_level: string|null, status: string, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}|null
     */
    public static function getIdeaById(int $ideaId): ?array {
        $sql = '
            SELECT
                i.idea_id,
                ie.edition_id,
                i.title,
                i.brief_description,
                i.expected_results,
                i.preferred_skills,
                i.possible_mentors,
                i.estimated_hours,
                i.skill_level,
                ie.status,
                i.blog_link,
                i.contributor_username,
                i.created_at,
                i.updated_at
            FROM
                GSoC_Idea i
            LEFT JOIN
                GSoC_Idea_Edition ie ON ie.idea_id = i.idea_id
            LEFT JOIN
                GSoC_Edition e ON e.edition_id = ie.edition_id
            WHERE
                i.idea_id = ?
            ORDER BY
                e.year DESC
            LIMIT 1;
        ';

        /** @var array{idea_id: int|string, edition_id: int|string|null, title: string, brief_description: string|null, expected_results: string|null, preferred_skills: string|null, possible_mentors: string|null, estimated_hours: int|string|null, skill_level: string|null, status: string|null, blog_link: string|null, contributor_username: string|null, created_at: string, updated_at: string}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$ideaId]);
        if (empty($row)) {
            return null;
        }
        return self::toPublicArray($row);
    }

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
        $idea = new \OmegaUp\DAO\VO\GSoCIdea([
            'title' => $title,
            'brief_description' => $briefDescription,
            'expected_results' => $expectedResults,
            'preferred_skills' => $preferredSkills,
            'possible_mentors' => $possibleMentors,
            'estimated_hours' => $estimatedHours,
            'skill_level' => $skillLevel,
            'blog_link' => $blogLink,
            'contributor_username' => $contributorUsername,
        ]);
        self::create($idea);

        $ideaEdition = new \OmegaUp\DAO\VO\GSoCIdeaEdition([
            'idea_id' => $idea->idea_id,
            'edition_id' => $editionId,
            'status' => $status,
        ]);
        \OmegaUp\DAO\GSoCIdeaEdition::create($ideaEdition);

        return intval($idea->idea_id);
    }

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
        $idea = self::getByPK($ideaId);
        if (is_null($idea)) {
            return 0;
        }
        if (!is_null($title)) {
            $idea->title = $title;
        }
        if (!is_null($briefDescription)) {
            $idea->brief_description = $briefDescription;
        }
        if (!is_null($expectedResults)) {
            $idea->expected_results = $expectedResults;
        }
        if (!is_null($preferredSkills)) {
            $idea->preferred_skills = $preferredSkills;
        }
        if (!is_null($possibleMentors)) {
            $idea->possible_mentors = $possibleMentors;
        }
        if (!is_null($estimatedHours)) {
            $idea->estimated_hours = $estimatedHours;
        }
        if (!is_null($skillLevel)) {
            $idea->skill_level = $skillLevel;
        }
        if (!is_null($blogLink)) {
            $idea->blog_link = $blogLink;
        }
        if (!is_null($contributorUsername)) {
            $idea->contributor_username = $contributorUsername;
        }
        $affectedRows = self::update($idea);

        if (!is_null($editionId) || !is_null($status)) {
            $targetEditionId = $editionId;
            if (is_null($targetEditionId)) {
                $existingIdea = self::getIdeaById($ideaId);
                $targetEditionId = is_null($existingIdea) ? null : intval($existingIdea['edition_id']);
            }
            if (!is_null($targetEditionId) && $targetEditionId > 0) {
                $ideaEdition = \OmegaUp\DAO\GSoCIdeaEdition::getByIdeaAndEdition(
                    $ideaId,
                    $targetEditionId
                );
                if (is_null($ideaEdition)) {
                    $ideaEdition = new \OmegaUp\DAO\VO\GSoCIdeaEdition([
                        'idea_id' => $ideaId,
                        'edition_id' => $targetEditionId,
                        'status' => is_null($status) ? 'Proposed' : $status,
                    ]);
                    $affectedRows += \OmegaUp\DAO\GSoCIdeaEdition::create($ideaEdition);
                } elseif (!is_null($status)) {
                    $ideaEdition->status = $status;
                    $affectedRows += \OmegaUp\DAO\GSoCIdeaEdition::update($ideaEdition);
                }
            }
        }
        return $affectedRows;
    }
}
