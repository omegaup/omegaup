<?php

namespace OmegaUp\Controllers;

/**
 * SubmissionController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type Submission=array{time: \OmegaUp\Timestamp, username: string, school_id: int|null, school_name: string|null, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}
 * @psalm-type SubmissionsListPayload=array{includeUser: bool, username?: string, submissions: list<Submission>}
 */
class Submission extends \OmegaUp\Controllers\Controller {
    const SUBMISSION_LIST_PAGE_SIZE_DEFAULT = 100;
    const MAX_SUBMISSION_LIST_PAGE_SIZE = 500;
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
        ) ?? self::SUBMISSION_LIST_PAGE_SIZE_DEFAULT ;
        /** @psalm-suppress RedundantCondition Function checking if $pageSize is numeric */
        \OmegaUp\Validators::validateNumberInRange(
            $pageSize,
            'pageSize',
            null,
            self::MAX_SUBMISSION_LIST_PAGE_SIZE
        );
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
        ) ?? self::SUBMISSION_LIST_PAGE_SIZE_DEFAULT;
        /** @psalm-suppress RedundantCondition Function checking if $pageSize is numeric */
        \OmegaUp\Validators::validateNumberInRange(
            $pageSize,
            'pageSize',
            null,
            self::MAX_SUBMISSION_LIST_PAGE_SIZE
        );
        $identity = \OmegaUp\DAO\Identities::FindByUsername($username);
        if ($identity === null) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        if (
            $user !== null &&
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
                    'username' => $username,
                    'submissions' => \OmegaUp\DAO\Submissions::getLatestSubmissions(
                        identityId: $identity->identity_id,
                        page: $page,
                        rowsPerPage: $pageSize,
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
     * Returns a list of submissions in the last 24 hours
     * for given page and username.
     * @param \OmegaUp\Request $r
     *
     * @return array{submissions: list<Submission>}
     *
     * @omegaup-request-param string|null $username
     * @omegaup-request-param int|null $page
     * @omegaup-request-param int|null $pageSize
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $username = $r->ensureOptionalString('username');
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt(
            'pageSize'
        ) ?? self::SUBMISSION_LIST_PAGE_SIZE_DEFAULT;
        /** @psalm-suppress RedundantCondition Function checking if $pageSize is  numeric */
        \OmegaUp\Validators::validateNumberInRange(
            $pageSize,
            'pageSize',
            null,
            self::MAX_SUBMISSION_LIST_PAGE_SIZE
        );
        if ($username === null) {
            return [
                'submissions' =>  \OmegaUp\DAO\Submissions::getLatestSubmissions(
                    page: $page,
                    rowsPerPage: $pageSize,
                ),
            ];
        }
        $identity = \OmegaUp\DAO\Identities::FindByUsername($username);
        if ($identity === null) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        if (
            $user !== null &&
            ($user->main_identity_id == $identity->identity_id) &&
            $user->is_private
        ) {
            // Only the user's main identity is private.
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userInformationIsPrivate'
            );
        }
        return [
            'submissions' =>  \OmegaUp\DAO\Submissions::getLatestSubmissions(
                identityId: $identity->identity_id,
                page: $page,
                rowsPerPage: $pageSize,
            ),
        ];
    }

    /**
     * Creates the feedback thread for a submission
     *
     * @param array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     */
    private static function createFeedbackThread(
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        int $submissionFeedbackId,
        string $feedback,
        \OmegaUp\DAO\VO\Courses $course
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

        return $submissionFeedbackThread;
    }

    /**
     * Creates or updates the feedback for a submission, avoiding duplicating feedbacks
     *
     * @param array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     */
    public static function createOrUpdateFeedback(
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        \OmegaUp\DAO\VO\Submissions $submission,
        string $feedback,
        ?int $rangeBytesStart = null,
        ?int $rangeBytesEnd = null
    ): \OmegaUp\DAO\VO\SubmissionFeedback {
        if ($submission->guid === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
            );
        }
        $submissionFeedback = \OmegaUp\DAO\SubmissionFeedback::getFeedbackBySubmission(
            $submission->guid,
            $rangeBytesStart
        );
        if ($submissionFeedback !== null) {
            // Edit the feedback for specific range bytes is not supported yet
            // so we just return the existing submission feedback
            if ($rangeBytesStart !== null) {
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
        string $guid,
        ?string $authorUsername = null,
        string $notificationType = \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK
    ): void {
        $notificationTranslationString = new \OmegaUp\TranslationString(
            'notificationCourseSubmissionFeedback'
        );
        if ($notificationType === \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK_THREAD) {
            $notificationTranslationString = new \OmegaUp\TranslationString(
                'notificationCourseSubmissionFeedbackThread'
            );
        }
        \OmegaUp\Controllers\Notification::setCommonNotification(
            [$authorUserId],
            $notificationTranslationString,
            $notificationType,
            "/course/{$courseAlias}/assignment/{$assignmentAlias}/#problems/{$problemAlias}/show-run:{$guid}",
            [
                'problemAlias' => $problemAlias,
                'courseName' => $courseName,
                'username' => $authorUsername,
            ]
        );
    }

    /**
     * Updates the admin feedback for a submission or creates the request feedback,
     * also it creates a notification
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
        if ($submission === null || $submission->guid === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
            );
        }

        $feedback = $r->ensureString(
            'feedback',
            fn (string $feedback) => \OmegaUp\Validators::stringOfLengthInRange(
                $feedback,
                1,
                1000
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
        if ($courseSubmissionInfo === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseSubmissionNotFound'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseSubmissionInfo['course_id']
        );
        if ($course === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        $submissionFeedbackId = $r->ensureOptionalInt('submission_feedback_id');
        $submissionFeedback = null;
        if ($submissionFeedbackId !== null) {
            $submissionFeedbackThread = self::createFeedbackThread(
                $r->identity,
                $submissionFeedbackId,
                $feedback,
                $course
            );
            self::sendNotificationForThreadParticipants(
                $submissionFeedbackId,
                $r->identity,
                $course,
                $courseSubmissionInfo,
                $guid
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
            $rangeBytesStart !== null && $rangeBytesEnd !== null && ($rangeBytesStart < 0
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
            $feedback,
            $rangeBytesStart,
            $rangeBytesEnd
        );

        if ($courseSubmissionInfo['author_id'] !== null) {
            self::createNotificationForFeedback(
                $courseSubmissionInfo['author_id'],
                $courseSubmissionInfo['problem_alias'],
                $course->name,
                $course->alias,
                $courseSubmissionInfo['assignment_alias'],
                $submission->guid
            );
        }

        return [
            'status' => 'ok',
            'submissionFeedback' => $submissionFeedback,
            'submissionFeedbackThread' => null,
        ];
    }

    /**
     * Sends a notification to all the participants of a thread
     *
     * @param \OmegaUp\DAO\VO\SubmissionFeedbackThread $submissionFeedbackThread
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param array{assignment_alias: string, course_alias: string, course_id: int, problem_alias: string} $courseSubmissionInfo
     * @param string $guid
     */
    private static function sendNotificationForThreadParticipants(
        int $submissionFeedbackId,
        \OmegaUp\DAO\VO\Identities $feedbackAuthor,
        \OmegaUp\DAO\VO\Courses $course,
        array $courseSubmissionInfo,
        string $guid
    ): void {
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
                $guid,
                $feedbackAuthor->username,
                \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK_THREAD
            );
        }
    }

    /**
     * Updates the admin feedback for a submission or creates the request feedback,
     * also it creates a notification
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $feedback_list
     * @omegaup-request-param string $guid
     *
     * @return array{status: string}
     */
    public static function apiSetFeedbackList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $guid = $r->ensureString('guid');

        $submission = \OmegaUp\DAO\Submissions::getByGuid($guid);
        if ($submission === null || $submission->guid === null) {
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
        if ($courseSubmissionInfo === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseSubmissionNotFound'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseSubmissionInfo['course_id']
        );
        if ($course === null) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
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

        $feedbackString = $r->ensureString('feedback_list');
        /** @var list<array{lineNumber: int, feedback: string}> */
        $feedbackList = json_decode($feedbackString, associative: true);

        foreach ($feedbackList as $feedback) {
            self::createOrUpdateFeedback(
                $r->identity,
                $submission,
                $feedback['feedback'],
                $feedback['lineNumber']
            );
        }

        if ($courseSubmissionInfo['author_id'] !== null) {
            self::createNotificationForFeedback(
                $courseSubmissionInfo['author_id'],
                $courseSubmissionInfo['problem_alias'],
                $course->name,
                $course->alias,
                $courseSubmissionInfo['assignment_alias'],
                $submission->guid
            );
        }

        return [
            'status' => 'ok',
        ];
    }
}
