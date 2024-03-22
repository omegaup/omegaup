<?php

namespace OmegaUp;

class CourseParams {
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
        if (!is_null($params['level'])) {
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
        if (!is_null($params['admission_mode'])) {
            \OmegaUp\Validators::validateInEnum(
                $params['admission_mode'],
                'admission_mode',
                [
                    \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PRIVATE,
                    \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_REGISTRATION,
                    \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PUBLIC,
                ]
            );
        }
        if (!is_null($params['requests_user_information'])) {
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
                array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
            )
            );
        }

        if (
            !is_null($params['finish_time']) &&
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

        $this->unlimitedDuration = is_null($this->finishTime);
        $this->public = $this->admissionMode === \OmegaUp\CourseParams::COURSE_ADMISSION_MODE_PUBLIC;
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
