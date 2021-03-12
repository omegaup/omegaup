<?php

namespace OmegaUp\Controllers;

/**
 * SubmissionFeedbackController
 *
 * @author carlosabcs
 */
class SubmissionFeedback extends \OmegaUp\Controllers\Controller {
    /**
     * Adds admin feedback to a submission
     *
     * @omegaup-request-param int $submission_id
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param string $assignment_alias
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $feedback
     *
     * @return array{status: string}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $submission = \OmegaUp\DAO\Submissions::getByPK(
            $r->ensureInt('submission_id')
        );
        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'submissionNotFound'
            );
        }

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $feedback = $r->ensureString(
            'feedback',
            fn (string $feedback) => \OmegaUp\Validators::stringOfLengthInRange(
                $feedback,
                1,
                200
            )
        );

        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
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

        $assignmentAlias = $r->ensureString(
            'assignment_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            intval($course->course_id)
        );
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if (
            is_null(
                \OmegaUp\DAO\ProblemsetProblems::getByPK(
                    intval($assignment->problemset_id),
                    $problem->problem_id
                )
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFoundInProblemset'
            );
        }

        if (
            $submission->problem_id !== $problem->problem_id ||
            $submission->problemset_id !== $assignment->problemset_id
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'submissionNotAssociatedToProblem'
            );
        }

        // Verificar si ya existe un feedback para esto.
        $submissionFeedback = \OmegaUp\DAO\SubmissionFeedback::getBySubmission(
            $submission
        );
        if (!is_null($submissionFeedback)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'submissionFeedbackAlreadyExists'
            );
        }

        $submissionAuthor = \OmegaUp\DAO\Identities::getByPK(
            intval(
                $submission->identity_id
            )
        );
        if (is_null($submissionAuthor)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        \OmegaUp\DAO\SubmissionFeedback::create(
            new \OmegaUp\DAO\VO\SubmissionFeedback([
                'identity_id' => $r->identity->identity_id,
                'submission_id' => $submission->submission_id,
                'feedback' => $feedback,
            ])
        );

        if ($submissionAuthor->user_id) {
            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $submissionAuthor->user_id,
                    'contents' =>  json_encode([
                        'type' => \OmegaUp\DAO\Notifications::COURSE_SUBMISSION_FEEDBACK,
                        'body' => [
                            'localizationString' => new \OmegaUp\TranslationString(
                                'notificationCourseSubmissionFeedback'
                            ),
                            'localizationParams' => [
                                'problemAlias' => $problem->alias,
                                'courseName' => $course->name,
                            ],
                            'url' => "/course/{$course->alias}/assignment/{$assignmentAlias}/#problems/{$problem->alias}/",
                            'iconUrl' => '/media/info.png',
                        ]
                    ]),
                ])
            );
        }

        return [
            'status' => 'ok',
        ];
    }
}
