<?php

namespace OmegaUp\Test\Factories;

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
        $this->title = $params['title'] ?? \OmegaUp\Test\Utils::createRandomString();
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
            ] = \OmegaUp\Test\Factories\User::createUser();
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

class Contest {
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
            'feedback' => 'detailed',
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
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param int $numOfProblems
     * @return list<array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request}>
     */
    public static function insertProblemsInContest(
        array $contestData,
        int $numOfProblems = 3
    ): array {
        /** @var list<array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request}> */
        $problems = [];
        for ($i = 0; $i < $numOfProblems; $i++) {
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problem,
                $contestData
            );
            $problems[] = $problem;
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
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(
            $privateParams
        );

        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = \OmegaUp\Test\ControllerTestCase::login($contestDirector);
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
        // Log in as contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        // Call API
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 100,
            'order_in_contest' => 1,
        ]));
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
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        // Call API
        return \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias']
        ]));
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $user
     */
    public static function openContest(
        $contestData,
        $user
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login($user);

        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));
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
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login($user);

        \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => strval($problemData['request']['problem_alias']),
        ]));
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
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser($r);
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
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser($r);
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
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Contest::apiAddAdmin($r);
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
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );
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
            strval($contestData['request']['alias'])
        );
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }
        $contest->admission_mode = 'public';
        if (!is_null($lastUpdated)) {
            $contest->last_updated = $lastUpdated;
        }
        \OmegaUp\DAO\Contests::update($contest);
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function setScoreboardPercentage(
        array $contestData,
        int $percentage
    ): void {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            strval($contestData['request']['alias'])
        );
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }
        $contest->scoreboard = $percentage;
        \OmegaUp\DAO\Contests::update($contest);
    }
}
