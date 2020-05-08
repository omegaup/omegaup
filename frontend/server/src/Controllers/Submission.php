<?php

 namespace OmegaUp\Controllers;

/**
 * SubmissionController
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }

    /**
     * Returns the latest submissions
     *
     * @return array{submissions: list<array{time: \OmegaUp\Timestamp, username: string, school_id: int|null, school_name: string|null, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}>, totalRows: int}
     *
     * @omegaup-request-param int $offset
     * @omegaup-request-param int $rowcount
     * @omegaup-request-param mixed $username
     */
    public static function apiLatestSubmissions(\OmegaUp\Request $r) {
        $r->ensureOptionalInt('offset');
        $r->ensureOptionalInt('rowcount');

        $offset = is_null($r['offset']) ? 1 : intval($r['offset']);
        $rowCount = is_null($r['rowcount']) ? 100 : intval($r['rowcount']);

        $identityId = null;
        if (!is_null($r['username'])) {
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

            $identityId = $identity->identity_id;
        }

        return \OmegaUp\DAO\Submissions::getLatestSubmissions(
            $offset,
            $rowCount,
            $identityId
        );
    }

    /**
     * Gets the details for the latest submissions with pagination
     *
     * @return array{smartyProperties: array{submissionsPayload: array{page: int, length: int, includeUser: bool}}, template: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getLatestSubmissionsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        return [
            'smartyProperties' => [
                'submissionsPayload' => [
                    'page' => $page,
                    'length' => $length,
                    'includeUser' => true,
                ],
            ],
            'template' => 'submissions.list.tpl',
        ];
    }

    /**
     * Gets the details for the latest submissions of
     * a certain user with pagination
     *
     * @return array{smartyProperties: array{submissionsPayload: array{page: int, length: int, includeUser: bool}}, template: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     * @omegaup-request-param mixed $username
     */
    public static function getLatestUserSubmissionsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);

        return [
            'smartyProperties' => [
                'submissionsPayload' => [
                    'page' => $page,
                    'length' => $length,
                    'user' => $identity->username,
                    'includeUser' => false,
                ],
            ],
            'template' => 'submissions.user_list.tpl',
        ];
    }
}
