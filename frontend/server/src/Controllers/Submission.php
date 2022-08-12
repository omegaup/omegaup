<?php

namespace OmegaUp\Controllers;

/**
 * SubmissionController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type Submission=array{time: \OmegaUp\Timestamp, username: string, school_id: int|null, school_name: string|null, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}
 * @psalm-type SubmissionsListPayload=array{includeUser: bool, submissions: list<Submission>}
 */
class Submission extends \OmegaUp\Controllers\Controller {
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }

    /**
     * Gets the details for the latest submissions.
     *
     * @return array{templateProperties: array{payload: SubmissionsListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getLatestSubmissionsForTypeScript(\OmegaUp\Request $r): array {
        return [
            'templateProperties' => [
                'payload' => [
                    'includeUser' => true,
                    'submissions' => \OmegaUp\DAO\Submissions::getLatestSubmissions(),
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
     * a certain user.
     *
     * @return array{templateProperties: array{payload: SubmissionsListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $username
     */
    public static function getLatestUserSubmissionsForTypeScript(\OmegaUp\Request $r): array {
        $username = $r->ensureString('username');

        $identity = \OmegaUp\DAO\Identities::FindByUsername($username);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $user = \OmegaUp\DAO\Users::FindByUsername($username);
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

        return [
            'templateProperties' => [
                'payload' => [
                    'includeUser' => false,
                    'submissions' => \OmegaUp\DAO\Submissions::getLatestSubmissions(
                        $identity->identity_id,
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
     * Creates the feedback for a submission and its corresponding
     * notification, avoiding duplicating feedbacks
     *
     * @param array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     */
    private static function createFeedback(
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        \OmegaUp\DAO\VO\Submissions $submission,
        \OmegaUp\DAO\VO\Courses $course,
        ?int $range_bytes_start,
        ?int $range_bytes_end,
        string $feedback,
        $courseSubmissionInfo
    ): void {
        \OmegaUp\DAO\Base\SubmissionFeedback::create(
            new \OmegaUp\DAO\VO\SubmissionFeedback([
                'identity_id' => $feedbackAuthor->identity_id,
                'submission_id' => $submission->submission_id,
                'range_bytes_start' => $range_bytes_start,
                'range_bytes_end' => $range_bytes_end,
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
    }

    /**
     * Updates the admin feedback for a submission
     *
     * @omegaup-request-param string $guid
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $feedback
     * @omegaup-request-param int|null $range_bytes_end
     * @omegaup-request-param int|null $range_bytes_start
     *
     * @return array{status: string}
     */
    public static function apiSetFeedback(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $r->ensureString('guid')
        );
        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
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

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseSubmissionInfo['course_id']
        );
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        $range_bytes_start = $r->ensureOptionalInt('range_bytes_start');
        $range_bytes_end = $r->ensureOptionalInt('range_bytes_end');

        if (
            !is_null(
                $range_bytes_start
            ) && !is_null(
                $range_bytes_end
            ) && ($range_bytes_start < 0
            ||  $range_bytes_end < $range_bytes_start)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            ) && !\OmegaUp\Authorization::isTeachingAssistant(
                $r->identity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        try {
            \OmegaUp\DAO\DAO::transBegin();

            $submissionFeedback = \OmegaUp\DAO\SubmissionFeedback::getFeedbackBySubmission(
                $submission
            );

            if (is_null($submissionFeedback)) {
                self::createFeedback(
                    $r->identity,
                    $submission,
                    $course,
                    $range_bytes_start,
                    $range_bytes_end,
                    $feedback,
                    $courseSubmissionInfo
                );
            } else {
                $submissionFeedback->identity_id = $r->identity->identity_id;
                $submissionFeedback->feedback = $feedback;
                \OmegaUp\DAO\Base\SubmissionFeedback::update(
                    $submissionFeedback
                );
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'submissionFeedbackAlreadyExists'
                );
            }

            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }
}
