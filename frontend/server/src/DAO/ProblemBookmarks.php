<?php

namespace OmegaUp\DAO;

/**
 * ProblemBookmarks Data Access Object (DAO).
 *
 * @psalm-type BookmarkProblem=array{alias: string, attempted: bool, contest_alias?: null|string, contest_title?: null|string, is_private?: bool, practice_url?: null|string, solved: bool, title: string}
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
    public static function getAllBookmarkedProblems(
        int $userIdentityId,
        ?\OmegaUp\DAO\VO\Identities $identity = null
    ): array {
        if (is_null($identity)) {
            $identity = \OmegaUp\DAO\Identities::getByPK($userIdentityId);
        }
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

        if (empty($resultRows)) {
            return [];
        }

        $problemIds = array_map(
            fn (array $row) => intval($row['problem_id']),
            $resultRows
        );

        $now = \OmegaUp\Time::get();
        $placeholders = implode(',', array_fill(0, count($problemIds), '?'));
        $contestQuery = '
            SELECT
                pp.problem_id,
                c.contest_id,
                c.problemset_id,
                c.alias AS contest_alias,
                c.title AS contest_title,
                c.finish_time,
                c.admission_mode,
                c.acl_id,
                EXISTS (
                    SELECT
                        1
                    FROM
                        Problemset_Identities psi
                    WHERE
                        psi.problemset_id = c.problemset_id
                        AND psi.identity_id = ?
                ) AS is_enrolled,
                EXISTS (
                    SELECT
                        1
                    FROM
                        Problemset_Identity_Request pir
                    WHERE
                        pir.problemset_id = c.problemset_id
                        AND pir.identity_id = ?
                        AND pir.accepted = 1
                ) AS is_registered,
                EXISTS (
                    SELECT
                        1
                    FROM
                        Submissions s
                    WHERE
                        s.problemset_id = c.problemset_id
                        AND s.identity_id = ?
                ) AS has_submitted
            FROM
                Contests c
            INNER JOIN
                Problemset_Problems pp ON c.problemset_id = pp.problemset_id
            WHERE
                pp.problem_id IN (' . $placeholders . ')
                AND c.finish_time <= FROM_UNIXTIME(?)
                AND c.archived = 0
            ORDER BY
                c.finish_time DESC;';
        $contestQueryParams = array_merge(
            [$userIdentityId, $userIdentityId, $userIdentityId],
            $problemIds,
            [$now]
        );
        /** @var list<array{acl_id: int, admission_mode: string, contest_alias: string, contest_id: int, contest_title: string, finish_time: \OmegaUp\Timestamp, has_submitted: int, is_enrolled: int, is_registered: int, problem_id: int, problemset_id: int}> */
        $contestRows = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $contestQuery,
            $contestQueryParams
        );

        $isSystemAdmin = (
            !is_null($identity) &&
            \OmegaUp\Authorization::isSystemAdmin($identity)
        );

        /** @var array<int, array{alias: string, title: string}> */
        $bestPracticeContestsByProblemId = [];
        /** @var array<int, bool> */
        $problemHasParticipatedContest = [];

        foreach ($contestRows as $cRow) {
            $problemId = intval($cRow['problem_id']);
            if (!empty($problemHasParticipatedContest[$problemId])) {
                continue;
            }

            $isEnrolled = boolval($cRow['is_enrolled']);
            $isRegistered = boolval($cRow['is_registered']);
            $hasSubmitted = boolval($cRow['has_submitted']);

            $isContestAdmin = false;
            if (!$isSystemAdmin && !is_null($identity)) {
                $isContestAdmin = \OmegaUp\Authorization::isAdmin(
                    $identity,
                    new \OmegaUp\DAO\VO\Contests([
                        'contest_id' => $cRow['contest_id'],
                        'acl_id' => $cRow['acl_id'],
                    ])
                );
            }

            $canAccess = $isSystemAdmin || $isContestAdmin;
            if (!$canAccess) {
                if ($cRow['admission_mode'] === 'public') {
                    $canAccess = true;
                } elseif ($cRow['admission_mode'] === 'registration') {
                    $canAccess = $isRegistered || $isEnrolled;
                } elseif ($cRow['admission_mode'] === 'private') {
                    $canAccess = $isEnrolled;
                }
            }

            if (!$canAccess) {
                continue;
            }

            $participated = (
                $hasSubmitted ||
                $isEnrolled ||
                $isRegistered ||
                $isContestAdmin
            );
            $contestAlias = strval($cRow['contest_alias']);
            $contestTitle = strval($cRow['contest_title']);

            if ($participated) {
                $bestPracticeContestsByProblemId[$problemId] = [
                    'alias' => $contestAlias,
                    'title' => $contestTitle,
                ];
                $problemHasParticipatedContest[$problemId] = true;
            } elseif (!isset($bestPracticeContestsByProblemId[$problemId])) {
                $bestPracticeContestsByProblemId[$problemId] = [
                    'alias' => $contestAlias,
                    'title' => $contestTitle,
                ];
            }
        }

        $bookmarkedProblems = [];
        foreach ($resultRows as $rowData) {
            $solved = boolval($rowData['solved']);
            $attempted = boolval($rowData['attempted']);
            $problemId = intval($rowData['problem_id']);
            unset($rowData['solved'], $rowData['attempted']);
            $problem = new \OmegaUp\DAO\VO\Problems($rowData);

            $isPublic = \OmegaUp\DAO\Problems::isVisible($problem);
            $canEditProblem = (
                !is_null($identity) &&
                \OmegaUp\Authorization::canEditProblem($identity, $problem)
            );
            $bestContest = $bestPracticeContestsByProblemId[$problemId] ?? null;

            if (!$isPublic && !$canEditProblem && is_null($bestContest)) {
                continue;
            }

            $practiceUrl = null;
            $contestAlias = null;
            $contestTitle = null;
            if (!is_null($bestContest)) {
                $contestAlias = $bestContest['alias'];
                $contestTitle = $bestContest['title'];
                $practiceUrl = (
                    '/arena/' .
                    $contestAlias .
                    '/practice/#problems/' .
                    strval($problem->alias)
                );
            }

            $bookmarkedProblems[] = [
                'alias' => strval($problem->alias),
                'attempted' => $attempted,
                'contest_alias' => $contestAlias,
                'contest_title' => $contestTitle,
                'is_private' => !$isPublic,
                'practice_url' => $practiceUrl,
                'solved' => $solved,
                'title' => strval($problem->title),
            ];
        }
        return $bookmarkedProblems;
    }
}
