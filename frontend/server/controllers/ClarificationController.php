<?php

/**
 * Description of ClarificationController
 *
 * @author joemmanuel
 */
class ClarificationController extends \OmegaUp\Controllers\Controller {
    /** @var null|\OmegaUp\Broadcaster */
    public static $broadcaster = null;

    /**
     * Creates an instance of Broadcaster if not already created
     */
    private static function initializeBroadcaster() : void {
        if (is_null(self::$broadcaster)) {
            // Create new grader
            self::$broadcaster = new \OmegaUp\Broadcaster();
        }
    }

    /**
     * Validate the request of apiCreate
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCreate(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['username'], 'username');
        \OmegaUp\Validators::validateStringOfLengthInRange($r['message'], 'message', 1, 200);

        $r['contest'] = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        $r['identity'] = !is_null($r['username']) ?
            \OmegaUp\DAO\Identities::findByUsername($r['username']) : null;

        if (is_null($r['contest'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (is_null($r['problem'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Is the combination problemset_id and problem_id valid?
        if (is_null(\OmegaUp\DAO\ProblemsetProblems::getByPK($r['contest']->problemset_id, $r['problem']->problem_id))) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFoundInContest');
        }
    }

    /**
     * Creates a Clarification
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        // Validate request
        self::validateCreate($r);

        $receiverId = $r['identity'] ? $r['identity']->identity_id : null;
        $r['clarification'] = new \OmegaUp\DAO\VO\Clarifications([
            'author_id' => $r->identity->identity_id,
            'receiver_id' => $receiverId,
            'problemset_id' => $r['contest']->problemset_id,
            'problem_id' => $r['problem']->problem_id,
            'message' => $r['message'],
            'time' => \OmegaUp\Time::get(),
            'public' => $receiverId == $r->identity->identity_id ? '1' : '0',
        ]);

        \OmegaUp\DAO\Clarifications::create($r['clarification']);
        self::clarificationUpdated($r, $r['clarification']);

        return [
            'status' => 'ok',
            'clarification_id' => $r['clarification']->clarification_id,
        ];
    }

    /**
     * Validate Details API request
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateDetails(\OmegaUp\Request $r) {
        $r->ensureInt('clarification_id');

        // Check that the clarification actually exists
        $r['clarification'] = \OmegaUp\DAO\Clarifications::getByPK($r['clarification_id']);
        if (is_null($r['clarification'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('clarificationNotFound');
        }

        // If the clarification is private, verify that our user is invited or is contest director
        if ($r['clarification']->public != 1) {
            if (!\OmegaUp\Authorization::canViewClarification(
                $r->identity,
                $r['clarification']
            )) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
        }
    }

    /**
     * API for getting a clarification
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        // Authenticate the user
        self::authenticateRequest($r);

        // Validate request
        self::validateDetails($r);

        // Create array of relevant columns
        $relevant_columns = ['message', 'answer', 'time', 'problem_id', 'problemset_id'];

        // Add the clarificatoin the response
        $response = $r['clarification']->asFilteredArray($relevant_columns);
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Validate update API request
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateUpdate(\OmegaUp\Request $r) {
        $r->ensureInt('clarification_id');
        $r->ensureBool('public', false /* not required */);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['answer'], 'answer');
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['message'], 'message');

        // Check that clarification exists
        $r['clarification'] = \OmegaUp\DAO\Clarifications::GetByPK($r['clarification_id']);
        if (is_null($r['clarification'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('clarificationNotFound');
        }

        if (!\OmegaUp\Authorization::canEditClarification(
            $r->identity,
            $r['clarification']
        )) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Update a clarification
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        // Validate request
        self::validateUpdate($r);

        // Update clarification
        $valueProperties = [
            'message',
            'answer',
            'public',
        ];
        $clarification = $r['clarification'];
        self::updateValueProperties($r, $clarification, $valueProperties);
        $r['clarification'] = $clarification;

        // Save the clarification
        $clarification->time = \OmegaUp\Time::get();
        \OmegaUp\DAO\Clarifications::update($clarification);

        $r['problem'] = $r['contest'] = $r['user'] = null;
        self::clarificationUpdated($r, $clarification);

        $response = [];
        $response['status'] = 'ok';

        return $response;
    }

    private static function clarificationUpdated(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ) : void {
        try {
            if (is_null($r['problem'])) {
                $r['problem'] = \OmegaUp\DAO\Problems::GetByPK($clarification->problem_id);
            }
            if (is_null($r['contest']) && !is_null($clarification->problemset_id)) {
                $r['contest'] = \OmegaUp\DAO\Contests::getByProblemset($clarification->problemset_id);
            }
            if (is_null($r['user'])) {
                $r['user'] = \OmegaUp\DAO\Identities::GetByPK($clarification->author_id);
            }
        } catch (Exception $e) {
            self::$log->error('Failed to broadcast clarification', $e);
            return;
        }
        self::initializeBroadcaster();
        self::$broadcaster->broadcastClarification(
            $clarification,
            $r['problem'],
            $r['user'],
            $r['contest']
        );
    }
}
