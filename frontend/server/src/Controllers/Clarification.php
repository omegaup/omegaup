<?php

 namespace OmegaUp\Controllers;

/**
 * Description of ClarificationController
 *
 * @author joemmanuel
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
     * Creates a Clarification
     *
     * @return array{clarification_id: int}
     *
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $message
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param null|string $username
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        // Validate request
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['message'],
            'message',
            1,
            200
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $identity = !is_null($r['username']) ?
            \OmegaUp\DAO\Identities::findByUsername($r['username']) : null;

        // Is the combination problemset_id and problem_id valid?
        if (
            is_null(
                \OmegaUp\DAO\ProblemsetProblems::getByPK(
                    $contest->problemset_id,
                    $problem->problem_id
                )
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFoundInContest'
            );
        }

        $receiverId = $identity ? $identity->identity_id : null;
        $clarification = new \OmegaUp\DAO\VO\Clarifications([
            'author_id' => $r->identity->identity_id,
            'receiver_id' => $receiverId,
            'problemset_id' => $contest->problemset_id,
            'problem_id' => $problem->problem_id,
            'message' => $r['message'],
            'time' => \OmegaUp\Time::get(),
            'public' => $receiverId == $r->identity->identity_id,
        ]);

        \OmegaUp\DAO\Clarifications::create($clarification);
        self::clarificationUpdated(
            $r,
            $clarification,
            $r->identity,
            $problem,
            $contest
        );

        return [
            'clarification_id' => intval($clarification->clarification_id),
        ];
    }

    /**
     * API for getting a clarification
     *
     * @return array{message: string, answer: null|string, time: int, problem_id: int, problemset_id: int|null}
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

        /** @var array{message: string, answer: null|string, time: int, problem_id: int, problemset_id: int|null} */
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

        // Validate request
        $r->ensureInt('clarification_id');
        $r->ensureOptionalBool('public');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['answer'],
            'answer'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['message'],
            'message'
        );

        // Check that clarification exists
        $clarification = \OmegaUp\DAO\Clarifications::GetByPK(
            intval($r['clarification_id'])
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
        $valueProperties = [
            'message',
            'answer',
            'public',
        ];
        self::updateValueProperties($r, $clarification, $valueProperties);

        // Save the clarification
        $clarification->time = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
        \OmegaUp\DAO\Clarifications::update($clarification);

        self::clarificationUpdated($r, $clarification, null, null, null);

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
