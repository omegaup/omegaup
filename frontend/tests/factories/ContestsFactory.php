<?php

/**
 * ContestParams
 */
class ContestParams implements ArrayAccess {
    private $params;

    public function __construct($params = null) {
        if (!is_object($params)) {
            $this->params = [];
            if (is_array($params)) {
                $this->params = array_merge([], $params);
            }
        } else {
            $this->params = clone $params;
        }
        ContestParams::validateParameter('title', $this->params, false, Utils::CreateRandomString());
        ContestParams::validateParameter('admission_mode', $this->params, false, 'public');
        ContestParams::validateParameter('basic_information', $this->params, false, 'false');
        ContestParams::validateParameter('requests_user_information', $this->params, false, 'no');
        ContestParams::validateParameter('contestDirector', $this->params, false, UserFactory::createUser());
        ContestParams::validateParameter('languages', $this->params, false);
        ContestParams::validateParameter('start_time', $this->params, false, (Utils::GetPhpUnixTimestamp() - 60 * 60));
        ContestParams::validateParameter('finish_time', $this->params, false, (Utils::GetPhpUnixTimestamp() + 60 * 60));
        ContestParams::validateParameter('last_updated', $this->params, false, (Utils::GetPhpUnixTimestamp() + 60 * 60));
        ContestParams::validateParameter('penalty_calc_policy', $this->params, false);
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    public static function fromContest(Contests $contest) {
        return new ContestParams([
            'title' => $contest->title,
            'admission_mode' => $contest->admission_mode,
            'basic_information' => $contest->basic_information,
            'contestDirector' => $contest->contestDirector,
            'languages' => $contest->languages,
            'start_time' => $contest->start_time,
            'finish_time' => $contest->finish_time,
            'last_updated' => $contest->last_updated,
            'penalty_calc_policy' => $contest->penalty_calc_policy,
        ]);
    }

    /**
     * Checks if array contains a key defined by $parameter
     * @param string $parameter
     * @param array $array
     * @param boolean $required
     * @param $default
     * @return boolean
     * @throws InvalidParameterException
     */
    private static function validateParameter($parameter, &$array, $required = true, $default = null) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new InvalidParameterException('ParameterEmpty', $parameter);
            }
            $array[$parameter] = $default;
        }

        return true;
    }
}

/**
 * ContestsFactory
 *
 * @author joemmanuel
 */

class ContestsFactory {
    /**
     * Returns a Request object with complete context to create a contest.
     * By default, contest duration is 1HR.
     *
     * @param string $title
     * @param string $public
     * @param Users $contestDirector
     * @return Request
     */
    public static function getRequest($params = null) {
        if (!($params instanceof ContestParams)) {
            $params = new ContestParams($params);
        }

        // Set context
        $r = new Request([
            'title' => $params['title'],
            'description' => 'description',
            'start_time' => $params['start_time'],
            'finish_time' => $params['finish_time'],
            'last_updated' => $params['last_updated'],
            'window_length' => null,
            'admission_mode' => $params['admission_mode'],
            'alias' => substr($params['title'], 0, 20),
            'points_decay_factor' => '0.02',
            'partial_score' => '0',
            'submissions_gap' => '60',
            'feedback' => 'yes',
            'penalty' => 100,
            'scoreboard' => 100,
            'penalty_type' => 'contest_start',
            'languages' => $params['languages'],
            'recommended' => 0, // This is just a default value, it is not honored by apiCreate.
            'basic_information' => $params['basic_information'], // This is just a default value.
        ]);
        if ($params['penalty_calc_policy'] == null) {
            $r['penalty_calc_policy'] = 'sum';
        } else {
            $r['penalty_calc_policy'] = $params['penalty_calc_policy'];
        }
        $r['languages'] = $params['languages'];
        $r['basic_information'] = $params['basic_information']; // This is just a default value.
        $r['requests_user_information'] = $params['requests_user_information']; // This is just a default value.

        return [
            'request' => $r,
            'director' => $params['contestDirector']
        ];
    }

    /**
     * Insert problems in a contest
     *
     * @param type $contestData
     * @param type $numOfProblems
     * @return array array of problemData
     */
    public static function insertProblemsInContest($contestData, $numOfProblems = 3) {
        // Create problems
        $problems = [];
        for ($i = 0; $i < $numOfProblems; $i++) {
            $problems[$i] = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problems[$i], $contestData);
        }

        return $problems;
    }

    public static function createContest($params = null) {
        if (!($params instanceof ContestParams)) {
            $params = new ContestParams($params);
        }
        $privateParams = new ContestParams($params);
        // Create a valid contest Request object
        $privateParams['admission_mode'] = 'private';
        $contestData = ContestsFactory::getRequest($privateParams);

        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = OmegaupTestCase::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);
        if ($params['admission_mode'] === 'public') {
            self::forcePublic($contestData, $params['last_updated']);
            $r['admission_mode'] = 'public';
        }

        $contest = ContestsDAO::getByAlias($r['alias']);

        return [
            'director' => $contestData['director'],
            'request' => $r,
            'contest' => $contest
        ];
    }

    public static function addProblemToContest($problemData, $contestData) {
        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['problem_alias'];
        $r['points'] = 100;
        $r['order_in_contest'] = 1;

        // Call API
        $response = ContestController::apiAddProblem($r);

        // Clean up
        unset($_REQUEST);
    }

    public static function removeProblemFromContest($problemData, $contestData) {
        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new Request(
            [
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias']
            ]
        );

        // Call API
        $response = ContestController::apiRemoveProblem($r);

        // Clean up
        unset($_REQUEST);

        return $response;
    }

    public static function openContest($contestData, $user) {
        // Create an empty request
        $r = new Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Prepare our request
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        ContestController::apiOpen($r);

        unset($_REQUEST);
    }

    public static function openProblemInContest($contestData, $problemData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['problem_alias'];

        // Log in the user
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ProblemController::apiDetails($r);

        unset($_REQUEST);
    }

    public static function addUser($contestData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddUser($r);

        unset($_REQUEST);
    }

    public static function addAdminUser($contestData, $user) {
        // Prepare our request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddAdmin($r);

        unset($_REQUEST);
    }

    public static function addGroupAdmin($contestData, Groups $group) {
        // Prepare our request
        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'group' => $group->alias,
        ]);

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ContestController::apiAddGroupAdmin($r);
    }

    public static function makeContestWindowLength($contestData, $windowLength = 20) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->window_length = $windowLength;
        ContestsDAO::save($contest);
    }

    public static function forcePublic($contestData, $last_updated = null) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->admission_mode = 'public';
        $contest->last_updated = gmdate('Y-m-d H:i:s', $last_updated);
        ContestsDAO::save($contest);
    }

    public static function setScoreboardPercentage($contestData, $percentage) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->scoreboard = $percentage;
        ContestsDAO::save($contest);
    }
}
