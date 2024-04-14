<?php

namespace OmegaUp\Controllers;

/**
 * SubmissionController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type Submission=array{alias: string, classname: string, guid: string, language: string, memory: int, runtime: int, school_id: int|null, school_name: null|string, time: \OmegaUp\Timestamp, title: string, username: string, verdict: string}
 * @psalm-type SubmissionsListPayload=array{includeUser: bool, page: int, submissions: list<Submission>}
 */
class Submission extends \OmegaUp\Controllers\Controller {
    const SUBMISSION_LIST_PAGE_SIZE = 100;
    public static function getSource(string $guid): string {
        return \OmegaUp\Grader::GetInstance()->getSource($guid);
    }

    /**
     * Gets the details for the latest submissions.
     *
     * @return array{templateProperties: array{payload: SubmissionsListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $pageSize
     */
    public static function getLatestSubmissionsForTypeScript(\OmegaUp\Request $r): array {
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            'pageSize'
        ) ?? self::SUBMISSION_LIST_PAGE_SIZE;
        return [
            'templateProperties' => [
                'payload' => [
                    'includeUser' => true,
                    'submissions' => \OmegaUp\DAO\Submissions::getLatestSubmissions(
                        page: $page,
                        rowsPerPage: $pageSize,
                    ),
                    'page' => $page,
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
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param string $username
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $pageSize
     *
     */
    public static function getLatestUserSubmissionsForTypeScript(\OmegaUp\Request $r): array {
        $username = $r->ensureString('username');
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            'pageSize'
        ) ?? self::SUBMISSION_LIST_PAGE_SIZE;

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
                        identityId: $identity->identity_id,
                        page: $page,
                        rowsPerPage: $pageSize,
                    ),
                    'page' => $page,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleLatestSubmissions'
                ),
            ],
            'entrypoint' => 'submissions_list',
        ];
    }

    /**
     * Returns a list of submissions created in the last 24 hours
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{results: list<Submission>}
     *
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $rowcount
     * @omegaup-request-param null|string $username
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $page = $r->ensureOptionalInt('page') ?? 1;
        $rowcount = $r->ensureOptionalInt(
            'rowcount'
        ) ?? self::SUBMIT_LIST_PAGE_SIZE;
        $username = $r->ensureOptionalString('username');
        if (is_null($username)) {
            return [
                'results' => \OmegaUp\DAO\Submissions::getLatestSubmissions(
                    identityId: null,
                    page: $page,
                    rowsPerPage: $rowcount,
                ),
            ];
        }
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
            'results' => \OmegaUp\DAO\Submissions::getLatestSubmissions(
                $identity->identity_id,
                $page,
                $rowcount,
            ),
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
    ): \OmegaUp\DAO\VO\SubmissionFeedbackThread {
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
        $submissionFeedbackThread = new \OmegaUp\DAO\VO\SubmissionFeedbackThread([
            'identity_id' => $feedbackAuthor->identity_id,
            'submission_feedback_id' => $submissionFeedbackId,
            'contents' => $feedback,
            'date' => gmdate('Y-m-d H:i:s'),
        ]);
        \OmegaUp\DAO\Base\SubmissionFeedbackThread::create(
            $submissionFeedbackThread
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
        $courseName = $course->name;
        $assignmentAlias = $courseSubmissionInfo['assignment_alias'];
        $problemAlias = $courseSubmissionInfo['problem_alias'];
        foreach ($participantsNotifications as $participant) {
            self::createNotificationForFeedback(
                $participant['author_id'],
                $problemAlias,
                $courseName,
                $courseAlias,
                $assignmentAlias,
                $guid
            );
        }

        return $submissionFeedbackThread;
    }

    /**
     * Creates or updates the feedback for a submission and its corresponding
     * notification when it is a new feedback, avoiding duplicating feedbacks
     *
     * @param array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     */
    public static function createOrUpdateFeedback(
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        \OmegaUp\DAO\VO\Submissions $submission,
        \OmegaUp\DAO\VO\Courses $course,
        array $courseSubmissionInfo,
        string $feedback,
        ?int $rangeBytesStart = null,
        ?int $rangeBytesEnd = null
    ): \OmegaUp\DAO\VO\SubmissionFeedback {
        if (is_null($submission->guid)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
            );
        }
        $submissionFeedback = \OmegaUp\DAO\SubmissionFeedback::getFeedbackBySubmission(
            $submission->guid,
            $rangeBytesStart
        );
        if (!is_null($submissionFeedback)) {
            // Edit the feedback for specific range bytes is not supported yet
            // so we just return the existing submission feedback
            if (!is_null($rangeBytesStart)) {
                return $submissionFeedback;
            }
            return self::updateFeedback(
                $submissionFeedback,
                $feedbackAuthor->identity_id,
                $feedback
            );
        }
        try {
            \OmegaUp\DAO\DAO::transBegin();

            $submissionFeedback = new \OmegaUp\DAO\VO\SubmissionFeedback([
                'identity_id' => $feedbackAuthor->identity_id,
                'submission_id' => $submission->submission_id,
                'range_bytes_start' => $rangeBytesStart,
                'range_bytes_end' => $rangeBytesEnd,
                'feedback' => $feedback,
            ]);
            \OmegaUp\DAO\Base\SubmissionFeedback::create(
                $submissionFeedback
            );

            if (!is_null($courseSubmissionInfo['author_id'])) {
                self::createNotificationForFeedback(
                    $courseSubmissionInfo['author_id'],
                    $courseSubmissionInfo['problem_alias'],
                    $course->name,
                    $course->alias,
                    $courseSubmissionInfo['assignment_alias'],
                    $submission->guid
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

        return $submissionFeedback;
    }

    private static function updateFeedback(
        \OmegaUp\DAO\VO\SubmissionFeedback $submissionFeedback,
        ?int $authorIdentityId,
        string $feedback
    ): \OmegaUp\DAO\VO\SubmissionFeedback {
        $submissionFeedback->identity_id = $authorIdentityId;
        $submissionFeedback->feedback = $feedback;
        \OmegaUp\DAO\Base\SubmissionFeedback::update(
            $submissionFeedback
        );
        return $submissionFeedback;
    }

    private static function createNotificationForFeedback(
        int $authorUserId,
        string $problemAlias,
        ?string $courseName,
        ?string $courseAlias,
        string $assignmentAlias,
        string $guid
    ): void {
        \OmegaUp\Controllers\Notification::setCommonNotification(
            [$authorUserId],
            new \OmegaUp\TranslationString(
                'notificationCourseSubmissionFeedback'
            ),
            \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
            "/course/{$courseAlias}/assignment/{$assignmentAlias}/#problems/{$problemAlias}/show-run:{$guid}",
            [
                'problemAlias' => $problemAlias,
                'courseName' => $courseName,
            ]
        );
    }

    /**
     * Updates the admin feedback for a submission or creates the request feedback
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $feedback
     * @omegaup-request-param string $guid
     * @omegaup-request-param int|null $range_bytes_end
     * @omegaup-request-param int|null $range_bytes_start
     * @omegaup-request-param int|null $submission_feedback_id
     *
     * @return array{status: string, submissionFeedback: null|\OmegaUp\DAO\VO\SubmissionFeedback, submissionFeedbackThread: null|\OmegaUp\DAO\VO\SubmissionFeedbackThread}
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
        $submissionFeedback = null;
        if (!is_null($submissionFeedbackId)) {
            $submissionFeedbackThread = self::createFeedbackThread(
                $r->identity,
                $submissionFeedbackId,
                $feedback,
                $guid,
                $course,
                $courseSubmissionInfo
            );
            $submissionFeedback = \OmegaUp\DAO\SubmissionFeedback::getByPK(
                $submissionFeedbackId
            );
            return [
                'status' => 'ok',
                'submissionFeedback' => $submissionFeedback,
                'submissionFeedbackThread' => $submissionFeedbackThread,
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

        self::createOrUpdateFeedback(
            $r->identity,
            $submission,
            $course,
            $courseSubmissionInfo,
            $feedback,
            $rangeBytesStart,
            $rangeBytesEnd
        );

        return [
            'status' => 'ok',
            'submissionFeedback' => $submissionFeedback,
            'submissionFeedbackThread' => null,
        ];
    }
}
