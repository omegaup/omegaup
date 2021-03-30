<?php

 namespace OmegaUp\Controllers;

/**
 * Description of ClarificationController
 *
 * @author joemmanuel
 *
 * @psalm-type Clarification=array{answer: null|string, assignment_alias?: string, author: null|string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, time: \OmegaUp\Timestamp}
 */
class Clarification extends \OmegaUp\Controllers\Controller {
    /** @var null|\OmegaUp\Broadcaster */
    public static $broadcaster = null;

    /**
     * Creates an instance of Broadcaster if not already created
     */
    private static function getBroadcasterInstance(): \OmegaUp\Broadcaster {
        if (is_null(self::$broadcaster)) {
            // Create new grader
            self::$broadcaster = new \OmegaUp\Broadcaster();
        }
        return self::$broadcaster;
    }

    /**
     * Creates a Clarification for a contest or an assignment of a course
     *
     * @return Clarification
     *
     * @omegaup-request-param string|null $contest_alias
     * @omegaup-request-param string|null $course_alias
     * @omegaup-request-param string|null $assignment_alias
     * @omegaup-request-param null|string $username
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param string $message
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);

        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $message = $r->ensureString(
            'message',
            fn (string $message) => \OmegaUp\Validators::stringOfLengthInRange(
                $message,
                1,
                200
            )
        );

        $username = $r->ensureOptionalString('username');

        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            false,
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $contest = null;
        $problemsetId = null;
        $admins = [];
        /** @var array{type: string, body: array{localizationString: \OmegaUp\TranslationString, localizationParams: array{problemAlias: string, contestAlias?: string, courseAlias?: string}, url: string, iconUrl: string}}*/
        $notificationContents = [];
        if (is_null($contestAlias)) {
            // Clarification for course assignment
            $courseAlias = $r->ensureString(
                'course_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );

            $assignmentAlias = $r->ensureString(
                'assignment_alias',
                fn (string $alias) => \OmegaUp\Validators::alias($alias)
            );

            $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);

            if (is_null($course)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseNotFound'
                );
            }

            if (
                is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
                    $course->group_id,
                    $r->identity->identity_id
                ))
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseStudentNotInCourse'
                );
            }

            $problemset = \OmegaUp\DAO\Assignments::getProblemset(
                intval($course->course_id),
                $assignmentAlias
            );

            if (is_null($problemset)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }

            $problemsetId = $problemset->problemset_id;

            $admins = \OmegaUp\DAO\UserRoles::getCourseAdmins($course);

            $notificationContents = [
                'type' => \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_REQUEST,
                'body' => [
                    'localizationString' => new \OmegaUp\TranslationString(
                        'notificationCourseClarificationRequest'
                    ),
                    'localizationParams' => [
                        'problemAlias' => $problem->alias,
                        'courseName' => $course->name,
                    ],
                    'url' => "/course/{$course->alias}/assignment/{$assignmentAlias}/#problems/{$problem->alias}/",
                    'iconUrl' => '/media/info.png',
                ],
            ];
        } else {
            // Clarification for contest
            $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
            if (is_null($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }

            $problemsetId = $contest->problemset_id;

            $admins = \OmegaUp\DAO\UserRoles::getContestAdmins($contest);

            $notificationContents = [
                'type' => \OmegaUp\DAO\Notifications::CONTEST_CLARIFICATION_REQUEST,
                'body' => [
                    'localizationString' => new \OmegaUp\TranslationString(
                        'notificationContestClarificationRequest'
                    ),
                    'localizationParams' => [
                        'problemAlias' => $problem->alias,
                        'contestAlias' => $contest->alias,
                    ],
                    'url' => "/arena/{$contest->alias}/#problems/{$problem->alias}/",
                    'iconUrl' => '/media/info.png',
                ],
            ];
        }

        // Is the combination problemset_id and problem_id valid?
        if (
            is_null(
                \OmegaUp\DAO\ProblemsetProblems::getByPK(
                    $problemsetId,
                    $problem->problem_id
                )
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFoundInProblemset'
            );
        }

        $receiver = !is_null($username) ?
        \OmegaUp\DAO\Identities::findByUsername($username) : null;
        $receiverId = $receiver ? $receiver->identity_id : null;

        $clarification = new \OmegaUp\DAO\VO\Clarifications([
            'author_id' => $r->identity->identity_id,
            'receiver_id' => $receiverId,
            'problemset_id' => $problemsetId,
            'problem_id' => $problem->problem_id,
            'message' => $message,
            'time' => \OmegaUp\Time::get(),
            'public' => $receiverId === $r->identity->identity_id,
        ]);

        \OmegaUp\DAO\Clarifications::create($clarification);

        if (!is_null($contest)) {
            self::clarificationUpdated(
                $r,
                $clarification,
                $r->identity,
                $problem,
                $contest
            );
        }

        /** @var array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string} */
        foreach ($admins as $admin) {
            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $admin['user_id'],
                    'contents' =>  json_encode($notificationContents),
                ])
            );
        }

        return [
            'answer' => $clarification->answer,
            'author' => $r->identity->username,
            'clarification_id' => intval($clarification->clarification_id),
            'contest_alias' => null,
            'message' => strval($clarification->message),
            'problem_alias' => strval($problem->alias),
            'public' => $clarification->public,
            'receiver' => $receiver ? $receiver->username : null,
            'time' => $clarification->time,
        ];
    }

    /**
     * API for getting a clarification
     *
     * @return array{message: string, answer: null|string, time: \OmegaUp\Timestamp, problem_id: int, problemset_id: int|null}
     *
     * @omegaup-request-param int $clarification_id
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        // Authenticate the user
        $r->ensureIdentity();

        // Validate request
        $r->ensureInt('clarification_id');

        // Check that the clarification actually exists
        $clarification = \OmegaUp\DAO\Clarifications::getByPK(
            intval($r['clarification_id'])
        );
        if (is_null($clarification)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'clarificationNotFound'
            );
        }

        // If the clarification is private, verify that our user is invited or is contest director
        if ($clarification->public != 1) {
            if (
                !\OmegaUp\Authorization::canViewClarification(
                    $r->identity,
                    $clarification
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
        }

        /** @var array{message: string, answer: null|string, time: \OmegaUp\Timestamp, problem_id: int, problemset_id: int|null} */
        return $clarification->asFilteredArray([
            'message',
            'answer',
            'time',
            'problem_id',
            'problemset_id',
        ]);
    }

    /**
     * Update a clarification
     *
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $answer
     * @omegaup-request-param int $clarification_id
     * @omegaup-request-param null|string $message
     * @omegaup-request-param bool|null $public
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        $public = $r->ensureOptionalBool('public');
        $answer = $r->ensureOptionalString(
            'answer',
            /*$required=*/false,
            fn (string $answer) => !empty($answer)
        );
        $message = $r->ensureOptionalString(
            'message',
            /*$required=*/false,
            fn (string $message) => !empty($message)
        );

        // Check that clarification exists
        $clarification = \OmegaUp\DAO\Clarifications::GetByPK(
            $r->ensureInt('clarification_id')
        );
        if (is_null($clarification)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'clarificationNotFound'
            );
        }

        if (
            !\OmegaUp\Authorization::canEditClarification(
                $r->identity,
                $clarification
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Update clarification
        if (!is_null($public)) {
            $clarification->public = $public;
        }

        if (!is_null($message)) {
            $clarification->message = $message;
        }

        if (!is_null($answer)) {
            $clarification->answer = $answer;
        }

        $author = \OmegaUp\DAO\Identities::getByPK(
            intval(
                $clarification->author_id
            )
        );
        if (is_null($author)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        $problem = \OmegaUp\DAO\Problems::GetByPK(
            intval($clarification->problem_id)
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        $contest = \OmegaUp\DAO\Contests::getByProblemset(
            intval($clarification->problemset_id)
        );
        $assignment = null;
        $course = null;
        if (is_null($contest)) {
            $assignment = \OmegaUp\DAO\Assignments::getByProblemset(
                intval($clarification->problemset_id)
            );
            if (is_null($assignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'assignmentNotFound'
                );
            }
            $course = \OmegaUp\DAO\Courses::getByPK(
                intval($assignment->course_id)
            );
            if (is_null($course)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseNotFound'
                );
            }
        }

        // Save the clarification
        $clarification->time = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        \OmegaUp\DAO\Clarifications::update($clarification);

        // Send notification to author
        if (!is_null($author->user_id) && !is_null($answer)) {
            if ($contest) {
                \OmegaUp\DAO\Notifications::create(
                    new \OmegaUp\DAO\VO\Notifications([
                        'user_id' => $author->user_id,
                        'contents' =>  json_encode([
                            'type' => \OmegaUp\DAO\Notifications::CONTEST_CLARIFICATION_RESPONSE,
                            'body' => [
                                'localizationString' => new \OmegaUp\TranslationString(
                                    'notificationContestClarificationResponse'
                                ),
                                'localizationParams' => [
                                    'problemAlias' => $problem->alias,
                                    'contestAlias' => $contest->alias,
                                ],
                                'url' => "/arena/{$contest->alias}/#problems/{$problem->alias}/",
                                'iconUrl' => '/media/info.png',
                            ],
                        ]),
                    ])
                );
            }
            if ($course && $assignment) {
                \OmegaUp\DAO\Notifications::create(
                    new \OmegaUp\DAO\VO\Notifications([
                        'user_id' => $author->user_id,
                        'contents' =>  json_encode([
                            'type' => \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_RESPONSE,
                            'body' => [
                                'localizationString' => new \OmegaUp\TranslationString(
                                    'notificationCourseClarificationResponse'
                                ),
                                'localizationParams' => [
                                    'problemAlias' => $problem->alias,
                                    'courseName' => $course->name,
                                ],
                                'url' => "/course/{$course->alias}/assignment/{$assignment->alias}/#problems/{$problem->alias}/",
                                'iconUrl' => '/media/info.png',
                            ],
                        ]),
                    ])
                );
            }
        }

        self::clarificationUpdated(
            $r,
            $clarification,
            $author,
            $problem,
            $contest
        );

        return [
            'status' => 'ok',
        ];
    }

    private static function clarificationUpdated(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Clarifications $clarification,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Problems $problem,
        ?\OmegaUp\DAO\VO\Contests $contest
    ): void {
        try {
            if (is_null($problem)) {
                $problem = \OmegaUp\DAO\Problems::GetByPK(
                    intval($clarification->problem_id)
                );
                if (is_null($problem)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemNotFound'
                    );
                }
            }
            if (
                is_null($contest) &&
                !is_null($clarification->problemset_id)
            ) {
                $contest = \OmegaUp\DAO\Contests::getByProblemset(
                    $clarification->problemset_id
                );
            }
            if (is_null($identity)) {
                $identity = \OmegaUp\DAO\Identities::GetByPK(
                    intval($clarification->author_id)
                );
                if (is_null($identity)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'userNotExist'
                    );
                }
            }
        } catch (\Exception $e) {
            self::$log->error('Failed to broadcast clarification', $e);
            return;
        }
        self::getBroadcasterInstance()->broadcastClarification(
            $clarification,
            $problem,
            $identity,
            $contest
        );
    }
}
