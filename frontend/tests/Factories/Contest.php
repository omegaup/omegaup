<?php

namespace OmegaUp\Test\Factories;

class ContestParams {
    /**
     * @readonly
     * @var string
     */
    public $title;

    /**
     * @readonly
     * @var string
     */
    public $description;

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
     * @var list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var \OmegaUp\Timestamp
     */
    public $startTime;

    /**
     * @readonly
     * @var \OmegaUp\Timestamp
     */
    public $finishTime;

    /**
     * @readonly
     * @var \OmegaUp\Timestamp
     */
    public $lastUpdated;

    /**
     * @readonly
     * @var string
     */
    public $penaltyCalcPolicy;

    /**
     * @readonly
     * @var string
     */
    public $feedback;

    /**
     * @readonly
     * @var bool
     */
    public $contestForTeams;

    /**
     * @readonly
     * @var null|string
     */
    public $teamsGroupAlias;

    /**
     * @readonly
     * @var null|string
     */
    public $scoreMode;

    /**
     * @readonly
     * @var bool
     */
    public $checkPlagiarism;

    /**
     * @readonly
     * @var int|null
     */
    public $scoreboardPct;

    /**
     * @readonly
     * @var string
     */
    public $alias;

    /**
     * @readonly
     * @var bool
     */
    public $showScoreboardAfter;

    /**
     * @readonly
     * @var string
     */
    public $penaltyType;

    /**
     * @readonly
     * @var int
     */
    public $submissionsGap;

    /**
     * @readonly
     * @var float
     */
    public $pointsDecayFactor;

    /**
     * @param array{alias?: string, admissionMode?: string, basicInformation?: bool, checkPlagiarism?: bool, contestDirector?: \OmegaUp\DAO\VO\Identities, contestDirectorUser?: \OmegaUp\DAO\VO\Users, contestForTeams?: bool, feedback?: string, finishTime?: \OmegaUp\Timestamp, languages?: list<string>, lastUpdated?: \OmegaUp\Timestamp, penaltyCalcPolicy?: string, penaltyType?: string, requestsUserInformation?: string, scoreboardPct?: int, scoreMode?: string, showScoreboardAfter?: bool, startTime?: \OmegaUp\Timestamp, teamsGroupAlias?: string, title?: string, windowLength?: ?int} $params
     */
    public function __construct($params = []) {
        $this->title = $params['title'] ?? \OmegaUp\Test\Utils::createRandomString();
        $this->description = $params['description'] ?? 'description';
        $this->alias = $params['alias'] ?? substr($this->title, 0, 20);
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
        $this->languages = $params['languages'] ?? ['c11-gcc','c11-clang','cpp11-gcc','cpp11-clang','cpp17-gcc','cpp17-clang','java','py2','py3','rb','cs','pas','hs','lua'];
        $this->startTime = (
            $params['startTime'] ??
            new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 60 * 60)
        );
        $this->finishTime = (
            $params['finishTime'] ??
            new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 60 * 60)
        );
        $this->lastUpdated = (
            $params['lastUpdated'] ??
            new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 60 * 60)
        );
        $this->penaltyCalcPolicy = $params['penaltyCalcPolicy'] ?? 'sum';
        $this->penaltyType = $params['penaltyType'] ?? 'contest_start';
        $this->feedback = $params['feedback'] ?? 'detailed';
        $this->contestForTeams = $params['contestForTeams'] ?? false;
        $this->teamsGroupAlias = $params['teamsGroupAlias'] ?? null;
        $this->scoreMode = $params['scoreMode'] ?? 'partial';
        $this->checkPlagiarism = $params['checkPlagiarism'] ?? false;
        $this->showScoreboardAfter = $params['showScoreboardAfter'] ?? false;
        $this->scoreboardPct = $params['scoreboardPct'] ?? 100;
        $this->submissionsGap = $params['submissionsGap'] ?? 60;
        $this->pointsDecayFactor = $params['pointsDecayFactor'] ?? 0.02;
    }
}

/**
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type InteractiveSettingsDistrib=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type ProblemsetterInfo=array{classname: string, creation_date: \OmegaUp\Timestamp|null, name: string, username: string}
 * @psalm-type ProblemStatement=array{images: array<string, string>, sources: array<string, string>, language: string, markdown: string}
 * @psalm-type ProblemSettingsDistrib=array{cases: array<string, array{in: string, out: string, weight?: float}>, interactive?: InteractiveSettingsDistrib, limits: LimitsSettings, validator: array{custom_validator?: array{language: string, limits?: LimitsSettings, source: string}, name: string, tolerance?: float}}
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ProblemDetailsForTesting=array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettingsDistrib, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 */
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
            'description' => $params->description,
            'start_time' => (new \OmegaUp\Timestamp($params->startTime))->time,
            'finish_time' => (new \OmegaUp\Timestamp(
                $params->finishTime
            ))->time,
            'last_updated' => (new \OmegaUp\Timestamp(
                $params->lastUpdated
            ))->time,
            'window_length' => $params->windowLength,
            'admission_mode' => $params->admissionMode,
            'alias' => $params->alias,
            'points_decay_factor' => $params->pointsDecayFactor,
            'score_mode' => $params->scoreMode,
            'submissions_gap' => $params->submissionsGap,
            'feedback' => $params->feedback,
            'penalty' => 100,
            'scoreboard' => $params->scoreboardPct,
            'penalty_type' => $params->penaltyType,
            'languages' => join(',', $params->languages),
            'needs_basic_information' => $params->basicInformation,
            'requests_user_information' => $params->requestsUserInformation,
            'penalty_calc_policy' => $params->penaltyCalcPolicy,
            'contest_for_teams' => $params->contestForTeams,
            'check_plagiarism' => $params->checkPlagiarism,
            'show_scoreboard_after' => $params->showScoreboardAfter,
        ]);

        if (!is_null($params->teamsGroupAlias)) {
            $r['teams_group_alias'] = $params->teamsGroupAlias;
        }

        if (!is_null($params->scoreMode)) {
            $r['score_mode'] = $params->scoreMode;
        }

        return [
            'request' => $r,
            'director' => $params->contestDirector,
            'userDirector' => $params->contestDirectorUser,
        ];
    }

    /**
     * Insert problems in a contest
     *
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
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
     * @return array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users}
     */
    public static function createContest(?ContestParams $params = null): array {
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
        \OmegaUp\Controllers\Contest::apiCreate(clone $r);
        if ($params->admissionMode === 'public') {
            self::forcePublic($contestData, $params->lastUpdated);
            $r['admission_mode'] = 'public';
        }

        $contest = \OmegaUp\DAO\Contests::getByAlias($r->ensureString('alias'));
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        return [
            'director' => $contestData['director'],
            'userDirector' => $contestData['userDirector'],
            'request' => $r,
            'contest' => $contest
        ];
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addProblemToContest(
        $problemData,
        $contestData,
        int $points = 100
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
            'points' => $points,
            'order_in_contest' => 1,
        ]));
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
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

    public static function openContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $user
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login($user);

        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contest->alias,
        ]));
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $user
     *
     * @return ProblemDetailsForTesting
     */
    public static function openProblemInContest(
        $contestData,
        $problemData,
        $user
    ): array {
        $login = \OmegaUp\Test\ControllerTestCase::login($user);

        return \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']->ensureString('alias'),
            'problem_alias' => (
                $problemData['request']->ensureString('problem_alias')
            ),
        ]));
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addUser(
        array $contestData,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        // Log in the contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']->ensureString('alias'),
            'usernameOrEmail' => $identity->username,
        ]));
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addIdentity(
        array $contestData,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        // Log in the contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        // Call api
        \OmegaUp\Controllers\Contest::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']->ensureString('alias'),
            'usernameOrEmail' => $identity->username,
        ]));
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function addAdminUser(
        $contestData,
        \OmegaUp\DAO\VO\Identities $user
    ): void {
        // Log in the contest director
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $contestData['director']
        );

        // Call api
        \OmegaUp\Controllers\Contest::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']->ensureString('alias'),
            'usernameOrEmail' => $user->username,
        ]));
    }

    /**
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
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
        ?\OmegaUp\Timestamp $lastUpdated = null
    ): void {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']->ensureString('alias')
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
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     */
    public static function setScoreboardPercentage(
        array $contestData,
        int $percentage
    ): void {
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']->ensureString('alias')
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
