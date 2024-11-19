<?php

namespace OmegaUp;

class ContestParams extends BaseParams {
    // Constants for admission mode.
    const CONTEST_ADMISSION_MODE_PRIVATE = 'private';
    const CONTEST_ADMISSION_MODE_REGISTRATION = 'registration';
    const CONTEST_ADMISSION_MODE_PUBLIC = 'public';

    // Feedback constants.
    const CONTEST_FEEDBACK_NONE = 'none';
    const CONTEST_FEEDBACK_SUMMARY = 'summary';
    const CONTEST_FEEDBACK_DETAILED = 'detailed';

    // Penalty type constants.
    const CONTEST_PENALTY_TYPE_CONTEST_START = 'contest_start';
    const CONTEST_PENALTY_TYPE_PROBLEM_OPEN = 'problem_open';
    const CONTEST_PENALTY_TYPE_RUNTIME = 'runtime';
    const CONTEST_PENALTY_TYPE_NONE = 'none';

    // Penalty calc policy constants.
    const CONTEST_PENALTY_CALC_POLICY_SUM = 'sum';
    const CONTEST_PENALTY_CALC_POLICY_MAX = 'max';

    // Certificate status constants.
    const CONTEST_CERTIFICATE_STATUS_UNINITIATED = 'uninitiated';
    const CONTEST_CERTIFICATE_STATUS_QUEUED = 'queued';
    const CONTEST_CERTIFICATE_STATUS_GENERATED = 'generated';
    const CONTEST_CERTIFICATE_STATUS_RETRYABLE_ERROR = 'retryable_error';
    const CONTEST_CERTIFICATE_STATUS_FATAL_ERROR = 'fatal_error';

    // Score mode constants.
    const CONTEST_SCORE_MODE_PARTIAL = 'partial';
    const CONTEST_SCORE_MODE_ALL_OR_NOTHING = 'all_or_nothing';
    const CONTEST_SCORE_MODE_MAX_PER_GROUP = 'max_per_group';

    const VALID_ADMISSION_MODES = [
        self::CONTEST_ADMISSION_MODE_PRIVATE,
        self::CONTEST_ADMISSION_MODE_REGISTRATION,
        self::CONTEST_ADMISSION_MODE_PUBLIC,
    ];

    const VALID_FEEDBACK_VALUES = [
        self::CONTEST_FEEDBACK_NONE,
        self::CONTEST_FEEDBACK_SUMMARY,
        self::CONTEST_FEEDBACK_DETAILED,
    ];

    const VALID_PENALTY_TYPES = [
        self::CONTEST_PENALTY_TYPE_CONTEST_START,
        self::CONTEST_PENALTY_TYPE_PROBLEM_OPEN,
        self::CONTEST_PENALTY_TYPE_RUNTIME,
        self::CONTEST_PENALTY_TYPE_NONE,
    ];

    const VALID_PENALTY_CALC_POLICY_VALUES = [
        self::CONTEST_PENALTY_CALC_POLICY_SUM,
        self::CONTEST_PENALTY_CALC_POLICY_MAX,
    ];

    const VALID_CERTIFICATES_STATUSES = [
        self::CONTEST_CERTIFICATE_STATUS_UNINITIATED,
        self::CONTEST_CERTIFICATE_STATUS_QUEUED,
        self::CONTEST_CERTIFICATE_STATUS_GENERATED,
        self::CONTEST_CERTIFICATE_STATUS_RETRYABLE_ERROR,
        self::CONTEST_CERTIFICATE_STATUS_FATAL_ERROR,
    ];

    const VALID_SCORE_MODES = [
        self::CONTEST_SCORE_MODE_PARTIAL,
        self::CONTEST_SCORE_MODE_ALL_OR_NOTHING,
        self::CONTEST_SCORE_MODE_MAX_PER_GROUP,
    ];

    /**
     * @readonly
     * @var null|string
     */
    public $contestAlias;

    /**
     * @readonly
     * @var null|string
     */
    public $title;

    /**
     * @readonly
     * @var null|string
     */
    public $description;

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
     * @var int|null
     */
    public $windowLength;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PRIVATE|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_REGISTRATION|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PUBLIC
     */
    public $admissionMode;

    /**
     * @readonly
     * @var float|null
     */
    public $scoreboard;

    /**
     * @readonly
     * @var float|null
     */
    public $pointsDecayFactor;

    /**
     * @readonly
     * @var int|null
     */
    public $submissionsGap;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_FEEDBACK_NONE|\OmegaUp\ContestParams::CONTEST_FEEDBACK_SUMMARY|\OmegaUp\ContestParams::CONTEST_FEEDBACK_DETAILED|null
     */
    public $feedback;

    /**
     * @readonly
     * @var int|null
     */
    public $penalty;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_CONTEST_START|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_PROBLEM_OPEN|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_RUNTIME|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_NONE|null
     */
    public $penaltyType;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_SUM|\OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_MAX|null
     */
    public $penaltyCalcPolicy;

    /**
     * @readonly
     * @var bool|null
     */
    public $showScoreboardAfter;

    /**
     * @readonly
     * @var null|list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var int|null
     */
    public $certificateCutoff;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_UNINITIATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_QUEUED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_GENERATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_RETRYABLE_ERROR|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_FATAL_ERROR|null
     */
    public $certificatesStatus;

    /**
     * @readonly
     * @var bool|null
     */
    public $contestForTeams;

    /**
     * @readonly
     * @var bool|null
     */
    public $defaultShowAllContestantsInScoreboard;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_SCORE_MODE_PARTIAL|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_ALL_OR_NOTHING|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_MAX_PER_GROUP|null
     */
    public $scoreMode;

    /**
     * @readonly
     * @var bool
     */
    public $checkPlagiarism;

    /**
     * @psalm-suppress InvalidArrayOffset
     * @param array{admission_mode: "private"|"public"|"registration", alias: null|string, check_plagiarism: bool, contest_for_teams: bool, description: null|string, feedback: "detailed"|"none"|"summary"|null, finish_time: \OmegaUp\Timestamp, languages: null|string, penalty: int|null, penalty_calc_policy: "max"|"sum"|null, penalty_type: "contest_start"|"none"|"problem_open"|"runtime"|null, points_decay_factor: float|null, score_mode: "all_or_nothing"|"max_per_group"|"partial"|null, scoreboard: float|null, show_scoreboard_after: bool|null, start_time: \OmegaUp\Timestamp, submissions_gap: int|null, title: null|string, window_length?: int|null} $params
     */
    public function __construct($params) {
        $languages = !is_null(
            $params['languages']
        ) ? explode(
            ',',
            $params['languages']
        ) : null;
        if (!is_null($languages)) {
            \OmegaUp\Validators::validateValidSubset(
                $languages,
                'languages',
                array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES())
            );
        }

        $this->contestAlias = $params['alias'];
        $this->title = $params['title'];
        $this->description = $params['description'];
        $this->startTime = $params['start_time'];
        $this->finishTime = $params['finish_time'];
        $this->windowLength = $params['window_length'] ?? null;
        $this->admissionMode = $params['admission_mode'];
        $this->scoreboard = $params['scoreboard'];
        $this->pointsDecayFactor = $params['points_decay_factor'];
        $this->submissionsGap = $params['submissions_gap'];
        $this->feedback = $params['feedback'];
        $this->penalty = $params['penalty'];
        $this->penaltyType = $params['penalty_type'];
        $this->penaltyCalcPolicy = $params['penalty_calc_policy'];
        $this->showScoreboardAfter = $params['show_scoreboard_after'];
        $this->languages = $languages;
        $this->certificatesStatus = $params['certificates_status'];
        $this->contestForTeams = $params['contest_for_teams'] ?? null;
        $this->defaultShowAllContestantsInScoreboard = $params['default_show_all_contestants_in_scoreboard'] ?? null;
        $this->scoreMode = $params['score_mode'];
        $this->checkPlagiarism = $params['check_plagiarism'];
    }
}
