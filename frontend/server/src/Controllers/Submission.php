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
     * Creates the feedback thread for a submission and its corresponding
     * notification, avoiding duplicating feedbacks
     *
     * @param array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     */
    private static function createFeedbackThread(
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        int $submissionFeedbackId,
        string $feedback,
        string $guid,
        \OmegaUp\DAO\VO\Courses $course,
        $courseSubmissionInfo
    ): void {
        $group = \OmegaUp\Controllers\Course::resolveGroup($course);
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $feedbackAuthor,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        \OmegaUp\DAO\Base\SubmissionFeedbackThread::create(
            new \OmegaUp\DAO\VO\SubmissionFeedbackThread([
                'identity_id' => $feedbackAuthor->identity_id,
                'submission_feedback_id' => $submissionFeedbackId,
                'contents' => $feedback,
                'date' => gmdate('Y-m-d H:i:s'),
            ])
        );

        $participants = \OmegaUp\DAO\SubmissionFeedbackThread::getSubmissionFeedbackThreadParticipants(
            $submissionFeedbackId
        );

        if (empty($participants)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionFeedbackParticipantsListEmpty'
            );
        }
        $participantsNotifications = array_filter(
            $participants,
            fn ($author) => $author['author_id'] != $feedbackAuthor->user_id
        );

        $courseAlias = $course->alias;
        $assignmentAlias = $courseSubmissionInfo['assignment_alias'];
        $problemAlias = $courseSubmissionInfo['problem_alias'];
        foreach ($participantsNotifications as $participant) {
            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $participant['author_id'],
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
                            'url' => "/course/{$courseAlias}/assignment/{$assignmentAlias}/#problems/{$problemAlias}/show-run:{$guid}",
                            'iconUrl' => '/media/info.png',
                        ]
                    ]),
                ])
            );
        }
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
        ?int $rangeBytesStart,
        ?int $rangeBytesEnd,
        string $feedback,
        $courseSubmissionInfo
    ): void {
        \OmegaUp\DAO\Base\SubmissionFeedback::create(
            new \OmegaUp\DAO\VO\SubmissionFeedback([
                'identity_id' => $feedbackAuthor->identity_id,
                'submission_id' => $submission->submission_id,
                'range_bytes_start' => $rangeBytesStart,
                'range_bytes_end' => $rangeBytesEnd,
                'feedback' => $feedback,
            ])
        );

        if (!is_null($courseSubmissionInfo['author_id'])) {
            $courseAlias = $course->alias;
            $assignmentAlias = $courseSubmissionInfo['assignment_alias'];
            $problemAlias = $courseSubmissionInfo['problem_alias'];
            $guid = $submission->guid;
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
                            'url' => "/course/{$courseAlias}/assignment/{$assignmentAlias}/#problems/{$problemAlias}/show-run:{$guid}",
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
     * @omegaup-request-param int|null $submission_feedback_id
     *
     * @return array{status: string}
     */
    public static function apiSetFeedback(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $guid = $r->ensureString('guid');

        $submission = \OmegaUp\DAO\Submissions::getByGuid($guid);
        if (is_null($submission) || is_null($submission->guid)) {
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

        $submissionFeedbackId = $r->ensureOptionalInt('submission_feedback_id');
        if (!is_null($submissionFeedbackId)) {
            self::createFeedbackThread(
                $r->identity,
                $submissionFeedbackId,
                $feedback,
                $guid,
                $course,
                $courseSubmissionInfo
            );
            return [
                'status' => 'ok',
            ];
        }

        // Default values for a general feedback
        $rangeBytesStart = $r->ensureOptionalInt('range_bytes_start');
        $rangeBytesEnd = $r->ensureOptionalInt('range_bytes_end');

        if (
            !is_null(
                $rangeBytesStart
            ) && !is_null(
                $rangeBytesEnd
            ) && ($rangeBytesStart < 0
            ||  $rangeBytesEnd < $rangeBytesStart)
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
                $submission->guid,
                $rangeBytesStart
            );

            if (is_null($submissionFeedback)) {
                self::createFeedback(
                    $r->identity,
                    $submission,
                    $course,
                    $rangeBytesStart,
                    $rangeBytesEnd,
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
