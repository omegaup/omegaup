<?php

 namespace OmegaUp\Controllers;

/**
 *  CourseController
 *
 * @author alanboy
 * @author pablo.aguilar
 * @author lhchavez
 * @author joemmanuel
 */
class Course extends \OmegaUp\Controllers\Controller {
    /**
     * Validate assignment_alias existis into the course and
     * return Assignments object
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param string $assignmentAlias
     * @return \OmegaUp\DAO\VO\Assignments
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseAssignmentAlias(\OmegaUp\DAO\VO\Courses $course, string $assignmentAlias) : \OmegaUp\DAO\VO\Assignments {
        $assignment = \OmegaUp\DAO\Courses::getAssignmentByAlias($course, $assignmentAlias);
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        return $assignment;
    }

    /**
     * Validates request for creating a new Assignment
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Assignments $assignment
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateCreateAssignment(\OmegaUp\Request $r, \OmegaUp\DAO\VO\Courses $course) : void {
        $isRequired = true;
        $courseStartTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp($course->start_time);
        $courseFinishTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp($course->finish_time);

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['name'], 'name', $isRequired);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt(
            'start_time',
            $courseStartTime,
            $courseFinishTime,
            $isRequired
        );
        $r->ensureInt(
            'finish_time',
            $courseStartTime,
            $courseFinishTime,
            $isRequired
        );

        if ($r['start_time'] > $r['finish_time']) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('courseInvalidStartTime');
        }

        \OmegaUp\Validators::validateInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $isRequired);
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', $isRequired);
    }

    /**
     * Validates clone Courses
     */
    private static function validateClone(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['name'], 'name');
        $r->ensureInt('start_time', null, null, true);
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
    }

    /**
     * Validates create Courses
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateCreate(
        \OmegaUp\Request $r
    ) : void {
        self::validateBasicCreateOrUpdate($r);

        if ($r['start_time'] > $r['finish_time']) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('courseInvalidStartTime');
        }
    }

    /**
     * Validates update Courses
     *
     * @param \OmegaUp\Request $r
     * @param string $courseAlias
     * @return \OmegaUp\DAO\VO\Courses
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateUpdate(
        \OmegaUp\Request $r,
        string $courseAlias
    ) : \OmegaUp\DAO\VO\Courses {
        self::validateBasicCreateOrUpdate($r, true /*is update*/);

        // Get the actual start and finish time of the course, considering that
        // in case of update, parameters can be optional.
        $originalCourse = self::validateCourseExists($courseAlias);

        if (is_null($r['start_time'])) {
            $r['start_time'] = $originalCourse->start_time;
        }
        if (is_null($r['finish_time'])) {
            $r['finish_time'] = $originalCourse->finish_time;
        }

        if ($r['start_time'] > $r['finish_time']) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('courseInvalidStartTime');
        }

        return $originalCourse;
    }

    /**
     * Validates basic information of a course
     * @param \OmegaUp\Request $r
     * @param bool $isUpdate
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateBasicCreateOrUpdate(\OmegaUp\Request $r, bool $isUpdate = false) : void {
        $isRequired = true;

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['name'], 'name', $isRequired);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt('start_time', null, null, !$isUpdate);
        $r->ensureInt('finish_time', null, null, !$isUpdate);

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', $isRequired);

        // Show scoreboard, needs basic information and request user information are always optional
        $r->ensureBool('needs_basic_information', false /*isRequired*/);
        $r->ensureBool('show_scoreboard', false /*isRequired*/);
        \OmegaUp\Validators::validateInEnum(
            $r['requests_user_information'],
            'requests_user_information',
            ['no', 'optional', 'required'],
            false
        );

        $r->ensureBool('public', false /*isRequired*/);
        $r->ensureInt('school_id', null, null, false /*isRequired*/);

        if (is_null($r['school_id'])) {
            $school = null;
        } else {
            $school = \OmegaUp\DAO\Schools::getByPK($r['school_id']);
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('schoolNotFound');
            }
        }

        // Only curator can set public
        if (!is_null($r['public'])
            && $r['public'] == true
            && !\OmegaUp\Authorization::canCreatePublicCourse($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects course alias, returns
     * course. Throws if not found.
     * @param string $courseAlias
     * @return \OmegaUp\DAO\VO\Courses
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateCourseExists(string $courseAlias) : \OmegaUp\DAO\VO\Courses {
        \OmegaUp\Validators::validateStringNonEmpty($courseAlias, 'course_alias');
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        return $course;
    }

    /**
     * Gets the Group assigned to the Course.
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Groups $group
     * @return \OmegaUp\DAO\VO\Groups
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function resolveGroup(\OmegaUp\DAO\VO\Courses $course, ?\OmegaUp\DAO\VO\Groups $group) : \OmegaUp\DAO\VO\Groups {
        if (!is_null($group)) {
            return $group;
        }

        $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        return $group;
    }

    /**
     * Clone a course
     *
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiClone(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        self::validateClone($r);
        $originalCourse = self::validateCourseExists($r['course_alias']);

        $offset = intval(round($r['start_time']) - $originalCourse->start_time);

        \OmegaUp\DAO\DAO::transBegin();

        try {
            // Create the course (and group)
            $course = \OmegaUp\Controllers\Course::createCourseAndGroup(new \OmegaUp\DAO\VO\Courses([
                'name' => $r['name'],
                'description' => $originalCourse->description,
                'alias' => $r['alias'],
                'school_id' => $originalCourse->school_id,
                'start_time' => $r['start_time'],
                'finish_time' => $originalCourse->finish_time + $offset,
                'public' => 0,
                'show_scoreboard' => $originalCourse->show_scoreboard,
                'needs_basic_information' => $originalCourse->needs_basic_information,
                'requests_user_information' => $originalCourse->requests_user_information
            ]), $r->user);

            $assignmentsProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsAssignmentByCourseAlias($originalCourse);

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
                    'start_time' => intval($assignmentProblems['start_time']) + $offset,
                    'finish_time' => intval($assignmentProblems['finish_time']) + $offset,
                    'order' => $assignmentProblems['order'],
                    'max_points' => $assignmentProblems['max_points'],
                ]));

                /** @var array{problem_id: int, problem_alias: string}[] $problem */
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

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    /**
     * Create new course API
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        self::validateCreate($r);

        self::createCourseAndGroup(new \OmegaUp\DAO\VO\Courses([
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'school_id' => $r['school_id'],
            'start_time' => $r['start_time'],
            'finish_time' => $r['finish_time'],
            'public' => $r['public'] ?: false,
            'show_scoreboard' => $r['show_scoreboard'],
            'needs_basic_information' => $r['needs_basic_information'],
            'requests_user_information' => $r['requests_user_information'],
        ]), $r->user);

        return ['status' => 'ok'];
    }

    /**
     * Function to create a new course with its corresponding group
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Users $creator
     * @return \OmegaUp\DAO\VO\Courses
     */
    private static function createCourseAndGroup(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Users $creator
    ) : \OmegaUp\DAO\VO\Courses {
        if (!is_null(\OmegaUp\DAO\Courses::getByAlias($course->alias))) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('aliasInUse');
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
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('titleInUse', $e);
            }
            throw $e;
        }
        return $course;
    }

    /**
     * Function to create a new assignment
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Assignments $assignment
     * @return \OmegaUp\DAO\VO\Problemsets
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    private static function createAssignment(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Assignments $assignment
    ) : \OmegaUp\DAO\VO\Problemsets {
        \OmegaUp\DAO\DAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new \OmegaUp\DAO\VO\Problemsets([
                'acl_id' => $assignment->acl_id,
                'type' => 'Assignment',
                'scoreboard_url' => \OmegaUp\SecurityTools::randomString(30),
                'scoreboard_url_admin' => \OmegaUp\SecurityTools::randomString(30),
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
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('aliasInUse', $e);
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
        ?int $points = 100,
        ?string $commit = null,
        ?int $order = 1
    ) : void {
        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        [$masterCommit, $currentVersion] = \OmegaUp\Controllers\Problem::resolveCommit(
            $problem,
            $commit
        );

        \OmegaUp\Controllers\Problemset::addProblem(
            $problemsetId,
            $problem,
            $masterCommit,
            $currentVersion,
            $identity,
            $points,
            $order,
            $validateVisibility
        );
    }

    /**
     * API to Create an assignment
     *
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiCreateAssignment(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
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
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Update an assignment
     *
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdateAssignment(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        [$course, $assignment] = self::validateAssignmentDetails(
            $r['course'],
            $r['assignment'],
            $r->identity
        );
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (is_null($r['start_time'])) {
            $r['start_time'] = $assignment->start_time;
        } else {
            $r->ensureInt(
                'start_time',
                $course->start_time,
                $course->finish_time,
                true /* is_required */
            );
        }
        if (is_null($r['start_time'])) {
            $r['finish_time'] = $assignment->finish_time;
        } else {
            $r->ensureInt(
                'finish_time',
                $course->start_time,
                $course->finish_time,
                true /* is_required */
            );
        }

        if ($r['start_time'] > $r['finish_time']) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('courseInvalidStartTime');
        }

        // Prevent date changes if a course already has runs
        if ($r['start_time'] != $assignment->start_time) {
            $runCount = 0;

            $runCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($assignment->problemset_id)
            );

            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('courseUpdateAlreadyHasRuns');
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

        \OmegaUp\DAO\Assignments::update($assignment);

        return ['status' => 'ok'];
    }

    /**
     * Adds a problem to an assignment
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAddProblem(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        $points = 100;
        if (is_numeric($r['points'])) {
            $points = intval($r['points']);
        }

        \OmegaUp\Validators::validateStringOfLengthInRange($r['commit'], 'commit', 1, 40, false);
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

        return ['status' => 'ok'];
    }

    /**
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdateProblemsOrder(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        // Update problems order
        /** @var array{alias: string, order: int}[] */
        $problems = $r['problems'];
        foreach ($problems as $problem) {
            $currentProblem = \OmegaUp\DAO\Problems::getByAlias($problem['alias']);
            if (is_null($currentProblem)) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
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

        return ['status' => 'ok'];
    }

    /**
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdateAssignmentsOrder(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Update assignments order
        foreach ($r['assignments'] as $assignment) {
            $currentAssignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse($assignment['alias'], $course->course_id);

            if (empty($currentAssignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
            }

            \OmegaUp\DAO\Assignments::updateAssignmentsOrder(
                $currentAssignment->assignment_id,
                intval($assignment['order'])
            );
        }

        return ['status' => 'ok'];
    }

    public static function apiGetProblemUsers(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get this problem
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $identities = \OmegaUp\DAO\Problems::getIdentitiesInGroupWhoAttemptedProblem(
            $course->group_id,
            $problem->problem_id
        );

        return ['status' => 'ok', 'identities' => $identities];
    }

    /**
     * Remove a problem from an assignment
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
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
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotPartOfAssignment');
        }
        if (\OmegaUp\DAO\Submissions::countTotalRunsOfProblemInProblemset(
            intval($problem->problem_id),
            intval($problemSet->problemset_id)
        ) > 0 &&
            !\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }
        \OmegaUp\DAO\ProblemsetProblems::delete($problemsetProblem);

        \OmegaUp\DAO\Courses::updateAssignmentMaxPoints(
            $course,
            $r['assignment_alias']
        );

        return ['status' => 'ok'];
    }

    /**
     * List course assignments
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiListAssignments(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        )) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Assignments::getSortedCourseAssignments(
            $course->course_id
        );

        $response = [
            'status' => 'ok',
            'assignments' => [],
        ];
        $time = \OmegaUp\Time::get();
        foreach ($assignments as $assignment) {
            $assignment['has_runs'] = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($assignment['problemset_id'])
            ) > 0;
            unset($assignment['problemset_id']);
            if ($assignment['start_time'] > $time &&
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
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRemoveAssignment(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = \OmegaUp\DAO\Assignments::getProblemset(
            $$course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        throw new \OmegaUp\Exceptions\UnimplementedException();
    }

    /**
     * Converts a Course object into an array
     * @return array
     */
    private static function convertCourseToArray(\OmegaUp\DAO\VO\Courses $course) : array {
        $relevant_columns = ['alias', 'name', 'start_time', 'finish_time'];
        $arr = $course->asFilteredArray($relevant_columns);

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
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiListCourses(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // TODO(pablo): Cache
        // Courses the user is an admin for.
        $admin_courses = [];
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $admin_courses = \OmegaUp\DAO\Courses::getAll(
                $page,
                $pageSize,
                'course_id',
                'DESC'
            );
        } else {
            $admin_courses = \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        // Courses the user is a student in.
        $student_courses = \OmegaUp\DAO\Courses::getCoursesForStudent($r->identity->identity_id);

        $response = [
            'admin' => [],
            'student' => [],
            'status' => 'ok'
        ];
        foreach ($admin_courses as $course) {
            $response['admin'][] = \OmegaUp\Controllers\Course::convertCourseToArray($course);
        }
        foreach ($student_courses as $course) {
            $response['student'][] = \OmegaUp\Controllers\Course::convertCourseToArray($course);
        }
        return $response;
    }

    /**
     * Returns true when logged user has previous activity in any course
     *
     * @param \OmegaUp\Request $r
     * @return bool
     */
    public static function userHasActivityInCourses(\OmegaUp\Request $r) : bool {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $identity = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session']['identity'];

        // User doesn't have activity because is not logged.
        if (is_null($identity)) {
            return false;
        }

        if (!empty(\OmegaUp\DAO\Courses::getCoursesForStudent($identity->identity_id))) {
            return true;
        }

        // Default values to search courses for legged user
        $page = 1;
        $pageSize = 1;
        if (\OmegaUp\Authorization::isSystemAdmin($identity)) {
            $result = \OmegaUp\DAO\Courses::getAll($page, $pageSize, 'course_id', 'DESC');
            if (!empty($result)) {
                return true;
            }
        }
        $result = \OmegaUp\DAO\Courses::getAllCoursesAdminedByIdentity(
            $identity->identity_id,
            $page,
            $pageSize
        );
        return !empty($result);
    }

    /**
     * List students in a course
     *
     * @param  \OmegaUp\Request $r
     * @return array response
     */
    public static function apiListStudents(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $students = \OmegaUp\DAO\Courses::getStudentsInCourseWithProgressPerAssignment(
            $course->course_id,
            $course->group_id
        );

        return [
            'students' => $students,
            'status' => 'ok',
        ];
    }

    public static function apiStudentProgress(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);
        if (is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
            $course->group_id,
            $resolvedIdentity->identity_id
        ))) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $r['assignment'] = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($r['assignment'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        $problems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $r['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $runsArray = \OmegaUp\DAO\Runs::getForProblemDetails(
                intval($problem['problem_id']),
                intval($r['assignment']->problemset_id),
                intval($resolvedIdentity->identity_id)
            );
            $problem['runs'] = [];
            foreach ($runsArray as $run) {
                $run['time'] = intval($run['time']);
                $run['contest_score'] = floatval($run['contest_score']);
                try {
                    $run['source'] = \OmegaUp\Controllers\Submission::getSource($run['guid']);
                } catch (\Exception $e) {
                    self::$log->error("Error fetching source for {$run['guid']}", $e);
                }
                array_push($problem['runs'], $run);
            }
            unset($problem['problem_id']);
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName($letter++);
        }

        return [
            'status' => 'ok',
            'problems' => $problems,
        ];
    }

    /**
     * Returns details of a given course
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyProgress(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        )) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $assignments = \OmegaUp\DAO\Courses::getAssignmentsProgress(
            $course->course_id,
            $r->identity->identity_id
        );

        return [
            'status' => 'ok',
            'assignments' => $assignments,
        ];
    }

    /**
     * Add Student to Course.
     *
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiAddStudent(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);

        // Only course admins or users adding themselves when the course is public
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            && ($course->public == false
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
            if ($resolvedIdentity->identity_id === $r->identity->identity_id
                 && $course->requests_user_information != 'no') {
                $privacystatement_id = \OmegaUp\DAO\PrivacyStatements::getId($r['privacy_git_object_id'], $r['statement_type']);
                if (!\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement($resolvedIdentity->identity_id, $privacystatement_id)) {
                    $privacystatement_consent_id = \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacystatement_id
                    );
                } else {
                    $privacystatement_consent_id = \OmegaUp\DAO\PrivacyStatementConsentLog::getId($resolvedIdentity->identity_id, $privacystatement_id);
                }

                $groupIdentity->privacystatement_consent_id = $privacystatement_consent_id;
            }
            if ($resolvedIdentity->identity_id === $r->identity->identity_id
                 && !empty($r['accept_teacher'])) {
                $privacystatement_id = \OmegaUp\DAO\PrivacyStatements::getId($r['accept_teacher_git_object_id'], 'accept_teacher');
                if (!\OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement($resolvedIdentity->identity_id, $privacystatement_id)) {
                    \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacystatement_id
                    );
                }
            }
            \OmegaUp\DAO\GroupsIdentities::replace($groupIdentity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return ['status' => 'ok'];
    }

    /**
     * Remove Student from Course
     *
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiRemoveStudent(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);

        if (is_null(\OmegaUp\DAO\GroupsIdentities::getByPK(
            $course->group_id,
            $resolvedIdentity->identity_id
        ))) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseStudentNotInCourse');
        }

        \OmegaUp\DAO\GroupsIdentities::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $resolvedIdentity->identity_id,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Returns all course administrators
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdmins(\OmegaUp\Request $r) {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'admins' => \OmegaUp\DAO\UserRoles::getCourseAdmins($course),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getCourseAdmins($course)
        ];
    }

    /**
     * Adds an admin to a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $resolvedUser = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only director is allowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addUser($course->acl_id, $resolvedUser->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity($r['usernameOrEmail']);
        if (is_null($resolvedIdentity->user_id)) {
            // Unassociated identities can't be course admins
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $resolvedUser = \OmegaUp\DAO\Users::getByPK($resolvedIdentity->user_id);

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isCourseAdmin($resolvedIdentity, $course)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser($course->acl_id, $resolvedUser->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admins are allowed to modify course
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::addGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check course_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Controllers\ACL::removeGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     * @param  \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException Course not found or trying to directly access a private course.
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @return array
     */
    public static function apiIntroDetails(\OmegaUp\Request $r) {
        $result = self::getIntroDetails($r)['smartyProperties']['coursePayload'];
        $result['status'] = 'ok';
        return $result;
    }

    public static function getCourseDetailsForSmarty(\OmegaUp\Request $r) : array {
        return self::getIntroDetails($r);
    }

    /**
     * Refactor of apiIntroDetails in order to be called from php files and APIs
     */
    public static function getIntroDetails(\OmegaUp\Request $r) : array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);
        $showAssignment = !empty($r['assignment_alias']);
        $shouldShowIntro = !\OmegaUp\Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        );
        $isFirstTimeAccess = false;
        $shouldShowAcceptTeacher = false;
        if (!\OmegaUp\Authorization::isGroupAdmin($r->identity, $group)) {
            $sharingInformation = \OmegaUp\DAO\Courses::getSharingInformation(
                $r->identity->identity_id,
                $course,
                $group
            );
            $isFirstTimeAccess =
                $sharingInformation['share_user_information'] == null;
            $shouldShowAcceptTeacher =
                $sharingInformation['accept_teacher'] == null;
        }
        if ($shouldShowIntro && !$course->public) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $courseDetails = self::getCommonCourseDetails(
            $course,
            $r->identity,
            true  /*onlyIntroDetails*/
        );
        $requestUserInformation = $courseDetails['requests_user_information'];
        if ($shouldShowIntro || $shouldShowAcceptTeacher || ($isFirstTimeAccess
            && $requestUserInformation != 'no'
        )) {
            $needsBasicInformation = $courseDetails['basic_information_required']
                && (!is_null($r->identity->country_id)
                || !is_null($r->identity->state_id) || !is_null($r->identity->school_id));

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
                $privacyStatement['gitObjectId'] =
                    \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                        $statementType
                    )['git_object_id'];
                $privacyStatement['statementType'] = $statementType;
            }

            $markdown = \OmegaUp\PrivacyStatement::getForConsent(
                $r->identity->language_id,
                'accept_teacher'
            );
            if (is_null($markdown)) {
                throw new \OmegaUp\Exceptions\InvalidFilesystemOperationException();
            }
            $acceptTeacherStatement = [
                'gitObjectId' =>
                    \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                        'accept_teacher'
                    )['git_object_id'],
                'markdown' => $markdown,
                'statementType' => 'accept_teacher',
            ];

            $smartyProperties = [
                'coursePayload' => [
                    'name' => $courseDetails['name'],
                    'description' => $courseDetails['description'],
                    'alias' => $courseDetails['alias'],
                    'currentUsername' => $r->identity->username,
                    'needsBasicInformation' => $needsBasicInformation,
                    'requestsUserInformation' =>
                        $courseDetails['requests_user_information'],
                    'shouldShowAcceptTeacher' => $shouldShowAcceptTeacher,
                    'statements' => [
                        'privacy' => $privacyStatement,
                        'acceptTeacher' => $acceptTeacherStatement,
                    ],
                    'isFirstTimeAccess' => $isFirstTimeAccess,
                    'shouldShowResults' => $shouldShowIntro,
                ]
            ];
            $template = 'arena.course.intro.tpl';
        } elseif ($showAssignment) {
            $smartyProperties = [
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
            ];
            $template = 'arena.contest.course.tpl';
        } else {
            $smartyProperties = [
                'showRanking' => \OmegaUp\Authorization::isCourseAdmin($r->identity, $course)
            ];
            $template = 'course.details.tpl';
        }

        return [
            'smartyProperties' => $smartyProperties,
            'template' => $template,
        ];
    }

    /**
     * Returns course details common between admin & non-admin
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param bool $onlyIntroDetails
     * @return array
     */
    private static function getCommonCourseDetails(
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $onlyIntroDetails
    ) : array {
        $isAdmin = \OmegaUp\Authorization::isCourseAdmin($identity, $course);

        if ($onlyIntroDetails) {
            $result = [
                'status' => 'ok',
                'name' => $course->name,
                'description' => $course->description,
                'alias' => $course->alias,
                'basic_information_required' => boolval($course->needs_basic_information),
                'requests_user_information' => $course->requests_user_information
            ];
        } else {
            $result = [
                'status' => 'ok',
                'assignments' => \OmegaUp\DAO\Courses::getAllAssignments($course->alias, $isAdmin),
                'name' => $course->name,
                'description' => $course->description,
                'alias' => $course->alias,
                'school_id' => $course->school_id,
                'start_time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp($course->start_time),
                'finish_time' => \OmegaUp\DAO\DAO::fromMySQLTimestamp($course->finish_time),
                'is_admin' => $isAdmin,
                'public' => $course->public,
                'basic_information_required' => boolval($course->needs_basic_information),
                'show_scoreboard' => boolval($course->show_scoreboard),
                'requests_user_information' => $course->requests_user_information
            ];

            if ($isAdmin) {
                if (is_null($course->group_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
                }
                $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
                if (is_null($group)) {
                    throw new \OmegaUp\Exceptions\NotFoundException('courseGroupNotFound');
                }
                $result['student_count'] = \OmegaUp\DAO\GroupsIdentities::GetMemberCountById(
                    $group->group_id
                );
            }
            if (!is_null($course->school_id)) {
                $school = \OmegaUp\DAO\Schools::getByPK($course->school_id);
                if ($school != null) {
                    $result['school_name'] = $school->name;
                    $result['school_id'] = $school->school_id;
                }
            }
        }

        return $result;
    }

    /**
     * Returns all details of a given Course
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdminDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
        $r->ensureIdentity();
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity, false /*onlyIntroDetails*/);
    }

    /**
     * Returns a report with all user activity for a course.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiActivityReport(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $accesses = \OmegaUp\DAO\ProblemsetAccessLog::GetAccessForCourse($course->course_id);
        $submissions = \OmegaUp\DAO\SubmissionLog::GetSubmissionsForCourse($course->course_id);

        return [
            'status' => 'ok',
            'events' => \OmegaUp\ActivityReport::getActivityReport($accesses, $submissions),
        ];
    }

    /**
     * Validates and authenticate token for operations when user can be logged
     * in or not. This is the only private function that receives Request as a
     * parameter because it needs authenticate it wheter there is no token.
     *
     * @param  string $courseAlias
     * @param  string $assignmentAlias
     * @param  string $token
     * @param  \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function authenticateAndValidateToken(
        string $courseAlias,
        string $assignmentAlias,
        ?string $token,
        \OmegaUp\Request $r
    ) : array {
        if (is_null($token)) {
            $r->ensureIdentity();
            [$course, $assignment] = self::validateAssignmentDetails(
                $courseAlias,
                $assignmentAlias,
                $r->identity
            );

            return [
                'hasToken' => false,
                'courseAdmin' => \OmegaUp\Authorization::isCourseAdmin(
                    $r->identity,
                    $course
                ),
                'assignment' => $assignment,
                'course' => $course,
            ];
        }

        $courseAdmin = false;

        $course = self::validateCourseExists($courseAlias);
        $assignment = self::validateCourseAssignmentAlias($course, $assignmentAlias);

        $assignmentProblemset = \OmegaUp\DAO\Assignments::getByIdWithScoreboardUrls($assignment->assignment_id);
        if (is_null($assignmentProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        if ($token === $assignmentProblemset['scoreboard_url_admin']) {
            $courseAdmin = true;
        } elseif ($token !== $assignmentProblemset['scoreboard_url']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('invalidScoreboardUrl');
        }

        // hasToken is true, it means we do not autenticate request user
        return [
            'courseAdmin' => $courseAdmin,
            'hasToken' => true,
            'assignment' => $assignment,
            'course' => $course,
        ];
    }

    /**
     * Validates assignment by course alias and assignment alias given
     * @param  string $courseAlias
     * @param  string $assignmentAlias
     * @param  \OmegaUp\DAO\VO\Identities $identity
     * @return array
     */
    private static function validateAssignmentDetails(
        ?string $courseAlias,
        ?string $assignmentAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : array {
        \OmegaUp\Validators::validateStringNonEmpty($courseAlias, 'course');
        \OmegaUp\Validators::validateStringNonEmpty($assignmentAlias, 'assignment');
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse($assignmentAlias, $course->course_id);
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        // Admins are almighty, no need to check anything else.
        if (\OmegaUp\Authorization::isCourseAdmin($identity, $course)) {
            return [$course, $assignment];
        }

        if ($assignment->start_time > \OmegaUp\Time::get() ||
            !\OmegaUp\DAO\GroupRoles::isContestant($identity->identity_id, $assignment->acl_id)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        return [$course, $assignment];
    }

    /**
     * Returns details of a given assignment
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiAssignmentDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );

        $problems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $tokenAuthenticationResult['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $problem['letter'] = \OmegaUp\Controllers\Contest::columnName($letter++);
            unset($problem['problem_id']);
        }

        $acl = \OmegaUp\DAO\ACLs::getByPK($tokenAuthenticationResult['course']->acl_id);
        if (is_null($acl) || is_null($acl->owner_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        $director = \OmegaUp\DAO\Identities::findByUserId(intval($acl->owner_id))->username;

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            // Authenticate request
            $r->ensureIdentity();
            \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
                'ip' => ip2long($_SERVER['REMOTE_ADDR']),
            ]));
        }

        return [
            'status' => 'ok',
            'name' => $tokenAuthenticationResult['assignment']->name,
            'description' => $tokenAuthenticationResult['assignment']->description,
            'assignment_type' => $tokenAuthenticationResult['assignment']->assignment_type,
            'start_time' => $tokenAuthenticationResult['assignment']->start_time,
            'finish_time' => $tokenAuthenticationResult['assignment']->finish_time,
            'problems' => $problems,
            'director' => $director,
            'problemset_id' => $tokenAuthenticationResult['assignment']->problemset_id,
            'admin' => $tokenAuthenticationResult['courseAdmin'],
        ];
    }

    /**
     * Returns all runs for a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRuns(\OmegaUp\Request $r) {
        // Authenticate request
        $r->ensureIdentity();

        // Validate request
        self::validateRuns($r);

        // Get our runs
        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            $r['assignment']->problemset_id,
            $r['status'],
            $r['verdict'],
            !is_null($r['problem']) ? $r['problem']->problem_id : null,
            $r['language'],
            !is_null($r['identity']) ? $r['identity']->identity_id : null,
            $r['offset'],
            $r['rowcount']
        );

        $result = [];

        foreach ($runs as $run) {
            $run['time'] = intval($run['time']);
            $run['score'] = floatval($run['score']);
            $run['contest_score'] = floatval($run['contest_score']);
            array_push($result, $run);
        }

        $response = [];
        $response['runs'] = $result;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Validates runs API
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRuns(\OmegaUp\Request $r) : void {
        $r->ensureIdentity();
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }
        \OmegaUp\Validators::validateStringNonEmpty($r['assignment_alias'], 'assignment_alias');

        $course = self::validateCourseExists($r['course_alias']);

        $r['assignment'] = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($r['assignment'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        \OmegaUp\Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        \OmegaUp\Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            $r['problem'] = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);

            if (is_null($r['problem'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
            }
        }

        \OmegaUp\Validators::validateInEnum(
            $r['language'],
            'language',
            array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES),
            false
        );

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            $r['identity'] = \OmegaUp\Controllers\Identity::resolveIdentity($r['username']);
        }
    }

    /**
     * Returns details of a given course
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!\OmegaUp\Authorization::canViewCourse($r->identity, $course, $group)) {
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
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        $originalCourse = self::validateUpdate($r, $r['course_alias']);
        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $originalCourse)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $valueProperties = [
            'alias',
            'name',
            'description',
            'start_time',
            'finish_time',
            'school_id',
            'show_scoreboard' => ['transform' => function ($value) {
                return $value == 'true' ? 1 : 0;
            }],
            'needs_basic_information' => ['transform' => function ($value) {
                return $value == 'true' ? 1 : 0;
            }],
            'requests_user_information',
            'public' => ['transform' => function ($value) {
                return is_null($value) ? false : $value;
            }],
        ];
        self::updateValueProperties($r, $originalCourse, $valueProperties);

        // Push changes
        \OmegaUp\DAO\Courses::update($originalCourse);

        // TODO: Expire cache

        self::$log->info('Course updated (alias): ' . $r['course_alias']);
        return ['status' => 'ok'];
    }

    /**
     * Gets Scoreboard for an assignment
     *
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiAssignmentScoreboard(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course'], $r['group']);

        if (!$tokenAuthenticationResult['hasToken']) {
            $r->ensureIdentity();
            if (!\OmegaUp\Authorization::canViewCourse(
                $r->identity,
                $tokenAuthenticationResult['course'],
                $group
            )) {
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
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiAssignmentScoreboardEvents(\OmegaUp\Request $r) {
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );

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
     * @param \OmegaUp\Request $r
     * @return array{status: string, user_problems: array{string: array{alias: string, title: string, username: string}[]}[]}
     */
    public static function apiListSolvedProblems(\OmegaUp\Request $r) : array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }
        $solvedProblems = \OmegaUp\DAO\Problems::getSolvedProblemsByUsersOfCourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($solvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
        }
        return ['status' => 'ok', 'user_problems' => $userProblems];
    }

    /**
     * Get Problems unsolved by users of a course
     *
     * @param \OmegaUp\Request $r
     * @return array{status: string, user_problems: array<string, array{alias: string, title: string, username: string}[]>}
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r) : array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');
        $course = self::validateCourseExists($r['course_alias']);

        if (!\OmegaUp\Authorization::isCourseAdmin($r->identity, $course)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }

        $unsolvedProblems = \OmegaUp\DAO\Problems::getUnsolvedProblemsByUsersOfCourse(
            $r['course_alias']
        );
        $userProblems = [];
        foreach ($unsolvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
        }
        return ['status' => 'ok', 'user_problems' => $userProblems];
    }

    /**
     * @param $identity_id
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param \OmegaUp\DAO\VO\Groups $group
     */
    public static function shouldShowScoreboard(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ) : bool {
        return \OmegaUp\Authorization::canViewCourse($identity, $course, $group) &&
               $course->show_scoreboard;
    }
}
