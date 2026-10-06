<?php

namespace OmegaUp\DAO;

/**
 * ProblemBookmarks Data Access Object (DAO).
 *
 * @psalm-type BookmarkProblem=array{alias: string, attempted: bool, solved: bool, title: string}
 *
 * @access public
 * @package docs
 */
class ProblemBookmarks extends \OmegaUp\DAO\Base\ProblemBookmarks {
    /**
     * Get all bookmarked problems for a specific identity
     *
     * @return list<BookmarkProblem>
     */
    public static function getAllBookmarkedProblems(int $userIdentityId): array {
        $query = '
            SELECT
                ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Problems::FIELD_NAMES,
            'Problems'
        ) . ',
                EXISTS(
                    SELECT
                        1
                    FROM
                        Submissions s
                    WHERE
                        s.problem_id = Problems.problem_id
                        AND s.identity_id = pb.identity_id
                        AND s.type = "normal"
                        AND s.verdict = "AC"
                ) AS solved,
                EXISTS(
                    SELECT
                        1
                    FROM
                        Submissions s
                    WHERE
                        s.problem_id = Problems.problem_id
                        AND s.identity_id = pb.identity_id
                        AND s.type = "normal"
                ) AS attempted
            FROM
                Problems
            INNER JOIN
                Problem_Bookmarks pb
            ON
                Problems.problem_id = pb.problem_id
            WHERE
                pb.identity_id = ?
            ORDER BY
                pb.created_at DESC;';
        $queryParams = [$userIdentityId];
        /** @var list<array{accepted: int, acl_id: int, alias: string, allow_user_add_tags: bool, attempted: int, commit: string, creation_date: \OmegaUp\Timestamp, current_version: string, deprecated: bool, difficulty: float|null, difficulty_histogram: null|string, email_clarifications: bool, input_limit: int, languages: string, order: string, problem_id: int, quality: float|null, quality_histogram: null|string, quality_seal: bool, show_diff: string, solved: int, source: null|string, submissions: int, title: string, visibility: int, visits: int}> */
        $resultRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $queryParams
        );

        $bookmarkedProblems = [];
        foreach ($resultRows as $rowData) {
            $solved = boolval($rowData['solved']);
            $attempted = boolval($rowData['attempted']);
            unset($rowData['solved'], $rowData['attempted']);
            $problem = new \OmegaUp\DAO\VO\Problems($rowData);
            if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                continue;
            }
            $bookmarkedProblems[] = [
                'alias' => strval($problem->alias),
                'attempted' => $attempted,
                'solved' => $solved,
                'title' => strval($problem->title),
            ];
        }
        return $bookmarkedProblems;
    }
}
