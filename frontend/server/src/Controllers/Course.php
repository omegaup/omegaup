<?php

namespace OmegaUp\Controllers;

/**
 *  CourseController
 *
 * @psalm-type Progress=array{score: float, max_score: float}
 * @psalm-type AssignmentProgress=array<string, Progress>
 * @psalm-type ProblemQualityPayload=array{canNominateProblem: bool, dismissed: bool, dismissedBeforeAC: bool, language?: string, nominated: bool, nominatedBeforeAC: bool, problemAlias: string, solved: bool, tried: bool}
 * @psalm-type ProblemsetProblem=array{accepts_submissions: bool, accepted: int, alias: string, commit: string, difficulty: float, input_limit: int, languages: string, letter: string, order: int, points: float, quality_payload?: ProblemQualityPayload, quality_seal: bool, submissions: int, title: string, version: string, visibility: int, visits: int}
 * @psalm-type IdentityRequest=array{accepted: bool|null, admin?: array{name: null|string, username: string}, country: null|string, country_id: null|string, last_update: \OmegaUp\Timestamp|null, request_time: \OmegaUp\Timestamp, username: string}
 * @psalm-type CourseAdmin=array{role: string, username: string}
 * @psalm-type CourseGroupAdmin=array{alias: string, name: string, role: string}
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, max_points: float, name: string, order: int, problemset_id: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CourseDetails=array{admission_mode?: string, alias: string, assignments?: list<CourseAssignment>, description: string, finish_time: \OmegaUp\Timestamp|null, is_admin: bool, is_curator: bool, name: string, needs_basic_information: bool, requests_user_information: string, school_id: int|null, school_name: null|string, show_scoreboard: bool, start_time: \OmegaUp\Timestamp, student_count?: int, unlimited_duration: bool}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type Run=array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int, type: null|string, username: string, classname: string, alias: string, country: string, contest_alias: null|string}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: RunMetadata, name: null|string, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: RunMetadata}>}>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
 * @psalm-type FilteredCourse=array{accept_teacher: bool|null, admission_mode: string, alias: string, assignments: list<CourseAssignment>, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, is_open: bool, name: string, progress?: float, school_name: null|string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CoursesList=array{admin: list<FilteredCourse>, public: list<FilteredCourse>, student: list<FilteredCourse>}
 * @psalm-type CourseDetailsPayload=array{details: CourseDetails, progress: AssignmentProgress}
 * @psalm-type CoursesByTimeType=array{courses: list<FilteredCourse>, timeType: string}
 * @psalm-type CoursesByAccessMode=array{accessMode: string, activeTab: string, filteredCourses: array{current: CoursesByTimeType, past: CoursesByTimeType}}
 * @psalm-type CourseProblemTried=array{alias: string, title: string, username: string}
 * @psalm-type CourseSubmissionsListPayload=array{solvedProblems: array<string, list<CourseProblemTried>>, unsolvedProblems: array<string, list<CourseProblemTried>>}
 * @psalm-type AdminCourses=array{admin: CoursesByAccessMode}
 * @psalm-type StudentCourses=array<string, CoursesByAccessMode>
 * @psalm-type CourseListMinePayload=array{courses: AdminCourses}
 * @psalm-type CourseListPayload=array{course_type: null|string, courses: StudentCourses}
 * @psalm-type CourseProblemStatistics=array{assignment_alias: string, average: float|null, avg_runs: float|null, high_score_percentage: float|null, low_score_percentage: float|null, max_points: float, maximum: float|null, minimum: float|null, problem_alias: string, variance: float|null}
 * @psalm-type CourseStatisticsPayload=array{course: CourseDetails, problemStats: list<CourseProblemStatistics>}
 * @psalm-type CourseStudent=array{name: null|string, username: string}
 * @psalm-type StudentProgress=array{name: string|null, progress: array<string, array<string, float>>, username: string}
 * @psalm-type CourseNewPayload=array{is_curator: bool, is_admin: bool}
 * @psalm-type CourseEditPayload=array{admins: list<CourseAdmin>, assignmentProblems: list<ProblemsetProblem>, course: CourseDetails, groupsAdmins: list<CourseGroupAdmin>, identityRequests: list<IdentityRequest>, selectedAssignment: CourseAssignment|null, students: list<CourseStudent>, tags: list<string>}
 * @psalm-type CourseAssignmentEditPayload=array{course: CourseDetails, assignment: CourseAssignment|null}
 * @psalm-type StudentProgressPayload=array{course: CourseDetails, students: list<StudentProgress>, student: string}
 * @psalm-type StudentsProgressPayload=array{course: CourseDetails, students: list<StudentProgress>}
 * @psalm-type CourseProblem=array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, submissions: int, title: string, version: string, visibility: int, visits: int, runs: list<array{guid: string, language: string, source?: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int}>}
 * @psalm-type IntroDetailsPayload=array{details: CourseDetails, progress?: AssignmentProgress, shouldShowFirstAssociatedIdentityRunWarning: bool}
 * @psalm-type AddedProblem=array{alias: string, commit?: string, points: float}
 */
class Course extends \OmegaUp\Controllers\Controller {
    // Admision mode constants
    const ADMISSION_MODE_PUBLIC = 'public';
    const ADMISSION_MODE_PRIVATE = 'private';
    const ADMISSION_MODE_REGISTRATION = 'registration';

    // Number of rows shown in course list
    const PAGE_SIZE = 100;

    /**
     * Validate assignment_alias existis into the course and
     * return Assignments object
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param string $assignmentAlias
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseAssignmentAlias(
        \OmegaUp\DAO\VO\Courses $course,
        string $assignmentAlias
    ): \OmegaUp\DAO\VO\Assignments {
        $assignment = \OmegaUp\DAO\Courses::getAssignmentByAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        return $assignment;
    }

    /**
     * Validates request for creating a new Assignment
     *
     * @return array{addedProblems: list<AddedProblem>}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param 'homework'|'lesson'|'test' $assignment_type
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $problems
     * @omegaup-request-param OmegaUp\Timestamp $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateCreateAssignment(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        /** @var \OmegaUp\Timestamp */
        $courseStartTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $course->start_time
        );
        $courseFinishTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $course->finish_time
        );

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['name'],
            'name'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['description'],
            'description'
        );

        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;
        [
            'startTime' => $startTime,
            'finishTime' => $finishTime,
        ] = self::validateAssignmentDates(
            $r,
            $unlimitedDuration,
            $courseStartTime,
            $courseFinishTime
        );

        if (
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        $r->ensureEnum(
            'assignment_type',
            ['test', 'lesson', 'homework']
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['alias'],
            'alias',
            /*$isRequired=*/true
        );

        $addedProblems = [];
        if (!empty($r['problems'])) {
          /** @var list<array{alias: string, commit?: string, points?: int|float|string}> */
            $problemsData = json_decode(
                strval(
                    $r['problems']
                ), /*$assoc=*/
                true
            );
            foreach ($problemsData as $problemData) {
                if (!isset($problemData['alias'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterEmpty',
                        'problems'
                    );
                }

                if (
                    is_null(
                        \OmegaUp\DAO\Problems::getByAlias(
                            $problemData['alias']
                        )
                    )
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemNotFound'
                    );
                }
                if (!empty($problemData['points'])) {
                    \OmegaUp\Validators::validateNumber(
                        $problemData['points'],
                        'points'
                    );
                    $problemData['points'] = floatval($problemData['points']);
                } else {
                    $problemData['points'] = 100.0;
                }

                $addedProblems[] = $problemData;
            }
        }

        return ['addedProblems' => $addedProblems];
    }

    /**
     * @return array{finishTime: \OmegaUp\Timestamp|null, startTime: \OmegaUp\Timestamp}
     *
     * @omegaup-request-param OmegaUp\Timestamp $start_time
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     */
    private static function validateAssignmentDates(
        \OmegaUp\Request $r,
        bool $unlimitedDuration,
        \OmegaUp\Timestamp $courseStartTime,
        ?\OmegaUp\Timestamp $courseFinishTime
    ): array {
        $startTime = $r->ensureTimestamp(
            'start_time',
            /*$startTime=*/null,
            is_null($courseFinishTime) ? null : $courseFinishTime->time
        );
        if ($startTime->time < $courseStartTime->time) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentStartDateBeforeCourseStartDate',
                'start_time'
            );
        }
        if ($unlimitedDuration && !is_null($courseFinishTime)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseDoesNotHaveUnlimitedDuration',
                'unlimited_duration'
            );
        }

        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            null,
            is_null($courseFinishTime) ? null : $courseFinishTime->time,
            /*$isRequired=*/!is_null($courseFinishTime) || !$unlimitedDuration
        );
        if (
            !is_null($finishTime)
            && $finishTime->time < $courseStartTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseAssignmentEndDateBeforeCourseStartDate',
                'finish_time'
            );
        }

        return ['startTime' => $startTime, 'finishTime' => $finishTime];
    }

    /**
     * Validates clone Courses
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $name
     * @omegaup-request-param int $start_time
     */
    private static function validateClone(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course
    ): void {
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        $r->ensureInt('start_time');
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        if (
            is_null($r->identity)
            || (
                !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
                && $course->admission_mode !== self::ADMISSION_MODE_PUBLIC
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Validates create Courses
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param mixed $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param mixed $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateCreate(
        \OmegaUp\Request $r
    ): void {
        self::validateBasicCreateOrUpdate($r);

        if (
            !is_null($r['finish_time']) &&
            $r['start_time'] > $r['finish_time']
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }
    }

    /**
     * Validates update Courses
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateUpdate(
        \OmegaUp\Request $r,
        string $courseAlias
    ): \OmegaUp\DAO\VO\Courses {
        self::validateBasicCreateOrUpdate($r, true /*is update*/);

        // Get the actual start and finish time of the course, considering that
        // in case of update, parameters can be optional.
        $originalCourse = self::validateCourseExists($courseAlias);

        $startTime = $r->ensureOptionalTimestamp(
            'start_time'
        ) ?? $originalCourse->start_time;
        $finishTime = $r->ensureOptionalTimestamp('finish_time');
        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;

        if (
            !$unlimitedDuration &&
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        return $originalCourse;
    }

    /**
     * Validates basic information of a course
     *
     * @param \OmegaUp\Request $r
     * @param bool $isUpdate
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param int|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param int $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateBasicCreateOrUpdate(
        \OmegaUp\Request $r,
        bool $isUpdate = false
    ): void {
        $r->ensureMainUserIdentity();
        $isRequired = !$isUpdate;

        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['name'],
            'name',
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['description'],
            'description',
            $isRequired
        );

        $r->ensureOptionalInt('start_time', null, null, !$isUpdate);
        $r->ensureOptionalInt(
            'finish_time',
            null,
            null,
            /* required */ (
                !$isUpdate &&
                !($r->ensureOptionalBool('unlimited_duration') ?? false)
            )
        );

        \OmegaUp\Validators::validateValidAlias(
            $r['alias'],
            'alias',
            $isRequired
        );

        // Show scoreboard, needs basic information and request user information are always optional
        $r->ensureOptionalBool('needs_basic_information');
        $r->ensureOptionalBool('show_scoreboard');
        $r->ensureOptionalEnum(
            'requests_user_information',
            ['no', 'optional', 'required']
        );
        $r->ensureOptionalInt('school_id');

        if (is_null($r['school_id'])) {
            $school = null;
        } else {
            $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'schoolNotFound'
                );
            }
        }

        $r->ensureOptionalEnum(
            'admission_mode',
            [
                self::ADMISSION_MODE_PUBLIC,
                self::ADMISSION_MODE_REGISTRATION,
                self::ADMISSION_MODE_PRIVATE,
            ]
        );

        if (
            is_null($r['admission_mode']) ||
            $r['admission_mode'] !== self::ADMISSION_MODE_PUBLIC
        ) {
            return;
        }

        // Only curator can set public
        if (
            !\OmegaUp\Authorization::canCreatePublicCourse($r->identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects course alias, returns
     * course. Throws if not found.
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseExists(string $courseAlias): \OmegaUp\DAO\VO\Courses {
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        return $course;
    }

    /**
     * Gets the Group assigned to the Course.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function resolveGroup(
        \OmegaUp\DAO\VO\Courses $course
    ): \OmegaUp\DAO\VO\Groups {
        if (is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseGroupNotFound'
            );
        }
        return $group;
    }

    /**
     * @return array{token: string}
     *
     * @omegaup-request-param string $course_alias
     */
    public static function apiGenerateTokenForCloneCourse(
        \OmegaUp\Request $r
    ): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        $courseAlias = $r->ensureString(
            'course_alias',
            fn (string $courseAlias) => \OmegaUp\Validators::stringNonEmpty(
                $courseAlias
            )
        );
        $course = self::validateCourseExists($courseAlias);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if ($course->admission_mode === \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'unnecessaryTokenForPublicCourses'
            );
        }

        $claims = [
            'course' => strval($course->alias),
            'permissions' => 'clone',
        ];
        $subject = strval($r->user->user_id);
        return [
            'token' => \OmegaUp\SecurityTools::getCourseCloneAuthorizationToken(
                $claims,
                $subject
            ),
        ];
    }

    /**
     * Clone a course
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{alias: string}
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $name
     * @omegaup-request-param OmegaUp\Timestamp $start_time
     */
    public static function apiClone(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $originalCourse = self::validateCourseExists($r['course_alias']);
        self::validateClone($r, $originalCourse);

        $startTime = $r->ensureTimestamp('start_time');
        $offset = $startTime->time - $originalCourse->start_time->time;

        $cloneCourseFinishTime = null;
        if (!is_null($originalCourse->finish_time)) {
            $cloneCourseFinishTime = new \OmegaUp\Timestamp(
                $originalCourse->finish_time->time + $offset
            );
        }

        \OmegaUp\DAO\DAO::transBegin();
        $course = null;

        try {
            // Create the course (and group)
            $course = \OmegaUp\Controllers\Course::createCourseAndGroup(
                new \OmegaUp\DAO\VO\Courses([
                    'name' => $r['name'],
                    'description' => $originalCourse->description,
                    'alias' => $r['alias'],
                    'school_id' => $originalCourse->school_id,
                    'start_time' => $startTime,
                    'finish_time' => $cloneCourseFinishTime,
                    'admission_mode' => self::ADMISSION_MODE_PRIVATE,
                    'show_scoreboard' => $originalCourse->show_scoreboard,
                    'needs_basic_information' => $originalCourse->needs_basic_information,
                    'requests_user_information' => $originalCourse->requests_user_information
                ]),
                $r->user
            );

            $assignmentsProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsAssignmentByCourseAlias(
                $originalCourse
            );

            foreach ($assignmentsProblems as $assignment => $assignmentProblems) {
                // Create and assign homeworks, lessons and tests to new course
                $problemset = self::createAssignment(
                    $originalCourse,
                    new \OmegaUp\DAO\VO\Assignments([
                        'course_id' => $course->course_id,
                        'acl_id' => $course->acl_id,
                        'name' => $assignmentProblems['name'],
                        'description' => $assignmentProblems['description'],
                        'alias' => $assignmentProblems['assignment_alias'],
                        'publish_time_delay' => $assignmentProblems['publish_time_delay'],
                        'assignment_type' => $assignmentProblems['assignment_type'],
                        'start_time' => new \OmegaUp\Timestamp(
                            $assignmentProblems['start_time']->time +
                            $offset
                        ),
                        'finish_time' => (
                            is_null($assignmentProblems['finish_time']) ?
                            null :
                            new \OmegaUp\Timestamp(
                                $assignmentProblems['finish_time']->time +
                                $offset
                            )
                        ),
                        'order' => $assignmentProblems['order'],
                        'max_points' => $assignmentProblems['max_points'],
                    ]),
                    $r->identity
                );
                if (is_null($problemset->problemset_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }

                foreach ($assignmentProblems['problems'] as $problem) {
                    // Create and assign problems to new course
                    self::addProblemToAssignment(
                        $problem['problem_alias'],
                        $problemset->problemset_id,
                        $r->identity,
                        false, // visbility mode validation no needed when it is a clone
                        100,
                        null,
                        1
                    );
                }
            }
            \OmegaUp\DAO\DAO::transEnd();
            $result = 'success';
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            $result = 'unknown';
            throw $e;
        } finally {
            \OmegaUp\DAO\CourseCloneLog::create(
                new \OmegaUp\DAO\VO\CourseCloneLog([
                    'ip' => strval($_SERVER['REMOTE_ADDR']),
                    'course_id' => $originalCourse->course_id,
                    'new_course_id' => !is_null(
                        $course
                    ) ? $course->course_id : null,
                    'token_payload' => '',
                    'timestamp' => \OmegaUp\Time::get(),
                    'user_id' => $r->user->user_id,
                    'result' => $result,
                ])
            );
        }

        return [
            'alias' => strval($r['alias'])
        ];
    }

    /**
     * Create new course API
     *
     * @return array{status: string}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @omegaup-request-param mixed $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param mixed $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param mixed $needs_basic_information
     * @omegaup-request-param mixed $public
     * @omegaup-request-param mixed $requests_user_information
     * @omegaup-request-param mixed $school_id
     * @omegaup-request-param mixed $show_scoreboard
     * @omegaup-request-param mixed $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        if (isset($r['public'])) {
            $r['admission_mode'] = boolval(
                $r['public']
            ) ? self::ADMISSION_MODE_PUBLIC : self::ADMISSION_MODE_PRIVATE;
        }
        self::validateCreate($r);

        self::createCourseAndGroup(new \OmegaUp\DAO\VO\Courses([
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'school_id' => $r['school_id'],
            'start_time' => $r['start_time'],
            'finish_time' => $r['finish_time'],
            'admission_mode' => $r['admission_mode'] ?: self::ADMISSION_MODE_PRIVATE,
            'show_scoreboard' => $r['show_scoreboard'],
            'needs_basic_information' => $r['needs_basic_information'],
            'requests_user_information' => $r['requests_user_information'],
        ]), $r->user);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Function to create a new course with its corresponding group
     */
    private static function createCourseAndGroup(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Users $creator
    ): \OmegaUp\DAO\VO\Courses {
        if (is_null($course->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        if (is_null($creator->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        if (!is_null(\OmegaUp\DAO\Courses::getByAlias($course->alias))) {
                $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse'
                );
                $exception->addCustomMessageToArray('parameter', 'alias');
                throw $exception;
        }

        \OmegaUp\DAO\DAO::transBegin();

        $group = \OmegaUp\Controllers\Group::createGroup(
            $course->alias,
            "students-{$course->alias}",
            "students-{$course->alias}",
            $creator->user_id
        );

        try {
            $acl = new \OmegaUp\DAO\VO\ACLs(['owner_id' => $creator->user_id]);
            \OmegaUp\DAO\ACLs::create($acl);

            \OmegaUp\DAO\GroupRoles::create(new \OmegaUp\DAO\VO\GroupRoles([
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ]));

            $course->group_id = $group->group_id;
            $course->acl_id = $acl->acl_id;

            \OmegaUp\DAO\Courses::create($course);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'titleInUse',
                    $e
                );
            }
            throw $e;
        }
        return $course;
    }

    /**
     * Function to create a new assignment
     *
     * @param list<AddedProblem> $addedProblems
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    private static function createAssignment(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Assignments $assignment,
        \OmegaUp\DAO\VO\Identities $identity,
        array $addedProblems = []
    ): \OmegaUp\DAO\VO\Problemsets {
        if (is_null($assignment->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }
        \OmegaUp\DAO\DAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new \OmegaUp\DAO\VO\Problemsets([
                'acl_id' => $assignment->acl_id,
                'type' => 'Assignment',
                'scoreboard_url' => \OmegaUp\SecurityTools::randomString(30),
                'scoreboard_url_admin' => \OmegaUp\SecurityTools::randomString(
                    30
                ),
            ]);

            \OmegaUp\DAO\Problemsets::create($problemset);
            $assignment->problemset_id = $problemset->problemset_id;

            \OmegaUp\DAO\Assignments::create($assignment);

            // Update assignment_id in problemset object
            $problemset->assignment_id = $assignment->assignment_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            if (is_null($problemset->problemset_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            if (!empty($addedProblems)) {
                foreach ($addedProblems as $i => $addedProblem) {
                    // Create and assign problems to new course
                    self::addProblemToAssignment(
                        $addedProblem['alias'],
                        $problemset->problemset_id,
                        $identity,
                        /*$validateVisibility=*/false,
                        /*$points=*/$addedProblem['points'],
                        $addedProblem['commit'] ?? null,
                        /*$order*/$i + 1
                    );
                }

                \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
                    $course,
                    $assignment->alias
                );
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                $exception = new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse'
                );
                $exception->addCustomMessageToArray('parameter', 'alias');
                throw $exception;
            }
            throw $e;
        }
        return $problemset;
    }

    /**
     * Function to add problems to a specific assignment
     *
     * @param string $problemAlias
     * @param int $problemsetId
     * @param int $userId
     * @param bool $validateVisibility validations no needed when it is a clone
     * @param ?string $commit
     * @param ?int $order = 1
     */
    private static function addProblemToAssignment(
        string $problemAlias,
        int $problemsetId,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $validateVisibility,
        ?float $points = 100,
        ?string $commit = null,
        ?int $order = 1
    ): void {
        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        [$masterCommit, $currentVersion] = \OmegaUp\Controllers\Problem::resolveCommit(
            $problem,
            $commit
        );

        $assignedPoints = $points ?? 100.0;
        \OmegaUp\Controllers\Problemset::addProblem(
            $problemsetId,
            $problem,
            $commit ?? $masterCommit,
            $currentVersion,
            $identity,
            $problem->languages === '' ? 0 : $assignedPoints,
            is_null($order) ? 1 : $order,
            $validateVisibility
        );
    }

    /**
     * API to Create an assignment
     *
     * @return array{status: string}
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $assignment_type
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param mixed $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param int|null $order
     * @omegaup-request-param mixed $problems
     * @omegaup-request-param mixed $publish_time_delay
     * @omegaup-request-param mixed $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiCreateAssignment(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $order = $r->ensureOptionalInt('order');
        $course = self::validateCourseExists($r['course_alias']);
        [
            'addedProblems' => $addedProblems,
        ] = self::validateCreateAssignment($r, $course);

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::createAssignment(
            $course,
            new \OmegaUp\DAO\VO\Assignments([
                'course_id' => $course->course_id,
                'acl_id' => $course->acl_id,
                'name' => $r['name'],
                'description' => $r['description'],
                'alias' => $r['alias'],
                'publish_time_delay' => $r['publish_time_delay'],
                'assignment_type' => $r['assignment_type'],
                'start_time' => $r['start_time'],
                'finish_time' => $r['finish_time'],
                'order' => $order ?? \OmegaUp\DAO\Assignments::getNextPositionOrder(
                    $course->course_id
                ),
            ]),
            $r->identity,
            $addedProblems
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Update an assignment
     *
     * @return array{status: 'ok'}
     *
     * @omegaup-request-param mixed $assignment
     * @omegaup-request-param mixed $course
     * @omegaup-request-param OmegaUp\Timestamp $finish_time
     * @omegaup-request-param OmegaUp\Timestamp $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiUpdateAssignment(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'course');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment'],
            'assignment'
        );
        [
            'course' => $course,
            'assignment' => $assignment
        ] = self::validateAssignmentDetails(
            $r['course'],
            $r['assignment'],
            $r->identity
        );
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (is_null($r['start_time'])) {
            $r['start_time'] = $assignment->start_time;
        }

        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;
        [
            'startTime' => $startTime,
            'finishTime' => $finishTime,
        ] = self::validateAssignmentDates(
            $r,
            $unlimitedDuration,
            $course->start_time,
            $course->finish_time
        );

        if (!is_null($finishTime) && $startTime->time > $finishTime->time) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime',
                'finish_time'
            );
        }

        // Prevent date changes if a course already has runs
        if ($startTime->time !== $assignment->start_time->time) {
            $runCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($assignment->problemset_id)
            );

            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'courseUpdateAlreadyHasRuns'
                );
            }
        }

        $valueProperties = [
            'name',
            'description',
            'start_time',
            'finish_time',
            'assignment_type',
        ];
        self::updateValueProperties($r, $assignment, $valueProperties);

        if (is_null($course->finish_time) && $unlimitedDuration) {
            $assignment->finish_time = null;
        }
        \OmegaUp\DAO\DAO::transBegin();
        try {
            \OmegaUp\DAO\Assignments::update($assignment);

            \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeAsFinishTime(
                $assignment
            );

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds a problem to an assignment
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $commit
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param float $points
     * @omegaup-request-param mixed $problem_alias
     *
     * @return array{status: 'ok'}
     */
    public static function apiAddProblem(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['commit'],
            'commit',
            1,
            40,
            false
        );
        self::addProblemToAssignment(
            $r['problem_alias'],
            $problemset->problemset_id,
            $r->identity,
            true, /* validateVisibility */
            $r->ensureOptionalFloat('points') ?? 100.0,
            $r['commit']
        );

        \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
            $course,
            $r['assignment_alias']
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $problems
     *
     * @return array{status: string}
     */
    public static function apiUpdateProblemsOrder(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problems'],
            'problems'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet) || is_null($problemSet->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        /** @var null|mixed */
        $rawAliases = json_decode($r['problems'], true);
        if (!is_array($rawAliases) || empty($rawAliases)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'problems'
            );
        }

        $aliases = [];
        /** @var mixed $alias */
        foreach ($rawAliases as $alias) {
            if (!is_string($alias)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'problems'
                );
            }
            $aliases[] = $alias;
        }

        \OmegaUp\DAO\DAO::transBegin();
        try {
            $order = 1;
            foreach ($aliases as $alias) {
                $currentProblem = \OmegaUp\DAO\Problems::getByAlias(
                    $alias
                );

                if (
                    is_null($currentProblem) ||
                    is_null($currentProblem->problem_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemNotFound'
                    );
                }

                \OmegaUp\DAO\ProblemsetProblems::updateProblemsOrder(
                    $problemSet->problemset_id,
                    $currentProblem->problem_id,
                    $order++
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @omegaup-request-param mixed $assignments
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{status: string}
     */
    public static function apiUpdateAssignmentsOrder(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignments'],
            'assignments'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        /** @var null|mixed */
        $rawAliases = json_decode($r['assignments'], true);
        if (!is_array($rawAliases) || empty($rawAliases)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'assignments'
            );
        }

        $aliases = [];
        /** @var mixed $alias */
        foreach ($rawAliases as $alias) {
            if (!is_string($alias)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'assignments'
                );
            }
            $aliases[] = $alias;
        }

        \OmegaUp\DAO\DAO::transBegin();
        try {
            $order = 1;
            foreach ($aliases as $alias) {
                $currentAssignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                    $alias,
                    $course->course_id
                );

                if (
                    empty($currentAssignment) ||
                    is_null($currentAssignment->assignment_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'assignmentNotFound'
                    );
                }

                \OmegaUp\DAO\Assignments::updateAssignmentsOrder(
                    $currentAssignment->assignment_id,
                    $order++
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $problem_alias
     *
     * @return array{identities: list<string>}
     */
    public static function apiGetProblemUsers(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem) || is_null($problem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $identities = \OmegaUp\DAO\Problems::getIdentitiesInGroupWhoAttemptedProblem(
            $course->group_id,
            $problem->problem_id
        );

        return [
            'identities' => $identities,
        ];
    }

    /**
     * Remove a problem from an assignment
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $problem_alias
     *
     * @return array{status: string}
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Delete the entry from the database.
        $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            $problemSet->problemset_id,
            $problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotPartOfAssignment'
            );
        }
        if (
            \OmegaUp\DAO\Submissions::countTotalRunsOfProblemInProblemset(
                intval($problem->problem_id),
                intval($problemSet->problemset_id)
            ) > 0 &&
            !\OmegaUp\Authorization::isSystemAdmin($r->identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblemWithSubmissions'
            );
        }
        \OmegaUp\DAO\ProblemsetProblems::delete($problemsetProblem);

        \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
            $course,
            $r['assignment_alias']
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * List course assignments
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{assignments: list<CourseAssignment>}
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiListAssignments(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $group = self::resolveGroup($course);

        // Only Course Admins or Group Members (students) can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Assignments::getSortedCourseAssignments(
            $course->course_id
        );

        $response = [
            'assignments' => [],
        ];
        $time = \OmegaUp\Time::get();
        foreach ($assignments as $assignment) {
            if (
                $assignment['start_time']->time > $time &&
                !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            ) {
                // Non-admins should not be able to see the assignments ahead
                // of time.
                continue;
            }
            $response['assignments'][] = $assignment;
        }

        return $response;
    }

    /**
     * Remove an assignment from a course
     *
     * @return array{status: string}
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     */
    public static function apiRemoveAssignment(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        [
            'course' => $course,
            'assignment' => $assignment,
        ] = self::validateAssignmentDetails(
            $r['course_alias'],
            $r['assignment_alias'],
            $r->identity
        );
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        $runCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
            intval($problemset->problemset_id)
        );

        if ($runCount > 0) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseUpdateAlreadyHasRuns'
            );
        }

        \OmegaUp\DAO\DAO::transBegin();

        try {
            \OmegaUp\DAO\Assignments::unlinkProblemset(
                $assignment,
                $problemset
            );

            \OmegaUp\DAO\ProblemsetAccessLog::removeAccessLogFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetIdentities::removeIdentitiesFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetProblemOpened::removeProblemOpenedFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\ProblemsetProblems::removeProblemsFromProblemset(
                $problemset->problemset_id
            );

            \OmegaUp\DAO\Assignments::delete($assignment);

            \OmegaUp\DAO\Problemsets::delete($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Converts a Course object into an array
     *
     * @return FilteredCourse
     */
    private static function convertCourseToArray(\OmegaUp\DAO\VO\Courses $course): array {
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $relevantColumns = [
            'admission_mode',
            'alias',
            'finish_time',
            'name',
            'start_time',
        ];
        /** @var array{admission_mode: string, alias: string, assignments: list<CourseAssignment>, finish_time: \OmegaUp\Timestamp|null, name: string, start_time: \OmegaUp\Timestamp} */
        $arr = $course->asFilteredArray($relevantColumns);
        $arr['assignments'] = [];
        $arr['school_name'] = null;
        $arr['accept_teacher'] = null;
        $arr['is_open'] = false;
        $arr['finish_time'] = $course->finish_time;

        $arr['counts'] = \OmegaUp\DAO\Assignments::getAssignmentCountsForCourse(
            $course->course_id
        );
        return $arr;
    }

    /**
     * Lists all the courses this user is associated with.
     *
     * Returns courses for which the current user is an admin and
     * for in which the user is a student.
     *
     * @return CoursesList
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     */
    public static function apiListCourses(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();

        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('page_size');

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        return self::getCoursesList($r->identity, $page, $pageSize);
    }

    /**
     * @param list<string> $courseTypes
     * @return CoursesList
     */
    private static function getCoursesList(
        \OmegaUp\DAO\VO\Identities $identity,
        int $page,
        int $pageSize,
        array $courseTypes = ['admin', 'student', 'public']
    ) {
        if (is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $response = ['admin' => [], 'student' => [], 'public' => []];

        if (in_array('admin', $courseTypes)) {
            // TODO(pablo): Cache
            // Courses the user is an admin for.
            if (\OmegaUp\Authorization::isSystemAdmin($identity)) {
                $adminCourses = \OmegaUp\DAO\Courses::getAll(
                    $page,
                    $pageSize,
                    'course_id',
                    'DESC'
                );
            } else {
                $adminCourses = \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
                    $identity->identity_id,
                    $page,
                    $pageSize
                );
            }
            foreach ($adminCourses as $course) {
                $response['admin'][] = \OmegaUp\Controllers\Course::convertCourseToArray(
                    $course
                );
            }
        }

        if (in_array('student', $courseTypes)) {
            $response['student'] = \OmegaUp\DAO\Courses::getCoursesForStudent(
                $identity->identity_id
            );
        }

        if (in_array('public', $courseTypes)) {
            $response['public'] = \OmegaUp\DAO\Courses::getPublicCourses();
        }

        return $response;
    }

    /**
     * Returns the list of requests made by participants who are interested to
     * join the course
     *
     * @omegaup-request-param string $course_alias
     *
     * @return array{users: list<IdentityRequest>}
     */
    public static function apiRequests(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );

        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $usersRequests =
            \OmegaUp\DAO\CourseIdentityRequest::getRequestsForCourseWithFirstAdmin(
                $course->course_id
            );

        return ['users' => $usersRequests];
    }

    /**
     * Stores the resolution given to a certain request made by a contestant
     * interested to join the course.
     *
     * @omegaup-request-param string $course_alias
     * @omegaup-request-param bool $resolution
     * @omegaup-request-param string $username
     *
     * @return array{status: string}
     */
    public static function apiArbitrateRequest(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );

        if (is_null($r['resolution'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = self::validateCourseExists($r['course_alias']);
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $targetIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $r['username']
        );
        if (is_null($targetIdentity) || is_null($targetIdentity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotExist'
            );
        }

        $request = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
            $targetIdentity->identity_id,
            $course->course_id
        );

        if (is_null($request)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'userNotInListOfRequests'
            );
        }

        $request->accepted = $r->ensureBool('resolution');
        $request->last_update = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        \OmegaUp\DAO\CourseIdentityRequest::update($request);

        // Save this action in the history
        \OmegaUp\DAO\CourseIdentityRequestHistory::create(
            new \OmegaUp\DAO\VO\CourseIdentityRequestHistory([
                'identity_id' => $request->identity_id,
                'course_id' => $course->course_id,
                'time' => $request->last_update,
                'admin_id' => intval($r->user->user_id),
                'accepted' => $request->accepted,
            ])
        );

        if (!is_null($targetIdentity->user_id)) {
            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $targetIdentity->user_id,
                    'contents' =>  json_encode(
                        [
                            'type' => (
                                $request->accepted ?
                                \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_ACCEPTED :
                                \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_REJECTED
                            ),
                            'body' => [
                                'localizationString' => (
                                    $request->accepted ?
                                    new \OmegaUp\TranslationString(
                                        'notificationCourseRegistrationAccepted'
                                    ) :
                                    new \OmegaUp\TranslationString(
                                        'notificationCourseRegistrationRejected'
                                    )
                                ),
                                'localizationParams' => [
                                    'courseName' => $course->name,
                                ],
                                'url' => "/course/{$course->alias}/",
                                'iconUrl' => '/media/info.png',
                            ],
                        ]
                    ),
                ])
            );
        }

        self::$log->info(
            "Arbitrated course for user, username={$targetIdentity->username}, state={$request->accepted}"
        );

        return ['status' => 'ok'];
    }

    /**
     * List students in a course
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{students: list<CourseStudent>}
     */
    public static function apiListStudents(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'students' => \OmegaUp\DAO\Courses::getStudentsInCourse(
                $course->course_id,
                $course->group_id
            ),
        ];
    }

    /**
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $usernameOrEmail
     *
     * @return array{problems: list<CourseProblem>}
     */
    public static function apiStudentProgress(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (
            is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
                $course->group_id,
                $resolvedIdentity->identity_id
            ))
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($assignment) || is_null($assignment->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $rawProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $assignment->problemset_id
        );
        $letter = 0;
        $problems = [];
        foreach ($rawProblems as $problem) {
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                intval($problem['problem_id']),
                intval($assignment->problemset_id),
                intval($resolvedIdentity->identity_id)
            );
            $problem['runs'] = [];
            foreach ($runsArray as $run) {
                try {
                    $run['source'] = \OmegaUp\Controllers\Submission::getSource(
                        $run['guid']
                    );
                } catch (\Exception $e) {
                    self::$log->error(
                        "Error fetching source for {$run['guid']}",
                        $e
                    );
                }
                $problem['runs'][] = $run;
            }
            unset($problem['problem_id']);
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $letter++
            );
            $problems[] = $problem;
        }

        return [
            'problems' => $problems,
        ];
    }

    /**
     * Returns details of a given course
     *
     * @omegaup-request-param mixed $alias
     *
     * @return array{assignments: AssignmentProgress}
     */
    public static function apiMyProgress(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['alias'], 'alias');
        $course = self::validateCourseExists($r['alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $group = self::resolveGroup($course);

        // Only Course Admins or Group Members (students) can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Courses::getAssignmentsProgress(
            $course->course_id,
            $r->identity->identity_id
        );

        return [
            'assignments' => $assignments,
        ];
    }

    /**
     * Add Student to Course.
     *
     * @omegaup-request-param bool|null $accept_teacher
     * @omegaup-request-param mixed $accept_teacher_git_object_id
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $privacy_git_object_id
     * @omegaup-request-param mixed $share_user_information
     * @omegaup-request-param mixed $statement_type
     * @omegaup-request-param mixed $usernameOrEmail
     *
     * @return array{status: string}
     */
    public static function apiAddStudent(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        $acceptTeacher = $r->ensureOptionalBool('accept_teacher');

        // Only course admins or users adding themselves when the course is public
        if (
            !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            && ($course->admission_mode !== self::ADMISSION_MODE_PUBLIC
            || $resolvedIdentity->identity_id !== $r->identity->identity_id)
            && $course->requests_user_information == 'no'
            && is_null($r['accept_teacher'])
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $groupIdentity = new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $resolvedIdentity->identity_id,
            'share_user_information' => $r['share_user_information'],
        ]);

        if (!is_null($acceptTeacher)) {
            $groupIdentity->accept_teacher = $acceptTeacher;
        }

        \OmegaUp\DAO\DAO::transBegin();

        try {
            // Only users adding themselves are saved in consent log
            if (
                $resolvedIdentity->identity_id === $r->identity->identity_id
                 && $course->requests_user_information !== 'no'
            ) {
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['privacy_git_object_id'],
                    'privacy_git_object_id'
                );
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['statement_type'],
                    'statement_type'
                );
                $privacyStatementId = \OmegaUp\DAO\PrivacyStatements::getId(
                    $r['privacy_git_object_id'],
                    $r['statement_type']
                );
                if (is_null($privacyStatementId)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'privacyStatementNotFound'
                    );
                }
                if (
                    !\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    )
                ) {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                } else {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::getId(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                }

                $groupIdentity->privacystatement_consent_id = $privacyStatementConsentId;
            }
            if (
                $resolvedIdentity->identity_id === $r->identity->identity_id
                 && !empty($r['accept_teacher'])
            ) {
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['accept_teacher_git_object_id'],
                    'accept_teacher_git_object_id'
                );
                $privacyStatementId = \OmegaUp\DAO\PrivacyStatements::getId(
                    $r['accept_teacher_git_object_id'],
                    'accept_teacher'
                );
                if (is_null($privacyStatementId)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'privacyStatementNotFound'
                    );
                }
                if (
                    !\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    )
                ) {
                    \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacyStatementId
                    );
                }
            }
            \OmegaUp\DAO\GroupsIdentities::replace($groupIdentity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Remove Student from Course
     *
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $usernameOrEmail
     *
     * @return array{status: string}
     */
    public static function apiRemoveStudent(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );

        if (
            is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
                $course->group_id,
                $resolvedIdentity->identity_id
            ))
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        \OmegaUp\DAO\GroupsIdentities::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $resolvedIdentity->identity_id,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Returns all course administrators
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{admins: list<array{role: string, username: string}>, group_admins: list<array{alias: string, name: string, role: string}>}
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'admins' => \OmegaUp\DAO\UserRoles::getCourseAdmins($course),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getCourseAdmins($course)
        ];
    }

    /**
     * Adds an admin to a course
     *
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $usernameOrEmail
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $resolvedUser = \OmegaUp\Controllers\User::resolveUser(
            $r['usernameOrEmail']
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only director is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes an admin from a course
     *
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $usernameOrEmail
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($resolvedIdentity->user_id)) {
            // Unassociated identities can't be course admins
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $resolvedUser = \OmegaUp\DAO\Users::getByPK($resolvedIdentity->user_id);
        if (is_null($resolvedUser)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $resolvedIdentity,
                $course
            )
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser(
            intval($course->acl_id),
            intval($resolvedUser->user_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds an group admin to a course
     *
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $group
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['group'], 'group');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admins are allowed to modify course
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addGroup(
            intval($course->acl_id),
            intval($group->group_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes a group admin from a course
     *
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $group
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['group'], 'group');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup(
            intval($course->acl_id),
            intval($group->group_id)
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Course not found or trying to directly access a private course.
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{name: string, description: string, alias: string, currentUsername: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, statements: array{privacy: array{markdown: null|string, gitObjectId: null|string, statementType: null|string}, acceptTeacher: array{gitObjectId: null|string, markdown: string, statementType: string}}, isFirstTimeAccess: bool, shouldShowResults: bool}
     */
    public static function apiIntroDetails(\OmegaUp\Request $r) {
        $introDetails = self::getIntroDetails($r);
        if (!isset($introDetails['smartyProperties']['coursePayload'])) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        return $introDetails['smartyProperties']['coursePayload'];
    }

    /**
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{inContest: bool, smartyProperties: array{coursePayload?: array{alias: string, currentUsername: string, description: string, isFirstTimeAccess: bool, name: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, shouldShowResults: bool, statements: array{acceptTeacher: array{gitObjectId: null|string, markdown: string, statementType: string}, privacy: array{gitObjectId: null|string, markdown: null|string, statementType: null|string}}, userRegistrationAccepted?: bool|null, userRegistrationAnswered?: bool, userRegistrationRequested?: bool}, payload?: IntroDetailsPayload, showRanking?: bool}, template: string}|array{smartyProperties: array{payload: CourseDetailsPayload, title: string}, entrypoint: string}
     */
    public static function getCourseDetailsForSmarty(\OmegaUp\Request $r): array {
        return self::getIntroDetails($r);
    }

    /**
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param bool|null $is_practice
     *
     * @return array{inContest: bool, smartyProperties: array{payload: IntroDetailsPayload}, template: string}
     */
    public static function getCourseAdminDetailsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $isPractice = $r->ensureOptionalBool('is_practice') ?? false;
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $group = self::resolveGroup($course);
        if (
            !\OmegaUp\Authorization::isGroupAdmin($r->identity, $group)
            && !\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $r['assignment_alias']
        );
        return [
            'smartyProperties' => [
                'payload' => [
                    'shouldShowFirstAssociatedIdentityRunWarning' => false,
                    'details' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                ],
            ],
            'template' => 'arena.course.admin.tpl',
            'inContest' => !$isPractice,
        ];
    }

    /**
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseNewPayload, title:string}}
     */
    public static function getCourseNewDetailsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return [
            'smartyProperties' => [
                'payload' => [
                    'is_curator' => \OmegaUp\Authorization::canCreatePublicCourse(
                        $r->identity
                    ),
                    'is_admin' => true,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseNew'
                ),
            ],
            'entrypoint' => 'course_new',
        ];
    }

    /**
     * @omegaup-request-param mixed $course
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseEditPayload, title: string}}
     */
    public static function getCourseEditDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'alias');

        $courseEditDetails = self::getCourseEditDetails(
            $r['course'],
            $r->identity
        );

        if (!empty($courseEditDetails['course']['assignments'])) {
            $courseEditDetails['selectedAssignment'] = $courseEditDetails['course']['assignments'][0];
            $courseEditDetails['assignmentProblems'] = self::getProblemsByAssignment(
                $courseEditDetails['selectedAssignment']['alias'],
                $r['course'],
                $r->identity,
                $r->user
            );
        }

        return [
            'smartyProperties' => [
                'payload' => $courseEditDetails,
                'title' => new \OmegaUp\TranslationString('courseEdit'),
            ],
            'entrypoint' => 'course_edit',
        ];
    }

    /**
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseAssignmentEditPayload, title: string}}
     */
    public static function getCourseEditDetailsWithSelectedAssignmentForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'alias');
        \OmegaUp\Validators::validateValidAlias(
            $r['assignment_alias'],
            'assignment_alias'
        );

        [
            'course' => $courseEditDetails,
        ] = self::getCourseEditDetails(
            $r['course'],
            $r->identity
        );

        $assignment = null;
        if (!empty($courseEditDetails['assignments'])) {
            $assignment = array_filter(
                $courseEditDetails['assignments'],
                fn (array $assignment) => $assignment['alias'] === $r['assignment_alias']
            );
            $assignment = array_shift($assignment);
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'course' => $courseEditDetails,
                    'assignment' => $assignment,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'courseAssignmentEdit'
                ),
            ],
            'entrypoint' => 'course_assignment_edit',
        ];
    }

    /**
     * @return CourseEditPayload
     */
    private static function getCourseEditDetails(
        string $courseAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $course = self::validateCourseExists($courseAlias);
        if (is_null($course->alias)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        self::resolveGroup($course);

        if (!\OmegaUp\Authorization::isCourseAdmin($identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $admins = \OmegaUp\DAO\UserRoles::getCourseAdmins($course);
        foreach ($admins as &$admin) {
            unset($admin['user_id']);
        }

        return [
            'course' => self::getCommonCourseDetails($course, $identity),
            'assignmentProblems' => [],
            'selectedAssignment' => null,
            'tags' => [],
            'students' => \OmegaUp\DAO\Courses::getStudentsInCourse(
                intval($course->course_id),
                intval($course->group_id)
            ),
            'identityRequests' => \OmegaUp\DAO\CourseIdentityRequest::getRequestsForCourseWithFirstAdmin(
                intval($course->course_id)
            ),
            'admins' => $admins,
            'groupsAdmins' => \OmegaUp\DAO\GroupRoles::getCourseAdmins(
                $course
            ),
        ];
    }

    /**
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseSubmissionsListPayload, title: string}}
     *
     * @omegaup-request-param mixed $course
     */
    public static function getCourseSubmissionsListForSmarty(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course'],
            'course'
        );
        $course = self::validateCourseExists($r['course']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $r['course']
        );
        $userSolvedProblems = [];
        $userUnsolvedProblems = [];
        foreach ($usersProblems as $userProblem) {
            if ($userProblem['solved']) {
                $userSolvedProblems[$userProblem['username']][] = $userProblem;
            } else {
                $userUnsolvedProblems[$userProblem['username']][] = $userProblem;
            }
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'solvedProblems' => $userSolvedProblems,
                    'unsolvedProblems' => $userUnsolvedProblems,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'courseSubmissionsList'
                ),
            ],
            'entrypoint' => 'course_submissions_list',
        ];
    }

    /**
     * @omegaup-request-param mixed $course
     *
     * @return array{smartyProperties: array{payload: StudentsProgressPayload, title: string}, entrypoint: string}
     */
    public static function getStudentsInformationForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'course');

        $course = self::validateCourseExists($r['course']);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'students' => \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
                        $course->course_id,
                        $course->group_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleStudentsProgress'
                ),
            ],
            'entrypoint' => 'course_students'
        ];
    }

    /**
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $student
     *
     * @return array{smartyProperties: array{payload: StudentProgressPayload, title: string}, entrypoint: string}
     */
    public static function getStudentProgressForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'course');
        \OmegaUp\Validators::validateStringNonEmpty($r['student'], 'student');

        $course = self::validateCourseExists($r['course']);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    // TODO: Get progress only for the given student, rather than every student.
                    'students' => \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
                        $course->course_id,
                        $course->group_id
                    ),
                    'student' => $r['student']
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleStudentsProgress'
                ),
            ],
            'entrypoint' => 'course_student'
        ];
    }
     /**
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseListMinePayload, title: string}}
     */
    public static function getCourseMineDetailsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 1000;

        $courses = self::getCoursesList(
            $r->identity,
            $page,
            $pageSize,
            /*$courseTypes=*/['admin']
        );
        $filteredCourses = [
            'admin' => [
                'filteredCourses' => [
                    'current' => [
                        'courses' => [],
                        'timeType' => 'current',
                    ],
                    'past' => [
                        'courses' => [],
                        'timeType' => 'past',
                    ],
                ],
                'accessMode' => 'admin',
                'activeTab' => '',
            ]
        ];
        foreach ($courses['admin'] as $course) {
            if (
                is_null($course['finish_time'])
                || $course['finish_time']->time > \OmegaUp\Time::get()
            ) {
                $filteredCourses['admin']['filteredCourses']['current']['courses'][] = $course;
                $filteredCourses['admin']['activeTab'] = 'current';
            } else {
                $filteredCourses['admin']['filteredCourses']['past']['courses'][] = $course;
            }
        }
        if (
            $filteredCourses['admin']['activeTab'] === ''
            && !empty(
                $filteredCourses['admin']['filteredCourses']['past']['courses']
            )
        ) {
            $filteredCourses['admin']['activeTab'] = 'past';
        }
        return [
            'smartyProperties' => [
                'payload' => [
                    'courses' => $filteredCourses,
                ],
                'title' => new \OmegaUp\TranslationString('courseList'),
            ],
            'entrypoint' => 'course_mine',
        ];
    }

    /**
     * @omegaup-request-param 'student'|'public' $course_type
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseListPayload, title: string}}
     */
    public static function getCourseListDetailsForSmarty(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('page_size');

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        $courseType = $r->ensureEnum(
            'course_type',
            ['student', 'public']
        );

        $courses = self::getCoursesList(
            $r->identity,
            $page,
            $pageSize,
            [$courseType]
        );

        $filteredCourses = self::getFilteredCourses($courses, [$courseType]);

        return [
            'smartyProperties' => [
                'payload' => [
                    'courses' => $filteredCourses,
                    'course_type' => $courseType
                ],
                'title' => new \OmegaUp\TranslationString('courseList'),
            ],
            'entrypoint' => 'course_single_list',
        ];
    }

    /**
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     *
     * @return array{entrypoint: string, smartyProperties: array{payload: CourseListPayload, title: string}}
     */
    public static function getCourseSummaryListDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $coursesTypes = ['student', 'public'];
        // Check who is visiting, but a not logged user can still view
        // the list of courses
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Show only public courses for no-logged users
            $courses = [
                'admin' => [],
                'student' => [],
                'public' => \OmegaUp\DAO\Courses::getPublicCourses(),
            ];

            $filteredCourses = self::getFilteredCourses(
                $courses,
                $coursesTypes
            );

            return [
                'smartyProperties' => [
                    'payload' => [
                        'courses' => $filteredCourses,
                        'course_type' => null,
                    ],
                    'title' => new \OmegaUp\TranslationString('courseList'),
                ],
                'entrypoint' => 'course_list',
            ];
        }
        $page = $r->ensureOptionalInt('page') ?? 1;
        $pageSize = $r->ensureOptionalInt('page_size') ?? 1000;

        $courses = self::getCoursesList(
            $r->identity,
            $page,
            $pageSize,
            $coursesTypes
        );

        $courses['student'] = array_filter(
            $courses['student'],
            fn ($course) => (
                is_null($course['finish_time']) ||
                $course['finish_time']->time > \OmegaUp\Time::get()
            )
        );
        $courses['student'] = array_slice($courses['student'], 0, 5);

        // Checks whether a public course has been open already by user
        foreach ($courses['public'] as &$publicCourse) {
            $matchedCourses = array_values(
                array_filter(
                    $courses['student'],
                    fn ($course) => $course['alias'] === $publicCourse['alias']
                )
            );
            if (!empty($matchedCourses)) {
                $publicCourse['is_open'] = $matchedCourses[0]['is_open'];
            }
        }

        $filteredCourses = self::getFilteredCourses($courses, $coursesTypes);

        return [
            'smartyProperties' => [
                'payload' => [
                    'courses' => $filteredCourses,
                    'course_type' => null,
                ],
                'title' => new \OmegaUp\TranslationString('courseList'),
            ],
            'entrypoint' => 'course_list',
        ];
    }

    /**
     * @omegaup-request-param mixed $course
     *
     * @return array{smartyProperties: array{payload: CourseStatisticsPayload, title: string}, entrypoint: string}
     */
    public static function getCourseStatisticsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'course');

        $course = self::validateCourseExists($r['course']);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'smartyProperties' => [
                'payload' => [
                    'course' => self::getCommonCourseDetails(
                        $course,
                        $r->identity
                    ),
                    'problemStats' => \OmegaUp\DAO\Assignments::getAssignmentsProblemsStatistics(
                        $course->course_id,
                        $course->group_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseStatistics'
                ),
            ],
            'entrypoint' => 'course_statistics'
        ];
    }

    /**
     * @param CoursesList $courses
     * @param list<string> $coursesTypes
     *
     * @return StudentCourses
     */
    private static function getFilteredCourses($courses, $coursesTypes) {
        $filteredCourses = [];
        foreach ($coursesTypes as $courseType) {
            $filteredCourses[$courseType] = [
                'filteredCourses' => [
                    'current' => [
                        'courses' => [],
                        'timeType' => 'current',
                    ],
                    'past' => [
                        'courses' => [],
                        'timeType' => 'past',
                    ],
                ],
                'accessMode' => $courseType,
                'activeTab' => '',
            ];
            foreach ($courses[$courseType] as $course) {
                if (
                    is_null($course['finish_time'])
                    || $course['finish_time']->time > \OmegaUp\Time::get()
                ) {
                    $filteredCourses[$courseType]['filteredCourses']['current']['courses'][] = $course;
                    $filteredCourses[$courseType]['activeTab'] = 'current';
                } else {
                    $filteredCourses[$courseType]['filteredCourses']['past']['courses'][] = $course;
                }
            }
            if (
                $filteredCourses[$courseType]['activeTab'] === ''
                && !empty(
                    $filteredCourses[$courseType]['filteredCourses']['past']['courses']
                )
            ) {
                $filteredCourses[$courseType]['activeTab'] = 'past';
            }
        }

        return $filteredCourses;
    }

    /**
     * Refactor of apiIntroDetails in order to be called from php files and APIs
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{inContest: bool, smartyProperties: array{coursePayload?: array{alias: string, currentUsername: string, description: string, isFirstTimeAccess: bool, name: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, shouldShowResults: bool, statements: array{acceptTeacher: array{gitObjectId: null|string, markdown: string, statementType: string}, privacy: array{gitObjectId: null|string, markdown: null|string, statementType: null|string}}, userRegistrationAccepted?: bool|null, userRegistrationAnswered?: bool, userRegistrationRequested?: bool}, payload?: IntroDetailsPayload, showRanking?: bool}, template: string}|array{smartyProperties: array{payload: CourseDetailsPayload, title: string}, entrypoint: string}
     */
    public static function getIntroDetails(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $group = self::resolveGroup($course);
        $showAssignment = !empty($r['assignment_alias']);
        $shouldShowIntro = !\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        );
        $hasSharedUserInformation = true;
        $hasAcceptedTeacher = null;
        $registrationResponse = [];
        if (!\OmegaUp\Authorization::isGroupAdmin($r->identity, $group)) {
            [
                'share_user_information' => $hasSharedUserInformation,
                'accept_teacher' => $hasAcceptedTeacher,
            ] = \OmegaUp\DAO\Courses::getSharingInformation(
                $r->identity->identity_id,
                $course,
                $group
            );
        }
        if (
            $shouldShowIntro &&
            $course->admission_mode === self::ADMISSION_MODE_PRIVATE
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        if ($course->admission_mode === self::ADMISSION_MODE_REGISTRATION) {
            $registration = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
                $r->identity->identity_id,
                $course->course_id
            );

            $registrationResponse['userRegistrationRequested'] = !is_null(
                $registration
            );
            if (is_null($registration)) {
                $registrationResponse['userRegistrationAnswered'] = false;
            } else {
                $registrationResponse['userRegistrationAnswered'] = !is_null(
                    $registration->accepted
                );
                $registrationResponse['userRegistrationAccepted'] = $registration->accepted;
            }
        }

        $courseDetails = self::getBasicCourseDetails($course, $r->identity);
        $commonDetails = self::getCommonCourseDetails($course, $r->identity);

        $requestUserInformation = $courseDetails['requests_user_information'];

        $entrypointResponse = [
            'smartyProperties' => [
                'payload' => [
                    'shouldShowFirstAssociatedIdentityRunWarning' => false,
                    'details' => $commonDetails,
                    'progress' => \OmegaUp\DAO\Courses::getAssignmentsProgress(
                        $course->course_id,
                        $r->identity->identity_id
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCourseDetails'
                ),
            ],
            'entrypoint' => 'course_details',
        ];
        if (
            isset($commonDetails['is_admin'])
            && $commonDetails['is_admin']
            && !$showAssignment
        ) {
            return $entrypointResponse;
        }

        if ($showAssignment) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['assignment_alias'],
                'assignment_alias'
            );
            $assignment = self::validateCourseAssignmentAlias(
                $course,
                $r['assignment_alias']
            );
            return [
                'smartyProperties' => [
                    'showRanking' => \OmegaUp\Controllers\Course::shouldShowScoreboard(
                        $r->identity,
                        $course,
                        $group
                    ),
                    'payload' => ['shouldShowFirstAssociatedIdentityRunWarning' =>
                        !is_null($r->user) &&
                        !\OmegaUp\Controllers\User::isMainIdentity(
                            $r->user,
                            $r->identity
                        ) &&
                        \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                            $r->user
                        ),
                        'details' => $commonDetails,
                    ],
                ],
                'template' => 'arena.contest.course.tpl',
                // Navbar is only hidden during exams.
                'inContest' => $assignment->assignment_type === 'test',
            ];
        }

        if (
            $shouldShowIntro
            || is_null($hasAcceptedTeacher)
            || (!$hasSharedUserInformation
            && $requestUserInformation !== 'no'
            )
        ) {
            $needsBasicInformation = $courseDetails['needs_basic_information']
                && (!is_null($r->identity->country_id)
                || !is_null(
                    $r->identity->state_id
                ) || !is_null(
                    $r->identity->current_identity_school_id
                ));

            // Privacy Statement Information
            $privacyStatementMarkdown = \OmegaUp\PrivacyStatement::getForProblemset(
                $r->identity->language_id,
                'course',
                $requestUserInformation
            );

            $privacyStatement = [
                'markdown' => $privacyStatementMarkdown,
                'gitObjectId' => null,
                'statementType' => null,
            ];
            if (!is_null($privacyStatementMarkdown)) {
                $statementType = "course_{$requestUserInformation}_consent";
                $statement =
                    \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                        $statementType
                    );
                $privacyStatement['statementType'] = $statementType;
                if (!is_null($statement)) {
                    $privacyStatement['gitObjectId'] = $statement['git_object_id'];
                }
            }

            $markdown = \OmegaUp\PrivacyStatement::getForConsent(
                $r->identity->language_id,
                'accept_teacher'
            );
            $acceptTeacherStatement = [
                'markdown' => $markdown,
                'statementType' => 'accept_teacher',
                'gitObjectId' => null,
            ];
            $teacherStatement =
                \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                    'accept_teacher'
                );
            if (!is_null($teacherStatement)) {
                $acceptTeacherStatement['gitObjectId'] = $teacherStatement['git_object_id'];
            }
            return [
                'smartyProperties' => [
                    'coursePayload' => array_merge(
                        $registrationResponse,
                        [
                            'name' => $courseDetails['name'],
                            'description' => $courseDetails['description'],
                            'alias' => $courseDetails['alias'],
                            'currentUsername' => $r->identity->username,
                            'needsBasicInformation' => $needsBasicInformation,
                            'requestsUserInformation' =>
                                $courseDetails['requests_user_information'],
                            'shouldShowAcceptTeacher' => !$hasAcceptedTeacher,
                            'statements' => [
                                'privacy' => $privacyStatement,
                                'acceptTeacher' => $acceptTeacherStatement,
                            ],
                            'isFirstTimeAccess' => !$hasSharedUserInformation,
                            'shouldShowResults' => $shouldShowIntro,
                        ]
                    ),
                ],
                'template' => 'arena.course.intro.tpl',
                'inContest' => false,
            ];
        }

        return $entrypointResponse;
    }

    /**
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{status: string}
     */
    public static function apiRegisterForCourse(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );

        $course = self::validateCourseExists($r['course_alias']);

        if ($course->admission_mode !== self::ADMISSION_MODE_REGISTRATION) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'courseDoesNotAdmitRegistration'
            );
        }

        \OmegaUp\DAO\CourseIdentityRequest::create(
            new \OmegaUp\DAO\VO\CourseIdentityRequest([
                'identity_id' => $r->identity->identity_id,
                'course_id' => $course->course_id,
                'request_time' => \OmegaUp\Time::get(),
            ])
        );

        /** @var array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string} */
        foreach (
            \OmegaUp\DAO\UserRoles::getCourseAdmins(
                $course
            ) as $admin
        ) {
            if (empty($admin['user_id']) || $admin['role'] === 'site-admin') {
                continue;
            }

            \OmegaUp\DAO\Notifications::create(
                new \OmegaUp\DAO\VO\Notifications([
                    'user_id' => $admin['user_id'],
                    'contents' =>  json_encode(
                        [
                            'type' => \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_REQUEST,
                            'body' => [
                                'localizationString' => new \OmegaUp\TranslationString(
                                    'notificationCourseRegistrationRequest'
                                ),
                                'localizationParams' => [
                                    'username' => $r->identity->username,
                                    'courseName' => $course->name,
                                ],
                                'url' => "/course/{$course->alias}/edit/#students",
                                'iconUrl' => '/media/info.png',
                            ],
                        ]
                    ),
                ])
            );
        }
        return ['status' => 'ok'];
    }

    /**
     * Returns course basic details
     *
     * @return array{alias: string, needs_basic_information: bool, description: string, name: string, requests_user_information: string}
     */
    private static function getBasicCourseDetails(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        return [
            'name' => strval($course->name),
            'description' => strval($course->description),
            'alias' => strval($course->alias),
            'needs_basic_information' => $course->needs_basic_information,
            'requests_user_information' => $course->requests_user_information,
        ];
    }

    /**
     * Returns course details common between admin & non-admin
     *
     * @return CourseDetails
     */
    private static function getCommonCourseDetails(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($identity, $course);

        $result = [
            'assignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                strval($course->alias),
                $isAdmin
            ),
            'name' => strval($course->name),
            'description' => strval($course->description),
            'alias' => strval($course->alias),
            'school_id' => intval($course->school_id),
            'school_name' => null,
            'start_time' => $course->start_time,
            'finish_time' => $course->finish_time,
            'is_admin' => $isAdmin,
            'is_curator' => \OmegaUp\Authorization::canCreatePublicCourse(
                $identity
            ),
            'admission_mode' => $course->admission_mode,
            'needs_basic_information' => $course->needs_basic_information,
            'show_scoreboard' => boolval($course->show_scoreboard),
            'requests_user_information' => $course->requests_user_information,
            'unlimited_duration' => false,
        ];

        if ($isAdmin) {
            if (is_null($course->group_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseNotFound'
                );
            }
            $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
            if (is_null($group) || is_null($group->group_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'courseGroupNotFound'
                );
            }
            $result['student_count'] =
                \OmegaUp\DAO\GroupsIdentities::GetMemberCountById(
                    $group->group_id
                );
        }
        if (!is_null($course->school_id)) {
            $school = \OmegaUp\DAO\Schools::getByPK($course->school_id);
            if (!is_null($school)) {
                $result['school_name'] = $school->name;
                $result['school_id'] = $school->school_id;
            }
        }

        return $result;
    }

    /**
     * Returns all details of a given Course
     *
     * @omegaup-request-param mixed $alias
     *
     * @return CourseDetails
     */
    public static function apiAdminDetails(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['alias'], 'alias');
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity);
    }

    /**
     * Returns a report with all user activity for a course.
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{events: list<array{username: string, ip: int, time: \OmegaUp\Timestamp, classname?: string, alias?: string}>}
     */
    public static function apiActivityReport(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $accesses = \OmegaUp\DAO\ProblemsetAccessLog::getAccessForCourse(
            $course->course_id
        );
        $submissions = \OmegaUp\DAO\SubmissionLog::GetSubmissionsForCourse(
            $course->course_id
        );

        return [
            'events' => \OmegaUp\ActivityReport::getActivityReport(
                $accesses,
                $submissions
            ),
        ];
    }

    /**
     * Validates and authenticate token for operations when user can be logged
     * in or not. This is the only private function that receives Request as a
     * parameter because it needs authenticate it wheter there is no token.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, course: \OmegaUp\DAO\VO\Courses, courseAdmin: bool, courseAssignments: list<CourseAssignment>, hasToken: bool}
     */
    private static function authenticateAndValidateToken(
        string $courseAlias,
        string $assignmentAlias,
        ?string $token,
        \OmegaUp\Request $r
    ): array {
        if (is_null($token)) {
            $r->ensureIdentity();
            [
                'course' => $course,
                'assignment' => $assignment
            ] = self::validateAssignmentDetails(
                $courseAlias,
                $assignmentAlias,
                $r->identity
            );
            $isAdmin = \OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $course
            );

            return [
                'hasToken' => false,
                'courseAdmin' => $isAdmin,
                'courseAssignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                    strval($course->alias),
                    $isAdmin
                ),
                'assignment' => $assignment,
                'course' => $course,
            ];
        }

        $courseAdmin = false;

        $course = self::validateCourseExists($courseAlias);
        $assignment = self::validateCourseAssignmentAlias(
            $course,
            $assignmentAlias
        );
        if (is_null($assignment->assignment_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $assignmentProblemset = \OmegaUp\DAO\Assignments::getByIdWithScoreboardUrls(
            $assignment->assignment_id
        );
        if (is_null($assignmentProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if ($token === $assignmentProblemset['scoreboard_url_admin']) {
            $courseAdmin = true;
        } elseif ($token !== $assignmentProblemset['scoreboard_url']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'invalidScoreboardUrl'
            );
        }

        // hasToken is true, it means we do not autenticate request user
        return [
            'courseAdmin' => $courseAdmin,
            'courseAssignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                strval($course->alias),
                $courseAdmin
            ),
            'hasToken' => true,
            'assignment' => $assignment,
            'course' => $course,
        ];
    }

    /**
     * Validates assignment by course alias and assignment alias given
     *
     * @return array{course: \OmegaUp\DAO\VO\Courses, assignment: \OmegaUp\DAO\VO\Assignments}
     */
    private static function validateAssignmentDetails(
        ?string $courseAlias,
        ?string $assignmentAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        \OmegaUp\Validators::validateStringNonEmpty($courseAlias, 'course');
        \OmegaUp\Validators::validateStringNonEmpty(
            $assignmentAlias,
            'assignment'
        );
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course) || is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            intval($course->course_id)
        );
        if (is_null($assignment) || is_null($assignment->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        // Admins are almighty, no need to check anything else.
        if (\OmegaUp\Authorization::isCourseAdmin($identity, $course)) {
            return [
                'course' => $course,
                'assignment' => $assignment
            ];
        }

        if (
            $assignment->start_time->time > \OmegaUp\Time::get() ||
            !\OmegaUp\DAO\GroupRoles::isContestant(
                intval($identity->identity_id),
                $assignment->acl_id
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return [
            'course' => $course,
            'assignment' => $assignment
        ];
    }

    /**
     * Returns details of a given assignment
     *
     * @return array{admin: bool, alias: string, assignment_type: null|string, courseAssignments: list<CourseAssignment>, description: null|string, director: string, finish_time: \OmegaUp\Timestamp|null, name: string, problems: list<ProblemsetProblem>, problemset_id: int, start_time: \OmegaUp\Timestamp}
     *
     * @omegaup-request-param mixed $assignment
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $token
     */
    public static function apiAssignmentDetails(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course'],
            'course'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment'],
            'assignment'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        if (
            is_null($tokenAuthenticationResult['course']->acl_id)
            || is_null($tokenAuthenticationResult['course']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (
            is_null($tokenAuthenticationResult['assignment']->problemset_id)
            || is_null($tokenAuthenticationResult['assignment']->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $acl = \OmegaUp\DAO\ACLs::getByPK(
            $tokenAuthenticationResult['course']->acl_id
        );
        if (is_null($acl) || is_null($acl->owner_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        $director = \OmegaUp\DAO\Identities::findByUserId(
            intval(
                $acl->owner_id
            )
        );
        if (is_null($director)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            // Authenticate request
            $r->ensureIdentity();
            \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'ip' => ip2long(strval($_SERVER['REMOTE_ADDR'])),
            ]));
        }

        return [
            'name' => strval($tokenAuthenticationResult['assignment']->name),
            'alias' => strval($tokenAuthenticationResult['assignment']->alias),
            'description' => $tokenAuthenticationResult['assignment']->description,
            'assignment_type' => $tokenAuthenticationResult['assignment']->assignment_type,
            'start_time' => $tokenAuthenticationResult['assignment']->start_time,
            'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
            'problems' => self::getProblemsByAssignment(
                $tokenAuthenticationResult['assignment']->alias,
                $tokenAuthenticationResult['course']->alias,
                $r->identity,
                $r->user
            ),
            'courseAssignments' => $tokenAuthenticationResult['courseAssignments'],
            'director' => strval($director->username),
            'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
            'admin' => $tokenAuthenticationResult['courseAdmin'],
        ];
    }

    /**
     * @return list<ProblemsetProblem>
     */
    private static function getProblemsByAssignment(
        string $assignmentAlias,
        string $courseAlias,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user
    ): array {
        $problems = [];
        $problemIndex = 0;
        foreach (
            \OmegaUp\DAO\ProblemsetProblems::getProblemsByAssignmentAlias(
                $assignmentAlias,
                $courseAlias
            ) as $problem
        ) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $problemIndex++
            );
            $problem['accepts_submissions'] = !empty($problem['languages']);

            if (
                is_null($identity)
                || is_null($identity->user_id)
                || is_null($identity->identity_id)
            ) {
                $nominationStatus = [
                    'solved' => false,
                    'tried' => false,
                    'nominated' => false,
                    'dismissed' => false,
                    'nominatedBeforeAC' => false,
                    'dismissedBeforeAC' => false,
                ];
            } else {
                $nominationStatus = \OmegaUp\DAO\QualityNominations::getNominationStatusForProblem(
                    $problem['problem_id'],
                    $identity->user_id
                );

                [
                    'tried' => $tried,
                    'solved' => $solved,
                ] = \OmegaUp\DAO\Runs::getSolvedAndTriedProblemByIdentity(
                    $problem['problem_id'],
                    $identity->identity_id
                );

                $nominationStatus['tried'] = $tried;
                $nominationStatus['solved'] = $solved;
                $problemStatement = \OmegaUp\Controllers\Problem::getProblemStatement(
                    $problem['alias'],
                    $problem['commit'],
                    \OmegaUp\Controllers\Identity::getPreferredLanguage(
                        $identity
                    )
                );
                $nominationStatus['language'] = (
                    !is_null($problemStatement) ?
                    $problemStatement['language'] :
                    'es'
                );
            }
            $nominationStatus['canNominateProblem'] = !is_null($user);
            $nominationStatus['problemAlias'] = $problem['alias'];
            $problem['quality_payload'] = $nominationStatus;
            unset($problem['problem_id']);

            $problems[] = $problem;
        }
        return $problems;
    }

    /**
     * Returns all runs for a course
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cs'|'hs'|'java'|'kj'|'kp'|'lua'|'pas'|'py2'|'py3'|'rb'|null $language
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $rowcount
     * @omegaup-request-param 'compiling'|'new'|'ready'|'running'|'waiting'|null $status
     * @omegaup-request-param mixed $username
     * @omegaup-request-param 'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null $verdict
     *
     * @return array{runs: list<Run>}
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        // Validate request
        [
            'assignment' => $assignment,
            'problem' => $problem,
            'identity' => $identity,
            'language' => $language,
            'status' => $status,
            'verdict' => $verdict,
        ] = self::validateRuns($r);

        // Get our runs
        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            $assignment->problemset_id,
            $status,
            $verdict,
            !is_null($problem) ? $problem->problem_id : null,
            $language,
            !is_null($identity) ? $identity->identity_id : null,
            !is_null($r['offset']) ? intval($r['offset']) : null,
            !is_null($r['rowcount']) ? intval($r['rowcount']) : null
        );

        $result = [];
        foreach ($runs as $run) {
            unset($run['run_id']);
            $run['contest_score'] = floatval($run['contest_score']);
            $result[] = $run;
        }

        return [
            'runs' => $result,
        ];
    }

    /**
     * Validates runs API
     *
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, identity: \OmegaUp\DAO\VO\Identities|null, language: 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cs'|'hs'|'java'|'kj'|'kp'|'lua'|'pas'|'py2'|'py3'|'rb'|null, problem: \OmegaUp\DAO\VO\Problems|null, status:'compiling'|'new'|'ready'|'running'|'waiting'|null, verdict:'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null}
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param 'c11-clang'|'c11-gcc'|'cat'|'cpp11-clang'|'cpp11-gcc'|'cpp17-clang'|'cpp17-gcc'|'cs'|'hs'|'java'|'kj'|'kp'|'lua'|'pas'|'py2'|'py3'|'rb'|null $language
     * @omegaup-request-param int $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param int $rowcount
     * @omegaup-request-param 'compiling'|'new'|'ready'|'running'|'waiting'|null $status
     * @omegaup-request-param mixed $username
     * @omegaup-request-param 'AC'|'CE'|'JE'|'MLE'|'NO-AC'|'OLE'|'PA'|'RFE'|'RTE'|'TLE'|'VE'|'WA'|null $verdict
     */
    private static function validateRuns(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment_alias'],
            'assignment_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );

        $course = self::validateCourseExists($r['course_alias']);

        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $r->ensureOptionalInt('offset');
        $r->ensureOptionalInt('rowcount');
        $status = $r->ensureOptionalEnum(
            'status',
            ['new', 'waiting', 'compiling', 'running', 'ready']
        );
        $verdict = $r->ensureOptionalEnum(
            'verdict',
            \OmegaUp\Controllers\Run::VERDICTS
        );

        // Check filter by problem, is optional
        $problem = null;
        if (!is_null($r['problem_alias'])) {
            $problem = \OmegaUp\DAO\Problems::getByAlias(
                strval($r['problem_alias'])
            );

            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        $language = $r->ensureOptionalEnum(
            'language',
            array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
        );

        // Get user if we have something in username
        $identity = null;
        if (!is_null($r['username'])) {
            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                strval($r['username'])
            );
        }

        return [
            'assignment' => $assignment,
            'problem' => $problem,
            'identity' => $identity,
            'language' => $language,
            'status' => $status,
            'verdict' => $verdict,
        ];
    }

    /**
     * Returns details of a given course
     *
     * @omegaup-request-param mixed $alias
     *
     * @return CourseDetails
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['alias'],
            'alias'
        );
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course);

        // Only Course Admins or Group Members (students) can see these results
        if (
            !\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $course,
                $group
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity);
    }

    /**
     * Edit Course contents
     *
     * @return array{status: string}
     *
     * @omegaup-request-param 'private'|'public'|'registration'|null $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param 'no'|'optional'|'required'|null $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['alias'],
            'alias'
        );

        $originalCourse = self::validateUpdate($r, $r['alias']);

        if (
            !\OmegaUp\Authorization::isCourseAdmin(
                $r->identity,
                $originalCourse
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $valueProperties = [
            'alias',
            'name',
            'description',
            'start_time',
            'finish_time',
            'school_id',
            'show_scoreboard' => [
                'transform' => fn (string $value): bool => boolval($value),
            ],
            'needs_basic_information' => [
                'transform' => fn (string $value): bool => boolval($value),
            ],
            'requests_user_information',
            'admission_mode',
        ];
        self::updateValueProperties($r, $originalCourse, $valueProperties);
        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;

        // Set null finish time if required
        if ($unlimitedDuration) {
            $originalCourse->finish_time = null;
        }

        // Push changes
        \OmegaUp\DAO\Courses::update($originalCourse);

        // TODO: Expire cache

        self::$log->info("Course updated (alias): {$r['alias']}");
        return [
            'status' => 'ok',
        ];
    }

    /**
     * Gets Scoreboard for an assignment
     *
     * @omegaup-request-param mixed $assignment
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $token
     *
     * @return Scoreboard
     */
    public static function apiAssignmentScoreboard(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course'],
            'course'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment'],
            'assignment'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course']);

        if (!$tokenAuthenticationResult['hasToken']) {
            if (
                !\OmegaUp\Authorization::canViewCourse(
                    $r->identity,
                    $tokenAuthenticationResult['course'],
                    $group
                )
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
        }

        $scoreboard = new \OmegaUp\Scoreboard(
            new \OmegaUp\ScoreboardParams([
                'alias' => $tokenAuthenticationResult['assignment']->alias,
                'title' => $tokenAuthenticationResult['assignment']->name,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'start_time' => $tokenAuthenticationResult['assignment']->start_time,
                'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
                'acl_id' => $tokenAuthenticationResult['assignment']->acl_id,
                'group_id' => $tokenAuthenticationResult['course']->group_id,
                'admin' => $tokenAuthenticationResult['courseAdmin'],
            ])
        );

        return $scoreboard->generate(
            /*$withRunDetails=*/            false,
            /*$sortByName=*/false
        );
    }

    /**
     * Returns the Scoreboard events
     *
     * @omegaup-request-param mixed $assignment
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $token
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{events: list<ScoreboardEvent>}
     */
    public static function apiAssignmentScoreboardEvents(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course'],
            'course'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['assignment'],
            'assignment'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        if (is_null($tokenAuthenticationResult['course']->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $scoreboard = new \OmegaUp\Scoreboard(
            \OmegaUp\ScoreboardParams::fromAssignment(
                $tokenAuthenticationResult['assignment'],
                $tokenAuthenticationResult['course']->group_id,
                $tokenAuthenticationResult['courseAdmin']/*show_all_runs*/
            )
        );

        // Push scoreboard data in response
        return [
            'events' => $scoreboard->events()
        ];
    }

    /**
     * Get Problems solved by users of a course
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{user_problems: array<string, list<array{alias: string, title: string, username: string}>>}
     */
    public static function apiListSolvedProblems(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($usersProblems as $userProblem) {
            if (!$userProblem['solved']) {
                continue;
            }
            $userProblems[$userProblem['username']][] = $userProblem;
        }
        return ['user_problems' => $userProblems];
    }

    /**
     * Get Problems unsolved by users of a course
     *
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{user_problems: array<string, list<array{alias: string, title: string, username: string}>>}
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        $usersProblems = \OmegaUp\DAO\Problems::getProblemsByUsersInACourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($usersProblems as $userProblem) {
            if ($userProblem['solved']) {
                continue;
            }
            $userProblems[$userProblem['username']][] = $userProblem;
        }
        return ['user_problems' => $userProblems];
    }

    public static function shouldShowScoreboard(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        return \OmegaUp\Authorization::canViewCourse(
            $identity,
            $course,
            $group
        ) &&
               $course->show_scoreboard;
    }
}
