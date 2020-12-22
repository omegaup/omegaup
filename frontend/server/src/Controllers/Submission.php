<?php

namespace OmegaUp\Controllers;

/**
 * SubmissionController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type Submission=array{time: \OmegaUp\Timestamp, username: string, school_id: int|null, school_name: string|null, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}
 * @psalm-type SubmissionsListPayload=array{page: int, length: int, includeUser: bool, pagerItems: list<PageItem>, submissions: list<Submission>, totalRows: int}
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }

    /**
     * Gets the details for the latest submissions with pagination
     *
     * @return array{smartyProperties: array{payload: SubmissionsListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getLatestSubmissionsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        $latestSubmissions = \OmegaUp\DAO\Submissions::getLatestSubmissions(
            $page,
            $length,
            null
        );
        return [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'includeUser' => true,
                    'submissions' => $latestSubmissions['submissions'],
                    'totalRows' => $latestSubmissions['totalRows'],
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $latestSubmissions['totalRows'],
                        $length,
                        $page,
                        '/submissions/',
                        2,
                        []
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleLatestSubmissions'
                ),
            ],
            'entrypoint' => 'submissions_list',
        ];
    }

    /**
     * Gets the details for the latest submissions of
     * a certain user with pagination
     *
     * @return array{smartyProperties: array{payload: SubmissionsListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     * @omegaup-request-param mixed $username
     */
    public static function getLatestUserSubmissionsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        \OmegaUp\Validators::validateValidUsername(
            $r['username'],
            'username'
        );

        $identity = \OmegaUp\DAO\Identities::FindByUsername($r['username']);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        if (
            !is_null(
                $user
            ) &&
            ($user->main_identity_id == $identity->identity_id) &&
            $user->is_private
        ) {
            // Only the user's main identity is private.
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userInformationIsPrivate'
            );
        }

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        $latestSubmissions = \OmegaUp\DAO\Submissions::getLatestSubmissions(
            $page,
            $length,
            $identity->identity_id
        );
        return [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'includeUser' => false,
                    'submissions' => $latestSubmissions['submissions'],
                    'totalRows' => $latestSubmissions['totalRows'],
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $latestSubmissions['totalRows'],
                        $length,
                        $page,
                        "/submissions/{$identity->username}/",
                        2,
                        []
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleLatestSubmissions'
                ),
            ],
            'entrypoint' => 'submissions_list',
        ];
    }
}
