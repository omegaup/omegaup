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
        ContestParams::validateParameter(
            'title',
            $this->params,
            false,
            Utils::CreateRandomString()
        );
        ContestParams::validateParameter(
            'admission_mode',
            $this->params,
            false,
            'public'
        );
        ContestParams::validateParameter(
            'basic_information',
            $this->params,
            false,
            'false'
        );
        ContestParams::validateParameter(
            'requests_user_information',
            $this->params,
            false,
            'no'
        );
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        ContestParams::validateParameter(
            'contestDirector',
            $this->params,
            false,
            $identity
        );
        ContestParams::validateParameter(
            'contestUserDirector',
            $this->params,
            false,
            $user
        );
        ContestParams::validateParameter('window_length', $this->params, false);
        ContestParams::validateParameter('languages', $this->params, false);
        ContestParams::validateParameter(
            'start_time',
            $this->params,
            false,
            (\OmegaUp\Time::get() - 60 * 60)
        );
        ContestParams::validateParameter(
            'finish_time',
            $this->params,
            false,
            (\OmegaUp\Time::get() + 60 * 60)
        );
        ContestParams::validateParameter(
            'last_updated',
            $this->params,
            false,
            (\OmegaUp\Time::get() + 60 * 60)
        );
        ContestParams::validateParameter(
            'penalty_calc_policy',
            $this->params,
            false
        );
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

    /**
     * Checks if array contains a key defined by $parameter
     * @param string $parameter
     * @param array $array
     * @param boolean $required
     * @param $default
     * @return boolean
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateParameter(
        $parameter,
        &$array,
        $required = true,
        $default = null
    ) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'ParameterEmpty',
                    $parameter
                );
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
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @param ContestParams $params
     * @return array{director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}
     */
    public static function getRequest($params = null) {
        if (!($params instanceof ContestParams)) {
            $params = new ContestParams($params);
        }

        // Set context
        $r = new \OmegaUp\Request([
            'title' => $params['title'],
            'description' => 'description',
            'start_time' => $params['start_time'],
            'finish_time' => $params['finish_time'],
            'last_updated' => $params['last_updated'],
            'window_length' => $params['window_length'],
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
            'basic_information' => $params['basic_information'],
            'requests_user_information' => $params['requests_user_information'],
            'languages' => $params['languages'],
        ]);
        if (is_null($params['penalty_calc_policy'])) {
            $r['penalty_calc_policy'] = 'sum';
        } else {
            $r['penalty_calc_policy'] = $params['penalty_calc_policy'];
        }

        return [
            'request' => $r,
            'director' => $params['contestDirector'],
            'userDirector' => $params['contestUserDirector'],
        ];
    }

    /**
     * Insert problems in a contest
     *
     * @param type $contestData
     * @param type $numOfProblems
     * @return array array of problemData
     */
    public static function insertProblemsInContest(
        $contestData,
        $numOfProblems = 3
    ) {
        // Create problems
        $problems = [];
        for ($i = 0; $i < $numOfProblems; $i++) {
            $problems[$i] = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problems[$i], $contestData);
        }

        return $problems;
    }

    /**
     * @return array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}
     */
    public static function createContest(
        ContestParams $params = null
    ): array {
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
        $response = \OmegaUp\Controllers\Contest::apiCreate($r);
        if ($params['admission_mode'] === 'public') {
            self::forcePublic($contestData, intval($params['last_updated']));
            $r['admission_mode'] = 'public';
        }

        $contest = \OmegaUp\DAO\Contests::getByAlias(strval($r['alias']));

        return [
            'director' => $contestData['director'],
            'userDirector' => $contestData['userDirector'],
            'request' => $r,
            'contest' => $contest
        ];
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addProblemToContest(
        $problemData,
        $contestData
    ): void {
        // Create an empty request
        $r = new \OmegaUp\Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Build request
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['problem_alias'];
        $r['points'] = 100;
        $r['order_in_contest'] = 1;

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiAddProblem($r);

        // Clean up
        unset($_REQUEST);
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @return array{status: string}
     */
    public static function removeProblemFromContest(
        $problemData,
        $contestData
    ): array {
        // Log in as contest director
        $login = OmegaupTestCase::login($contestData['director']);

        $r = new \OmegaUp\Request(
            [
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias']
            ]
        );

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRemoveProblem($r);

        // Clean up
        unset($_REQUEST);

        return $response;
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $user
     */
    public static function openContest(
        $contestData,
        $user
    ) {
        // Create an empty request
        $r = new \OmegaUp\Request();

        // Log in as contest director
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Prepare our request
        $r['contest_alias'] = $contestData['request']['alias'];

        // Call api
        \OmegaUp\Controllers\Contest::apiOpen($r);

        unset($_REQUEST);
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $user
     */
    public static function openProblemInContest(
        $contestData,
        $problemData,
        $user
    ) {
        // Prepare our request
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['problem_alias'] = $problemData['request']['problem_alias'];

        // Log in the user
        $login = OmegaupTestCase::login($user);
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Problem::apiDetails($r);

        unset($_REQUEST);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addUser(
        array $contestData,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        // Prepare our request
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $identity->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser($r);

        unset($_REQUEST);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addIdentity(
        array $contestData,
        \OmegaUp\DAO\VO\Identities $identitiy
    ): void {
        // Prepare our request
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $identitiy->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser($r);

        unset($_REQUEST);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addAdminUser(
        $contestData,
        \OmegaUp\DAO\VO\Identities $user
    ) {
        // Prepare our request
        $r = new \OmegaUp\Request();
        /** @var string */
        $r['contest_alias'] = $contestData['request']['alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddAdmin($r);

        unset($_REQUEST);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addGroupAdmin(
        $contestData,
        \OmegaUp\DAO\VO\Groups $group
    ) {
        // Prepare our request
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'group' => $group->alias,
        ]);

        // Log in the contest director
        $login = OmegaupTestCase::login($contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddGroupAdmin($r);
    }

    /**
     * @param array{director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function forcePublic(
        array $contestData,
        ?int $lastUpdated = null
    ) {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contest->admission_mode = 'public';
        $contest->last_updated = $lastUpdated;
        \OmegaUp\DAO\Contests::update($contest);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function setScoreboardPercentage(
        $contestData,
        int $percentage
    ) {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contest->scoreboard = $percentage;
        \OmegaUp\DAO\Contests::update($contest);
    }
}
