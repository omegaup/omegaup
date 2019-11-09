<?php

class ContestParams {
    /**
     * @readonly
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $admissionMode;

    /**
     * @readonly
     * @var bool
     */
    public $basicInformation;

    /**
     * @readonly
     * @var string
     */
    public $requestsUserInformation;

    /**
     * @readonly
     * @var \OmegaUp\DAO\VO\Identities
     */
    public $contestDirector;

    /**
     * @readonly
     * @var \OmegaUp\DAO\VO\Users
     */
    public $contestDirectorUser;

    /**
     * @readonly
     * @var ?int
     */
    public $windowLength;

    /**
     * @readonly
     * @var null|list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var int
     */
    public $startTime;

    /**
     * @readonly
     * @var int
     */
    public $finishTime;

    /**
     * @readonly
     * @var int
     */
    public $lastUpdated;

    /**
     * @readonly
     * @var string
     */
    public $penaltyCalcPolicy;

    /**
     * @param array{title?: string, admissionMode?: string, basicInformation?: bool, requestsUserInformation?: string, contestDirector?: \OmegaUp\DAO\VO\Identities, contestDirectorUser?: \OmegaUp\DAO\VO\Users, windowLength?: ?int, languages?: ?list<string>, startTime?: int, finishTime?: int, lastUpdated?: int, penaltyCalcPolicy?: string} $params
     */
    public function __construct($params = []) {
        $this->title = $params['title'] ?? Utils::CreateRandomString();
        $this->admissionMode = $params['admissionMode'] ?? 'public';
        $this->basicInformation = $params['basicInformation'] ?? false;
        $this->requestsUserInformation = $params['requestsUserInformation'] ?? 'no';
        if (
            !empty($params['contestDirector']) &&
            !empty($params['contestDirectorUser'])
        ) {
            $this->contestDirector = $params['contestDirector'];
            $this->contestDirectorUser = $params['contestDirectorUser'];
        } else {
            [
                'user' => $user,
                'identity' => $identity,
            ] = UserFactory::createUser();
            $this->contestDirector = $params['contestDirector'] ?? $identity;
            $this->contestDirectorUser = $params['contestDirectorUser'] ?? $user;
        }
        $this->windowLength = $params['windowLength'] ?? null;
        $this->languages = $params['languages'] ?? null;
        $this->startTime = $params['startTime'] ?? (\OmegaUp\Time::get() - 60 * 60);
        $this->finishTime = $params['finishTime'] ?? (\OmegaUp\Time::get() + 60 * 60);
        $this->lastUpdated = $params['lastUpdated'] ?? (\OmegaUp\Time::get() + 60 * 60);
        $this->penaltyCalcPolicy = $params['penaltyCalcPolicy'] ?? 'sum';
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
     * @return array{director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}
     */
    public static function getRequest(?ContestParams $params = null): array {
        if (is_null($params)) {
            $params = new ContestParams();
        }

        // Set context
        $r = new \OmegaUp\Request([
            'title' => $params->title,
            'description' => 'description',
            'start_time' => $params->startTime,
            'finish_time' => $params->finishTime,
            'last_updated' => $params->lastUpdated,
            'window_length' => $params->windowLength,
            'admission_mode' => $params->admissionMode,
            'alias' => substr($params->title, 0, 20),
            'points_decay_factor' => '0.02',
            'partial_score' => '0',
            'submissions_gap' => '60',
            'feedback' => 'yes',
            'penalty' => 100,
            'scoreboard' => 100,
            'penalty_type' => 'contest_start',
            'languages' => $params->languages,
            'recommended' => 0, // This is just a default value, it is not honored by apiCreate.
            'basic_information' => $params->basicInformation,
            'requests_user_information' => $params->requestsUserInformation,
            'penalty_calc_policy' => $params->penaltyCalcPolicy,
        ]);

        return [
            'request' => $r,
            'director' => $params->contestDirector,
            'userDirector' => $params->contestDirectorUser,
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
    public static function createContest(?ContestParams $params = null) {
        if (is_null($params)) {
            $params = new ContestParams();
        }

        $privateParams = clone $params;
        // Create a valid contest Request object
        $privateParams->admissionMode = 'private';
        $contestData = ContestsFactory::getRequest($privateParams);

        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = OmegaupTestCase::login($contestDirector);
        $r['auth_token'] = $login->auth_token;

        // Call the API
        $response = \OmegaUp\Controllers\Contest::apiCreate($r);
        if ($params->admissionMode === 'public') {
            self::forcePublic($contestData, $params->lastUpdated);
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
    ): void {
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
        $r['problem_alias'] = strval($problemData['request']['problem_alias']);

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
        $r['contest_alias'] = strval($contestData['request']['alias']);
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
        $r['contest_alias'] = strval($contestData['request']['alias']);
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
    ): void {
        // Prepare our request
        $r = new \OmegaUp\Request();
        $r['contest_alias'] = strval($contestData['request']['alias']);
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
    ): void {
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
    ): void {
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
    ): void {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contest->scoreboard = $percentage;
        \OmegaUp\DAO\Contests::update($contest);
    }
}
