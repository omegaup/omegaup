<?php

 namespace OmegaUp\Controllers;

/**
 *  CourseController
 *
 * @psalm-type Progress=array{score: float, max_score: float}
 * @psalm-type AssignmentProgress=array<string, Progress>
 * @psalm-type CourseAssignment=array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}
 * @psalm-type CourseDetails=array{admission_mode?: string, alias: string, assignments?: list<CourseAssignment>, basic_information_required: bool, description: string, finish_time?: \OmegaUp\Timestamp|null, isCurator?: bool, is_admin?: bool, name: string, requests_user_information: string, school_id?: int|null, school_name?: null|string, show_scoreboard?: bool, start_time?: \OmegaUp\Timestamp, student_count?: int}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type Run=array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int, type: null|string, username: string, classname: string, alias: string, country: string, contest_alias: null|string}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: RunMetadata, name: null|string, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: RunMetadata}>}>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type ScoreboardEvent=array{classname: string, country: string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}
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
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $assignment_type
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param OmegaUp\Timestamp $start_time
     * @omegaup-request-param bool|null $unlimited_duration
     */
    private static function validateCreateAssignment(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Courses $course
    ): void {
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

        $r->ensureOptionalBool('unlimited_duration');
        $startTime = $r->ensureTimestamp(
            'start_time',
            $courseStartTime->time,
            is_null($courseFinishTime) ? null : $courseFinishTime->time
        );
        $finishTime = $r->ensureOptionalTimestamp(
            'finish_time',
            $courseStartTime->time,
            is_null($courseFinishTime) ? null : $courseFinishTime->time,
            /*$isRequired=*/(
                !is_null($courseFinishTime) ||
                !$r['unlimited_duration']
            )
        );

        if (
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime'
            );
        }

        \OmegaUp\Validators::validateInEnum(
            $r['assignment_type'],
            'assignment_type',
            ['test', 'homework']
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['alias'],
            'alias',
            /*$isRequired=*/true
        );
    }

    /**
     * Validates clone Courses
     *
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $name
     * @omegaup-request-param int $start_time
     */
    private static function validateClone(\OmegaUp\Request $r): void {
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        $r->ensureInt('start_time');
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
    }

    /**
     * Validates create Courses
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param mixed $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param mixed $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param mixed $requests_user_information
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
                'courseInvalidStartTime'
            );
        }
    }

    /**
     * Validates update Courses
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param mixed $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param mixed $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param mixed $unlimited_duration
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

        if (
            (
                is_null($r['unlimited_duration']) ||
                !$r['unlimited_duration']
            ) &&
            !is_null($finishTime) &&
            $startTime->time > $finishTime->time
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime'
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
     * @omegaup-request-param mixed $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param int|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param mixed $requests_user_information
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
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['requests_user_information'],
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

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['admission_mode'],
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
        self::validateClone($r);
        \OmegaUp\Validators::validateValidAlias(
            $r['alias'],
            'alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $originalCourse = self::validateCourseExists($r['course_alias']);

        $startTime = $r->ensureTimestamp('start_time');
        $offset = $startTime->time - $originalCourse->start_time->time;

        $cloneCourseFinishTime = null;
        if (!is_null($originalCourse->finish_time)) {
            $cloneCourseFinishTime = new \OmegaUp\Timestamp(
                $originalCourse->finish_time->time + $offset
            );
        }

        \OmegaUp\DAO\DAO::transBegin();

        try {
            // Create the course (and group)
            $course = \OmegaUp\Controllers\Course::createCourseAndGroup(new \OmegaUp\DAO\VO\Courses([
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
            ]), $r->user);

            $assignmentsProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsAssignmentByCourseAlias(
                $originalCourse
            );

            foreach ($assignmentsProblems as $assignment => $assignmentProblems) {
                // Create and assign homeworks and tests to new course
                $problemset = self::createAssignment($originalCourse, new \OmegaUp\DAO\VO\Assignments([
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
                ]));
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
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'alias' => $r['alias']
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
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }
        if (!is_null(\OmegaUp\DAO\Courses::getByAlias($course->alias))) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'aliasInUse'
            );
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
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    private static function createAssignment(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Assignments $assignment
    ): \OmegaUp\DAO\VO\Problemsets {
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

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse',
                    $e
                );
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
     * @param ?int $points = 100
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

        $assignedPoints = is_null($points) ? 100 : $points;
        \OmegaUp\Controllers\Problemset::addProblem(
            $problemsetId,
            $problem,
            $masterCommit,
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
     * @omegaup-request-param mixed $order
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
        \OmegaUp\Validators::validateOptionalNumber(
            $r['order'],
            'order'
        );
        $course = self::validateCourseExists($r['course_alias']);
        self::validateCreateAssignment($r, $course);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::createAssignment($course, new \OmegaUp\DAO\VO\Assignments([
            'course_id' => $course->course_id,
            'acl_id' => $course->acl_id,
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'publish_time_delay' => $r['publish_time_delay'],
            'assignment_type' => $r['assignment_type'],
            'start_time' => $r['start_time'],
            'finish_time' => $r['finish_time'],
            'order' => intval($r['order']),
        ]));

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
        $startTime = $r->ensureTimestamp(
            'start_time',
            $course->start_time->time,
            is_null($course->finish_time) ? null : $course->finish_time->time
        );

        if (is_null($r['finish_time'])) {
            $r['finish_time'] = $assignment->finish_time;
        }
        $finishTime = $r->ensureTimestamp(
            'finish_time',
            $course->start_time->time,
            is_null($course->finish_time) ? null : $course->finish_time->time
        );

        $unlimitedDuration = $r->ensureOptionalBool(
            'unlimited_duration'
        ) ?? false;

        if ($unlimitedDuration && !is_null($course->finish_time)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseDoesNotHaveUnlimitedDuration'
            );
        }

        if ($startTime->time > $finishTime->time) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'courseInvalidStartTime'
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
     * @omegaup-request-param mixed $points
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

        $points = 100;
        if (is_numeric($r['points'])) {
            $points = intval($r['points']);
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
            $points,
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
     * @omegaup-request-param mixed $order
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

        // Update problems order
        /** @var array{alias: string, order: int}[] */
        $problems = $r['problems'];
        foreach ($problems as $problem) {
            $currentProblem = \OmegaUp\DAO\Problems::getByAlias(
                $problem['alias']
            );
            if (
                is_null($currentProblem) ||
                is_null($currentProblem->problem_id)
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            $order = 1;
            if (is_numeric($r['order'])) {
                $order = intval($r['order']);
            }
            \OmegaUp\DAO\ProblemsetProblems::updateProblemsOrder(
                $problemSet->problemset_id,
                $currentProblem->problem_id,
                $problem['order']
            );
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
        $course = self::validateCourseExists($r['course_alias']);
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Update assignments order
        /** @var array{name: string, description: string, alias: string, assignment_type: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, order: int, scoreboard_url: string, scoreboard_url_admin: string, has_runs: bool}[] */
        $assignments = $r['assignments'];

        foreach ($assignments as $assignment) {
            $currentAssignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                $assignment['alias'],
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
                intval($assignment['order'])
            );
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
     * @return array{assignments: list<array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, has_runs: bool, name: string, order: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}>}
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
            $assignment['has_runs'] = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($assignment['problemset_id'])
            ) > 0;
            unset($assignment['problemset_id']);
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
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     */
    public static function apiRemoveAssignment(\OmegaUp\Request $r): void {
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

        throw new \OmegaUp\Exceptions\UnimplementedException();
    }

    /**
     * Converts a Course object into an array
     *
     * @return array{alias: string, name: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp|null, public: bool, counts: array<string, int>}
     */
    private static function convertCourseToArray(\OmegaUp\DAO\VO\Courses $course): array {
        if (is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $relevantColumns = [
            'alias',
            'name',
            'start_time',
            'finish_time',
            'admission_mode',
        ];
        /** @var array{alias: string, name: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, public: bool} */
        $arr = $course->asFilteredArray($relevantColumns);

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
     * @return array{admin: list<array{alias: string, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, name: string, start_time: \OmegaUp\Timestamp}>, public: list<array{alias: string, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, name: string, start_time: \OmegaUp\Timestamp}>, student: list<array{alias: string, counts: array<string, int>, finish_time: \OmegaUp\Timestamp|null, name: string, start_time: \OmegaUp\Timestamp}>}
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

        // TODO(pablo): Cache
        // Courses the user is an admin for.
        $adminCourses = [];
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $adminCourses = \OmegaUp\DAO\Courses::getAll(
                $page,
                $pageSize,
                'course_id',
                'DESC'
            );
        } else {
            $adminCourses = \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        // Courses the user is a student in.
        $studentCourses = \OmegaUp\DAO\Courses::getCoursesForStudent(
            $r->identity->identity_id
        );

        $response = [
            'admin' => [],
            'student' => [],
            'public' => [],
        ];
        foreach ($adminCourses as $course) {
            $response['admin'][] = \OmegaUp\Controllers\Course::convertCourseToArray(
                $course
            );
        }
        foreach ($studentCourses as $course) {
            $courseAsArray = \OmegaUp\Controllers\Course::convertCourseToArray(
                $course
            );
            $response['student'][] = $courseAsArray;
            if ($course->admission_mode !== self::ADMISSION_MODE_PRIVATE) {
                $response['public'][] = $courseAsArray;
            }
        }

        return $response;
    }

    /**
     * It checks whether user has previous activity in any course in order to
     * redirect to right location
     *
     * @return array{smartyProperties: array<empty, empty>, template: string}
     */
    public static function schoolsIndexForSmarty(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // User is not logged. Anyways, we need to show intro school page
            return [
                'smartyProperties' => [],
                'template' => 'schools.intro.tpl',
            ];
        }

        if (
            !empty(
                \OmegaUp\DAO\Courses::getCoursesForStudent(
                    $r->identity->identity_id
                )
            )
        ) {
            die(header('Location: /course/'));
        }

        // Default values to search courses for logged user
        $page = 1;
        $pageSize = 1;
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            if (
                !empty(
                    \OmegaUp\DAO\Courses::getAll(
                        $page,
                        $pageSize,
                        'course_id',
                        'DESC'
                    )
                )
            ) {
                die(header('Location: /course/'));
            }
        }

        if (
            !empty(
                \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
                    $r->identity->identity_id,
                    $page,
                    $pageSize
                )
            )
        ) {
            die(header('Location: /course/'));
        }
        // User is logged in, but there is no information about courses
        return [
            'smartyProperties' => [],
            'template' => 'schools.intro.tpl',
        ];
    }

    /**
     * Returns the list of requests made by participants who are interested to
     * join the course
     *
     * @omegaup-request-param string $course_alias
     *
     * @return array{users: list<array{accepted: bool|null, admin?: array{name: null|string, username: string}, country: null|string, country_id: null|string, last_update: \OmegaUp\Timestamp|null, request_time: \OmegaUp\Timestamp, username: string}>}
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
                'userNotFound'
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
     * @return array{students: list<array{name: null|string, progress: array<string, float>, username: string}>}
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
            'students' => \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
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
     * @return array{problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, submissions: int, title: string, version: string, visibility: int, visits: int, runs: list<array{guid: string, language: string, source?: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int}>}>}
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
     * @omegaup-request-param mixed $accept_teacher
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
            'accept_teacher' => $r['accept_teacher'],
        ]);

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
     * @return array{inContest: bool, smartyProperties: array{coursePayload?: array{alias: string, currentUsername: string, description: string, isFirstTimeAccess: bool, name: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, shouldShowResults: bool, statements: array{acceptTeacher: array{gitObjectId: null|string, markdown: string, statementType: string}, privacy: array{gitObjectId: null|string, markdown: null|string, statementType: null|string}}, userRegistrationAccepted?: bool|null, userRegistrationAnswered?: bool, userRegistrationRequested?: bool}, payload?: array{details?: array{alias: string, assignments?: list<array{alias: string, assignment_type: string, description: string, finish_time: \OmegaUp\Timestamp|null, max_points: float, name: string, order: int, publish_time_delay: int|null, scoreboard_url: string, scoreboard_url_admin: string, start_time: \OmegaUp\Timestamp}>, basic_information_required: bool, description: string, finish_time?: \OmegaUp\Timestamp|null, is_admin?: bool, name: string, public?: bool, requests_user_information: string, school_id?: int|null, school_name?: null|string, show_scoreboard?: bool, start_time?: \OmegaUp\Timestamp, student_count?: int}, progress?: AssignmentProgress, shouldShowFirstAssociatedIdentityRunWarning?: bool}, showRanking?: bool}, template: string}
     */
    public static function getCourseDetailsForSmarty(\OmegaUp\Request $r): array {
        return self::getIntroDetails($r);
    }

    /**
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $student
     *
     * @return array{payload: array{course: CourseDetails, students: array{name: null|string, progress: array<string, float>, username: string}[], student?: string}}
     */
    public static function getStudentsInformationForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course'], 'course');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['student'],
            'student'
        );

        $course = self::validateCourseExists($r['course']);

        if (is_null($course->course_id) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $result = [
            'payload' => [
                'course' => self::getCommonCourseDetails(
                    $course,
                    $r->identity,
                    /*onlyIntroDetails=*/false
                ),
                'students' => \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
                    $course->course_id,
                    $course->group_id
                ),
            ],
        ];

        if (empty($r['student'])) {
            return $result;
        }

        $result['payload']['student'] = $r['student'];
        return $result;
    }

    /**
     * Refactor of apiIntroDetails in order to be called from php files and APIs
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     *
     * @return array{inContest: bool, smartyProperties: array{coursePayload?: array{alias: string, currentUsername: string, description: string, isFirstTimeAccess: bool, name: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, shouldShowResults: bool, statements: array{acceptTeacher: array{gitObjectId: null|string, markdown: string, statementType: string}, privacy: array{gitObjectId: null|string, markdown: null|string, statementType: null|string}}, userRegistrationAccepted?: bool|null, userRegistrationAnswered?: bool, userRegistrationRequested?: bool}, payload?: array{details?: CourseDetails, progress?: AssignmentProgress, shouldShowFirstAssociatedIdentityRunWarning?: bool}, showRanking?: bool}, template: string}
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
        $hasAcceptedTeacher = true;
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

        $courseDetails = self::getCommonCourseDetails(
            $course,
            $r->identity,
            true  /*onlyIntroDetails*/
        );
        $requestUserInformation = $courseDetails['requests_user_information'];

        $isCourseAdmin = \OmegaUp\Authorization::isCourseAdmin(
            $r->identity,
            $course
        );
        if ($isCourseAdmin && !$showAssignment) {
            return [
                'smartyProperties' => [
                    'showRanking' => true,
                    'payload' => [
                        'details' => self::getCommonCourseDetails(
                            $course,
                            $r->identity,
                            /*$onlyIntroDetails=*/ false
                        ),
                        'progress' => \OmegaUp\DAO\Courses::getAssignmentsProgress(
                            $course->course_id,
                            $r->identity->identity_id
                        ),
                    ],
                ],
                'template' => 'course.details.tpl',
                'inContest' => false,
            ];
        }

        if ($showAssignment) {
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
                    ],
                ],
                'template' => 'arena.contest.course.tpl',
                'inContest' => true,
            ];
        }

        if (
            $shouldShowIntro
            || !$hasAcceptedTeacher
            || (!$hasSharedUserInformation
            && $requestUserInformation !== 'no'
            )
        ) {
            $needsBasicInformation = $courseDetails['basic_information_required']
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

        return [
            'smartyProperties' => [
                'showRanking' => \OmegaUp\Controllers\Course::shouldShowScoreboard(
                    $r->identity,
                    $course,
                    $group
                ),
                'payload' => [
                    'details' => self::getCommonCourseDetails(
                        $course,
                        $r->identity,
                        /*$onlyIntroDetails=*/ false
                    ),
                    'progress' => \OmegaUp\DAO\Courses::getAssignmentsProgress(
                        $course->course_id,
                        $r->identity->identity_id
                    ),
                ],
            ],
            'template' => 'course.details.tpl',
            'inContest' => false,
        ];
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

        return ['status' => 'ok'];
    }

    /**
     * Returns course details common between admin & non-admin
     *
     * @return CourseDetails
     */
    private static function getCommonCourseDetails(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $onlyIntroDetails
    ): array {
        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($identity, $course);

        if ($onlyIntroDetails) {
            $result = [
                'name' => strval($course->name),
                'description' => strval($course->description),
                'alias' => strval($course->alias),
                'basic_information_required' => boolval(
                    $course->needs_basic_information
                ),
                'requests_user_information' => $course->requests_user_information,
            ];
        } else {
            $result = [
                'assignments' => \OmegaUp\DAO\Courses::getAllAssignments(
                    strval($course->alias),
                    $isAdmin
                ),
                'name' => strval($course->name),
                'description' => strval($course->description),
                'alias' => strval($course->alias),
                'school_id' => intval($course->school_id),
                'start_time' => $course->start_time,
                'finish_time' => $course->finish_time,
                'is_admin' => $isAdmin,
                'isCurator' => \OmegaUp\Authorization::canCreatePublicCourse(
                    $identity
                ),
                'admission_mode' => $course->admission_mode,
                'basic_information_required' => boolval(
                    $course->needs_basic_information
                ),
                'show_scoreboard' => boolval($course->show_scoreboard),
                'requests_user_information' => $course->requests_user_information
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

        return self::getCommonCourseDetails(
            $course,
            $r->identity,
            false /*onlyIntroDetails*/
        );
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
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, course: \OmegaUp\DAO\VO\Courses, courseAdmin: bool, courseAssignments: list<array{name: string, description: string, alias: string, publish_time_delay: ?int, assignment_type: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp|null, max_points: float, order: int, scoreboard_url: string, scoreboard_url_admin: string}>, hasToken: bool}
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
     * @omegaup-request-param mixed $assignment
     * @omegaup-request-param mixed $course
     * @omegaup-request-param mixed $lang
     * @omegaup-request-param mixed $token
     * @omegaup-request-param mixed $username
     *
     * @return array{admin: bool, alias: string, assignment_type: null|string, courseAssignments: list<array{name: string, description: string, alias: string, publish_time_delay: ?int, assignment_type: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp|null, max_points: float, order: int, scoreboard_url: string, scoreboard_url_admin: string}>, description: null|string, director: string, finish_time: \OmegaUp\Timestamp|null, name: string, problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, quality_payload: array{canNominateProblem: bool, dismissed: bool, dismissedBeforeAC: bool, language?: string, nominated: bool, nominatedBeforeAC: bool, problemAlias: string, solved: bool, tried: bool}, submissions: int, title: string, version: string, visibility: int, visits: int}>, problemset_id: int, start_time: \OmegaUp\Timestamp}
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
        if (is_null($tokenAuthenticationResult['course']->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        if (is_null($tokenAuthenticationResult['assignment']->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        $problems = [];
        $problemIndex = 0;
        foreach (
            \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                $tokenAuthenticationResult['assignment']->problemset_id
            ) as $problem
        ) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                $problemIndex++
            );

            if (
                is_null($r->identity)
                || is_null($r->identity->user_id)
                || is_null($r->identity->identity_id)
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
                    $r->identity->user_id
                );

                [
                    'tried' => $tried,
                    'solved' => $solved,
                ] = \OmegaUp\DAO\Runs::getSolvedAndTriedProblemByIdentity(
                    $problem['problem_id'],
                    $r->identity->identity_id
                );

                $nominationStatus['tried'] = $tried;
                $nominationStatus['solved'] = $solved;
                $nominationStatus['language'] = \OmegaUp\Controllers\Problem::getProblemStatement(
                    $problem['alias'],
                    $problem['commit'],
                    \OmegaUp\Controllers\Identity::getPreferredLanguage(
                        self::resolveTargetIdentity($r)
                    )
                )['language'];
            }
            $nominationStatus['canNominateProblem'] = !is_null($r->user);
            $nominationStatus['problemAlias'] = $problem['alias'];
            $problem['quality_payload'] = $nominationStatus;
            unset($problem['problem_id']);

            $problems[] = $problem;
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
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
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
            'problems' => $problems,
            'courseAssignments' => $tokenAuthenticationResult['courseAssignments'],
            'director' => strval($director->username),
            'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
            'admin' => $tokenAuthenticationResult['courseAdmin'],
        ];
    }

    /**
     * Returns all runs for a course
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $language
     * @omegaup-request-param mixed $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param mixed $rowcount
     * @omegaup-request-param mixed $status
     * @omegaup-request-param mixed $username
     * @omegaup-request-param mixed $verdict
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
        ] = self::validateRuns($r);

        // Get our runs
        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            $assignment->problemset_id,
            !is_null($r['status']) ? strval($r['status']) : null,
            !is_null($r['verdict']) ? strval($r['verdict']) : null,
            !is_null($problem) ? $problem->problem_id : null,
            !is_null($r['language']) ? strval($r['language']) : null,
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
     * @return array{assignment: \OmegaUp\DAO\VO\Assignments, problem: \OmegaUp\DAO\VO\Problems|null, identity: \OmegaUp\DAO\VO\Identities|null}
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @omegaup-request-param mixed $assignment_alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $language
     * @omegaup-request-param int $offset
     * @omegaup-request-param mixed $problem_alias
     * @omegaup-request-param int $rowcount
     * @omegaup-request-param mixed $status
     * @omegaup-request-param mixed $username
     * @omegaup-request-param mixed $verdict
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
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['status'],
            'status',
            ['new', 'waiting', 'compiling', 'running', 'ready']
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['verdict'],
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

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['language'],
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

        return self::getCommonCourseDetails(
            $course,
            $r->identity,
            false /*onlyIntroDetails*/
        );
    }

    /**
     * Edit Course contents
     *
     * @return array{status: string}
     *
     * @omegaup-request-param mixed $admission_mode
     * @omegaup-request-param mixed $alias
     * @omegaup-request-param mixed $course_alias
     * @omegaup-request-param mixed $description
     * @omegaup-request-param OmegaUp\Timestamp|null $finish_time
     * @omegaup-request-param mixed $name
     * @omegaup-request-param bool|null $needs_basic_information
     * @omegaup-request-param mixed $requests_user_information
     * @omegaup-request-param int $school_id
     * @omegaup-request-param bool|null $show_scoreboard
     * @omegaup-request-param OmegaUp\Timestamp|null $start_time
     * @omegaup-request-param mixed $unlimited_duration
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['course_alias'],
            'course_alias'
        );
        $originalCourse = self::validateUpdate($r, $r['course_alias']);

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
            'show_scoreboard' => ['transform' => function (string $value): bool {
                return boolval($value);
            }],
            'needs_basic_information' => ['transform' => function (string $value): bool {
                return boolval($value);
            }],
            'requests_user_information',
            'admission_mode',
        ];
        self::updateValueProperties($r, $originalCourse, $valueProperties);

        // Set null finish time if required
        if (
            !is_null($r['unlimited_duration']) &&
            $r['unlimited_duration']
        ) {
            $originalCourse->finish_time = null;
        }

        // Push changes
        \OmegaUp\DAO\Courses::update($originalCourse);

        // TODO: Expire cache

        self::$log->info("Course updated (alias): {$r['course_alias']}");
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
            false /*withRunDetails*/,
            true /*sortByName*/
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
        $solvedProblems = \OmegaUp\DAO\Problems::getSolvedProblemsByUsersOfCourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($solvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
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

        $unsolvedProblems = \OmegaUp\DAO\Problems::getUnsolvedProblemsByUsersOfCourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($unsolvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
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
