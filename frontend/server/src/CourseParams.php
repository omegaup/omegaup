<?php

namespace OmegaUp;

class CourseParams extends BaseParams {
    // Constants for course level
    const COURSE_LEVEL_INTRODUCTORY = 'introductory';
    const COURSE_LEVEL_INTERMEDIATE = 'intermediate';
    const COURSE_LEVEL_ADVANCED = 'advanced';

    // Constants for admission mode.
    const COURSE_ADMISSION_MODE_PRIVATE = 'private';
    const COURSE_ADMISSION_MODE_REGISTRATION = 'registration';
    const COURSE_ADMISSION_MODE_PUBLIC = 'public';

    // Request user information constants.
    const COURSE_REQUEST_USER_INFORMATION_NO = 'no';
    const COURSE_REQUEST_USER_INFORMATION_OPTIONAL = 'optional';
    const COURSE_REQUEST_USER_INFORMATION_REQUIRED = 'required';

    const VALID_ADMISSION_MODES = [
        self::COURSE_ADMISSION_MODE_PRIVATE,
        self::COURSE_ADMISSION_MODE_REGISTRATION,
        self::COURSE_ADMISSION_MODE_PUBLIC,
    ];

    /**
     * @readonly
     * @var string
     */
    public $courseAlias;

    /**
     * @readonly
     * @var string
     */
    public $name;

    /**
     * @readonly
     * @var string
     */
    public $description;

    /**
     * @readonly
     * @var null|string
     */
    public $objective;

    /**
     * @readonly
     * @var null|\OmegaUp\CourseParams::COURSE_LEVEL_INTRODUCTORY|\OmegaUp\CourseParams::COURSE_LEVEL_INTERMEDIATE|\OmegaUp\CourseParams::COURSE_LEVEL_ADVANCED
     */
    public $level;

    /**
     * @readonly
     * @var int
     */
    public $startTime;

    /**
     * @readonly
     * @var null|int
     */
    public $finishTime;

    /**
     * @readonly
     * @var \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PRIVATE|\OmegaUp\CourseParams::COURSE_ADMISSION_MODE_REGISTRATION|\OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PUBLIC
     */
    public $admissionMode;

    /**
     * @readonly
     * @var int|null
     */
    public $schoolId;

    /**
     * @readonly
     * @var bool
     */
    public $needsBasicInformation;

    /**
     * @readonly
     * @var \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_NO|\OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_OPTIONAL|\OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_REQUIRED
     */
    public $requestsUserInformation;

    /**
     * @readonly
     * @var bool
     */
    public $showScoreboard;

    /**
     * @readonly
     * @var null|list<string>
     */
    public $languages;

    /**
     * @readonly
     * @var bool
     */
    public $archived;

    /**
     * @readonly
     * @var int|null
     */
    public $minimumProgressForCertificate;

    /**
     * @readonly
     * @var bool
     */
    public $unlimitedDuration;

    /**
     * @readonly
     * @var bool
     */
    public $public;

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @param array{name: string, description: string, objective: null|string, alias: string, level: null|\OmegaUp\CourseParams::COURSE_LEVEL_INTRODUCTORY|\OmegaUp\CourseParams::COURSE_LEVEL_INTERMEDIATE|\OmegaUp\CourseParams::COURSE_LEVEL_ADVANCED, start_time: int, finish_time: null|int, admission_mode: null|\OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PRIVATE|\OmegaUp\CourseParams::COURSE_ADMISSION_MODE_REGISTRATION|\OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PUBLIC, school_id: int|null, needs_basic_information: bool, requests_user_information: \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_NO|\OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_OPTIONAL|\OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_REQUIRED, show_scoreboard: bool, languages: null|string, archived: bool|null, minimum_progress_for_certificate: int|null} $params
     */
    public function __construct($params) {
        if ($params['level'] !== null) {
            \OmegaUp\Validators::validateInEnum(
                $params['level'],
                'level',
                [
                    \OmegaUp\CourseParams::COURSE_LEVEL_INTRODUCTORY,
                    \OmegaUp\CourseParams::COURSE_LEVEL_INTERMEDIATE,
                    \OmegaUp\CourseParams::COURSE_LEVEL_ADVANCED,
                ]
            );
        }
        if ($params['admission_mode'] !== null) {
            \OmegaUp\Validators::validateInEnum(
                $params['admission_mode'],
                'admission_mode',
                \OmegaUp\CourseParams::VALID_ADMISSION_MODES
            );
        }
        if ($params['requests_user_information'] !== null) {
            \OmegaUp\Validators::validateInEnum(
                $params['requests_user_information'],
                'requests_user_information',
                [
                    \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_NO,
                    \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_OPTIONAL,
                    \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_REQUIRED,
                ]
            );
        }

        $languages = $params['languages'] !== null ? explode(
            ',',
            $params['languages']
        ) : null;
        if ($languages !== null) {
            \OmegaUp\Validators::validateValidSubset(
                $languages,
                'languages',
                array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES())
            );
        }

        if (
            $params['finish_time'] !== null &&
            $params['start_time'] > $params['finish_time']
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        $this->courseAlias = $params['alias'];
        $this->name = $params['name'];
        $this->description = $params['description'];
        $this->languages = $languages;
        $this->objective = $params['objective'] ?? null;
        $this->level = $params['level'] ?? null;
        $this->startTime = $params['start_time'];
        $this->finishTime = $params['finish_time'] ?? null;
        $this->admissionMode = $params['admission_mode'] ?? \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PRIVATE;
        $this->schoolId = $params['school_id'] ?? null;
        $this->needsBasicInformation = $params['needs_basic_information'];
        $this->requestsUserInformation = $params['requests_user_information'] ?? \OmegaUp\CourseParams::COURSE_REQUEST_USER_INFORMATION_NO;
        $this->archived = $params['archived'] ?? false;
        $this->showScoreboard = $params['show_scoreboard'] ?? false;
        $this->minimumProgressForCertificate = $params['minimum_progress_for_certificate'] ?? null;

        $this->unlimitedDuration = $this->finishTime === null;
        $this->public = $this->admissionMode === \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PUBLIC;
    }
}
