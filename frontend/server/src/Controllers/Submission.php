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
    public static function getLatestSubmissionsForTypeScript(\OmegaUp\Request $r): array {
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
    public static function getLatestUserSubmissionsForTypeScript(\OmegaUp\Request $r): array {
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

    /**
     * Adds admin feedback to a submission
     *
     * @omegaup-request-param string $guid
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $feedback
     *
     * @return array{status: string}
     */
    public static function apiCreateFeedback(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $r->ensureString('guid')
        );
        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
            );
        }

        $courseSubmissionInfo = \OmegaUp\DAO\Submissions::getCourseSubmissionInfo(
            $submission,
            $r->ensureString(
                'assignment_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            ),
            $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            )
        );
        if (is_null($courseSubmissionInfo)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseSubmissionNotFound'
            );
        }

        $feedback = $r->ensureString(
            'feedback',
            fn (string $feedback) => \OmegaUp\Validators::stringOfLengthInRange(
                $feedback,
                1,
                200
            )
        );

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseSubmissionInfo['course_alias']
        );
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Verificar si ya existe un feedback para esto.
        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\SubmissionFeedback::create(
                new \OmegaUp\DAO\VO\SubmissionFeedback([
                    'identity_id' => $r->identity->identity_id,
                    'submission_id' => $submission->submission_id,
                    'feedback' => $feedback,
                ])
            );

            if (!is_null($courseSubmissionInfo['author_id'])) {
                \OmegaUp\DAO\Notifications::create(
                    new \OmegaUp\DAO\VO\Notifications([
                        'user_id' => $courseSubmissionInfo['author_id'],
                        'contents' =>  json_encode([
                            'type' => \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
                            'body' => [
                                'localizationString' => new \OmegaUp\TranslationString(
                                    'notificationCourseSubmissionFeedback'
                                ),
                                'localizationParams' => [
                                    'problemAlias' => $courseSubmissionInfo['problem_alias'],
                                    'courseName' => $course->name,
                                ],
                                'url' => "/course/{$course->alias}/assignment/{$courseSubmissionInfo['assignment_alias']}/#problems/{$courseSubmissionInfo['problem_alias']}/",
                                'iconUrl' => '/media/info.png',
                            ]
                        ]),
                    ])
                );
            }
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'submissionFeedbackAlreadyExists'
                );
            }
        }

        return [
            'status' => 'ok',
        ];
    }
}
