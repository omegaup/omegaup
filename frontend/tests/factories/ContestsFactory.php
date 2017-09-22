<?php

/**
 * ContestsParams
 */
class ContestsParams implements ArrayAccess {
    private $params;

    public function __construct(array $params) {
        ContestsParams::validateParameter('title', $params, false);
        ContestsParams::validateParameter('public', $params, false, 1);
        ContestsParams::validateParameter('contestDirector', $params, false);
        ContestsParams::validateParameter('languages', $params, false);
        ContestsParams::validateParameter('start_time', $params, false);
        ContestsParams::validateParameter('finish_time', $params, false);
        ContestsParams::validateParameter('penalty_calc_policy', $params, false);

        $this->params = $params;
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
        return new ContestsParams([
                'title' => $contest->title,
                'public' => $contest->public,
                'contestDirector' => $contest->contestDirector,
                'languages' => $contest->languages,
                'start_time' => $contest->start_time,
                'finish_time' => $contest->finish_time,
                'penalty_calc_policy' => $contest->penalty_calc_policy]);
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
    private static function validateParameter($parameter, array& $array, $required = true, $default = null) {
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
    private $params;

    public function __construct(ContestsParams $params) {
        $this->params = $params;
    }

    /**
     * Returns a Request object with complete context to create a contest.
     * By default, contest duration is 1HR.
     *
     * @param string $title
     * @param string $public
     * @param Users $contestDirector
     * @return Request
     */
    public function getRequest() {
        if (is_null($this->params['contestDirector'])) {
            $this->params['contestDirector'] = UserFactory::createUser();
        }

        if (is_null($this->params['title'])) {
            $this->params['title'] = Utils::CreateRandomString();
        }

        // Set context
        $r = new Request();
        $r['title'] = $this->params['title'];
        $r['description'] = 'description';
        $r['start_time'] = ($this->params['start_time'] == null ? (Utils::GetPhpUnixTimestamp() - 60 * 60) : $this->params['start_time']);
        $r['finish_time'] = ($this->params['finish_time'] == null ? (Utils::GetPhpUnixTimestamp() + 60 * 60) : $this->params['finish_time']);
        $r['window_length'] = null;
        $r['public'] = $this->params['public'];
        $r['alias'] = substr($this->params['title'], 0, 20);
        $r['points_decay_factor'] = '.02';
        $r['partial_score'] = '0';
        $r['submissions_gap'] = '0';
        $r['feedback'] = 'yes';
        $r['penalty'] = 100;
        $r['scoreboard'] = 100;
        $r['penalty_type'] = 'contest_start';
        if ($this->params['penalty_calc_policy'] == null) {
            $r['penalty_calc_policy'] = 'sum';
        } else {
            $r['penalty_calc_policy'] = $this->params['penalty_calc_policy'];
        }
        $r['languages'] = $this->params['languages'];
        $r['recommended'] = 0; // This is just a default value, it is not honored by apiCreate.

        return [
            'request' => $r,
            'director' => $this->params['contestDirector']
        ];
    }

    public function createContest() {
        // Create a valid contest Request object
        $tmpPublic = $this->params['public'];
        $this->params['public'] = 0;
        $contestData = ContestsFactory::getRequest();
        $this->params['public'] = $tmpPublic;
        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = OmegaupTestCase::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = ContestController::apiCreate($r);

        if ($this->params['public'] === 1) {
            self::forcePublic($contestData);
            $r['public'] = 1;
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
        $r['problem_alias'] = $problemData['request']['alias'];
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
                'problem_alias' => $problemData['request']['alias']
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
        $r['problem_alias'] = $problemData['request']['alias'];

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

    public static function forcePublic($contestData) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->public = 1;
        ContestsDAO::save($contest);
    }

    public static function setScoreboardPercentage($contestData, $percentage) {
        $contest = ContestsDAO::getByAlias($contestData['request']['alias']);
        $contest->scoreboard = $percentage;
        ContestsDAO::save($contest);
    }
}
