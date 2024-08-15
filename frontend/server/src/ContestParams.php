<?php

namespace OmegaUp;

class ContestParams {
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
     * @var string
     */
    public $contestAlias;

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
    public $lastUpadted;

    /**
     * @readonly
     * @var int|null
     */
    public $windowLength;

    /**
     * @readonly
     * @var int|null
     */
    public $rerunId;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PRIVATE|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_REGISTRATION|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PUBLIC
     */
    public $admissionMode;

    /**
     * @readonly
     * @var int
     */
    public $scoreboard;

    /**
     * @readonly
     * @var float
     */
    public $pointsDecayFactor;

    /**
     * @readonly
     * @var int
     */
    public $submissionsGap;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_FEEDBACK_NONE|\OmegaUp\ContestParams::CONTEST_FEEDBACK_SUMMARY|\OmegaUp\ContestParams::CONTEST_FEEDBACK_DETAILED
     */
    public $feedback;

    /**
     * @readonly
     * @var int
     */
    public $penalty;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_CONTEST_START|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_PROBLEM_OPEN|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_RUNTIME|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_NONE
     */
    public $penaltyType;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_SUM|\OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_MAX
     */
    public $penaltyCalcPolicy;

    /**
     * @readonly
     * @var int
     */
    public $showScoreboardAfter;

    /**
     * @readonly
     * @var null|list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var bool
     */
    public $urgent;

    /**
     * @readonly
     * @var bool
     */
    public $recommended;

    /**
     * @readonly
     * @var bool
     */
    public $archived;

    /**
     * @readonly
     * @var int|null
     */
    public $certificateCutoff;

    /**
     * @readonly
     * @var \OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_UNINITIATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_QUEUED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_GENERATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_RETRYABLE_ERROR|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_FATAL_ERROR
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
     * @var \OmegaUp\ContestParams::CONTEST_SCORE_MODE_PARTIAL|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_ALL_OR_NOTHING|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_MAX_PER_GROUP
     */
    public $scoreMode;

    /**
     * @readonly
     * @var bool
     */
    public $plagiarismTreshold;

    /**
     * @readonly
     * @var bool
     */
    public $checkPlagiarism;

    /**
     * @param array{alias: string, title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length?: int, rerun_id?: int, admission_mode: \OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PRIVATE|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_REGISTRATION|\OmegaUp\ContestParams::CONTEST_ADMISSION_MODE_PUBLIC, scoreboard: int, points_decay_factor: float, submissions_gap: int, feedback: \OmegaUp\ContestParams::CONTEST_FEEDBACK_NONE|\OmegaUp\ContestParams::CONTEST_FEEDBACK_SUMMARY|\OmegaUp\ContestParams::CONTEST_FEEDBACK_DETAILED, penalty: int, penalty_type: \OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_CONTEST_START|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_PROBLEM_OPEN|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_RUNTIME|\OmegaUp\ContestParams::CONTEST_PENALTY_TYPE_NONE, penalty_calc_policy: \OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_SUM|\OmegaUp\ContestParams::CONTEST_PENALTY_CALC_POLICY_MAX, show_scoreboard_after: int, languages?: list<string>, urgent: bool, recommended: bool, archived: bool, certificate_cutoff?: int, certificates_status: \OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_UNINITIATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_QUEUED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_GENERATED|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_RETRYABLE_ERROR|\OmegaUp\ContestParams::CONTEST_CERTIFICATE_STATUS_FATAL_ERROR, contest_for_teams?: bool, default_show_all_contestants_in_scoreboard?: bool, score_mode: \OmegaUp\ContestParams::CONTEST_SCORE_MODE_PARTIAL|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_ALL_OR_NOTHING|\OmegaUp\ContestParams::CONTEST_SCORE_MODE_MAX_PER_GROUP, plagiarism_treshold: bool, check_plagiarism: bool} $params
     */
    public function __construct($params) {
        $this->contestAlias = $params['alias'];
        $this->title = $params['title'];
        $this->description = $params['description'];
        $this->startTime = $params['start_time'];
        $this->finishTime = $params['finish_time'];
        $this->lastUpadted = $params['last_updated'];
        $this->windowLength = $params['window_length'] ?? null;
        $this->rerunId = $params['rerun_id'] ?? null;
        $this->admissionMode = $params['admission_mode'];
        $this->scoreboard = $params['scoreboard'];
        $this->pointsDecayFactor = $params['points_decay_factor'];
        $this->submissionsGap = $params['submissions_gap'];
        $this->feedback = $params['feedback'];
        $this->penalty = $params['penalty'];
        $this->penaltyType = $params['penalty_type'];
        $this->penaltyCalcPolicy = $params['penalty_calc_policy'];
        $this->showScoreboardAfter = $params['show_scoreboard_after'];
        $this->languages = $params['languages'] ?? null;
        $this->urgent = $params['urgent'];
        $this->recommended = $params['recommended'];
        $this->archived = $params['archived'];
        $this->certificateCutoff = $params['certificate_cutoff'] ?? null;
        $this->certificatesStatus = $params['certificates_status'];
        $this->contestForTeams = $params['contest_for_teams'] ?? null;
        $this->defaultShowAllContestantsInScoreboard = $params['default_show_all_contestants_in_scoreboard'] ?? null;
        $this->scoreMode = $params['score_mode'];
        $this->plagiarismTreshold = $params['plagiarism_treshold'];
        $this->checkPlagiarism = $params['check_plagiarism'];
    }

    /**
     * Update properties of $object based on what is provided in this class.
     *
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool, alias?: string}> $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    public function updateValueParams(
        object $object,
        array $properties
    ): bool {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            /** @var null|callable(mixed):mixed */
            $transform = null;
            $important = false;
            if (is_int($source)) {
                $thisFieldName = $info;
                $objectFieldName = $info;
            } else {
                $thisFieldName = $source;
                if (isset($info['transform'])) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
                if (!empty($info['alias'])) {
                    $objectFieldName = $info['alias'];
                } else {
                    $objectFieldName = $thisFieldName;
                }
            }
            // Get or calculate new value.
            /** @var null|mixed */
            $value = $this->$thisFieldName;
            if (is_null($value)) {
                continue;
            }
            if (!is_null($transform)) {
                /** @var mixed */
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if ($important && !$importantChange) {
                $importantChange = ($value != $object->$objectFieldName);
            }
            $object->$objectFieldName = $value;
        }
        return $importantChange;
    }
}
