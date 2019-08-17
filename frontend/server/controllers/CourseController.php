<?php

require_once 'libs/ActivityReport.php';
require_once 'libs/PrivacyStatement.php';

/**
 *  CourseController
 *
 * @author alanboy
 * @author pablo.aguilar
 * @author lhchavez
 * @author joemmanuel
 */
class CourseController extends Controller {
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
        $assignment = CoursesDAO::getAssignmentByAlias($course, $assignmentAlias);
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

        Validators::validateStringNonEmpty($r['name'], 'name', $isRequired);
        Validators::validateStringNonEmpty($r['description'], 'description', $isRequired);

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

        Validators::validateInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $isRequired);
        Validators::validateValidAlias($r['alias'], 'alias', $isRequired);
    }

    /**
     * Validates clone Courses
     */
    private static function validateClone(\OmegaUp\Request $r) {
        Validators::validateStringNonEmpty($r['name'], 'name', true);
        $r->ensureInt('start_time', null, null, true);
        Validators::validateValidAlias($r['alias'], 'alias', true);
    }

    /**
     * Validates create Courses
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws ForbiddenAccessException
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
     * @throws ForbiddenAccessException
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
     * @throws ForbiddenAccessException
     */
    private static function validateBasicCreateOrUpdate(\OmegaUp\Request $r, bool $isUpdate = false) : void {
        $isRequired = true;

        Validators::validateStringNonEmpty($r['name'], 'name', $isRequired);
        Validators::validateStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt('start_time', null, null, !$isUpdate);
        $r->ensureInt('finish_time', null, null, !$isUpdate);

        Validators::validateValidAlias($r['alias'], 'alias', $isRequired);

        // Show scoreboard, needs basic information and request user information are always optional
        $r->ensureBool('needs_basic_information', false /*isRequired*/);
        $r->ensureBool('show_scoreboard', false /*isRequired*/);
        Validators::validateInEnum(
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
            $school = SchoolsDAO::getByPK($r['school_id']);
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('schoolNotFound');
            }
        }

        // Only curator can set public
        if (!is_null($r['public'])
            && $r['public'] == true
            && !Authorization::canCreatePublicCourse($r->identity)) {
            throw new ForbiddenAccessException();
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
        Validators::validateStringNonEmpty($courseAlias, 'course_alias', true /*is_required*/);
        $course = CoursesDAO::getByAlias($courseAlias);
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

        $group = GroupsDAO::getByPK($course->group_id);
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
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiClone(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r, true /* requireMainUserIdentity */);
        self::validateClone($r);
        $originalCourse = self::validateCourseExists($r['course_alias']);

        $offset = round($r['start_time']) - $originalCourse->start_time;

        \OmegaUp\DAO\DAO::transBegin();

        try {
            // Create the course (and group)
            $course = CourseController::createCourseAndGroup(new \OmegaUp\DAO\VO\Courses([
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

            $assignmentsProblems = ProblemsetProblemsDAO::getProblemsAssignmentByCourseAlias($originalCourse);

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
                    'start_time' => $assignmentProblems['start_time'] + $offset,
                    'finish_time' => $assignmentProblems['finish_time'] + $offset,
                ]));

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
        } catch (Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    /**
     * Create new course API
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r, true /* requireMainUserIdentity */);
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
        if (!is_null(CoursesDAO::getByAlias($course->alias))) {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        \OmegaUp\DAO\DAO::transBegin();

        $group = GroupController::createGroup(
            $course->alias,
            "students-{$course->alias}",
            "students-{$course->alias}",
            $creator->user_id
        );

        try {
            $acl = new \OmegaUp\DAO\VO\ACLs(['owner_id' => $creator->user_id]);
            ACLsDAO::create($acl);

            GroupRolesDAO::create(new \OmegaUp\DAO\VO\GroupRoles([
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'role_id' => Authorization::CONTESTANT_ROLE,
            ]));

            $course->group_id = $group->group_id;
            $course->acl_id = $acl->acl_id;

            CoursesDAO::create($course);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('titleInUse', $e);
            }
            throw $e;
        }
        return $course;
    }

    /**
     * Function to create a new assignment
     *
     * @param \OmegaUp\DAO\VO\Courses $course
     * @param Assignment $assignment
     * @return \OmegaUp\DAO\VO\Problemsets
     * @throws DuplicatedEntryInDatabaseException
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
                'scoreboard_url' => SecurityTools::randomString(30),
                'scoreboard_url_admin' => SecurityTools::randomString(30),
            ]);

            ProblemsetsDAO::create($problemset);
            $assignment->problemset_id = $problemset->problemset_id;

            AssignmentsDAO::create($assignment);

            // Update assignment_id in problemset object
            $problemset->assignment_id = $assignment->assignment_id;
            ProblemsetsDAO::update($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
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
        $problem = ProblemsDAO::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        [$masterCommit, $currentVersion] = ProblemController::resolveCommit(
            $problem,
            $commit
        );

        ProblemsetController::addProblem(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        self::validateCreateAssignment($r, $course);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        [$course, $assignment] = self::validateAssignmentDetails(
            $r['course'],
            $r['assignment'],
            $r->identity
        );
        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
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

            $runCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                (int)$assignment->problemset_id
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

        AssignmentsDAO::update($assignment);

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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        $points = 100;
        if (is_numeric($r['points'])) {
            $points = (int)$r['points'];
        }

        self::addProblemToAssignment(
            $r['problem_alias'],
            $problemset->problemset_id,
            $r->identity,
            true, /* validateVisibility */
            $points,
            $r['commit']
        );

        CoursesDAO::updateAssignmentMaxPoints(
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        // Update problems order
        $problems = $r['problems'];
        foreach ($problems as $problem) {
            $currentProblem = ProblemsDAO::getByAlias($problem['alias']);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
            }

            $order = 1;
            if (is_numeric($r['order'])) {
                $order = (int)$r['order'];
            }
            ProblemsetProblemsDAO::updateProblemsOrder(
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Update assignments order
        foreach ($r['assignments'] as $assignment) {
            $currentAssignment = AssignmentsDAO::getByAliasAndCourse($assignment['alias'], $course->course_id);

            if (empty($currentAssignment)) {
                throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
            }

            AssignmentsDAO::updateAssignmentsOrder(
                $currentAssignment->assignment_id,
                (int)$assignment['order']
            );
        }

        return ['status' => 'ok'];
    }

    public static function apiGetProblemUsers(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $identities = ProblemsDAO::getIdentitiesInGroupWhoAttemptedProblem(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        // Delete the entry from the database.
        $problemsetProblem = ProblemsetProblemsDAO::getByPK(
            $problemSet->problemset_id,
            $problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotPartOfAssignment');
        }
        if (SubmissionsDAO::countTotalRunsOfProblemInProblemset(
            (int)$problem->problem_id,
            (int)$problemSet->problemset_id
        ) > 0 &&
            !Authorization::isSystemAdmin($r->identity)) {
            throw new ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }
        ProblemsetProblemsDAO::delete($problemsetProblem);

        CoursesDAO::updateAssignmentMaxPoints(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = AssignmentsDAO::getSortedCourseAssignments(
            $course->course_id
        );

        $response = [
            'status' => 'ok',
            'assignments' => [],
        ];
        $time = \OmegaUp\Time::get();
        foreach ($assignments as $assignment) {
            $assignment['has_runs'] = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                (int)$assignment['problemset_id']
            ) > 0;
            unset($assignment['problemset_id']);
            if ($assignment['start_time'] > $time &&
                !Authorization::isCourseAdmin($r->identity, $course)
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $$course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemsetNotFound');
        }

        throw new UnimplementedException();
    }

    /**
     * Converts a Course object into an array
     * @param  Course $course
     * @return array
     */
    private static function convertCourseToArray(\OmegaUp\DAO\VO\Courses $course) : array {
        $relevant_columns = ['alias', 'name', 'start_time', 'finish_time'];
        $arr = $course->asFilteredArray($relevant_columns);

        $arr['counts'] = AssignmentsDAO::getAssignmentCountsForCourse(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // TODO(pablo): Cache
        // Courses the user is an admin for.
        $admin_courses = [];
        if (Authorization::isSystemAdmin($r->identity)) {
            $admin_courses = CoursesDAO::getAll(
                $page,
                $pageSize,
                'course_id',
                'DESC'
            );
        } else {
            $admin_courses = CoursesDAO::getAllCoursesAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        // Courses the user is a student in.
        $student_courses = CoursesDAO::getCoursesForStudent($r->identity->identity_id);

        $response = [
            'admin' => [],
            'student' => [],
            'status' => 'ok'
        ];
        foreach ($admin_courses as $course) {
            $response['admin'][] = CourseController::convertCourseToArray($course);
        }
        foreach ($student_courses as $course) {
            $response['student'][] = CourseController::convertCourseToArray($course);
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
            throw new ForbiddenAccessException('lockdown');
        }

        $identity = SessionController::apiCurrentSession($r)['session']['identity'];

        // User doesn't have activity because is not logged.
        if (is_null($identity)) {
            return false;
        }

        if (!empty(CoursesDAO::getCoursesForStudent($identity->identity_id))) {
            return true;
        }

        // Default values to search courses for legged user
        $page = 1;
        $pageSize = 1;
        if (Authorization::isSystemAdmin($identity)) {
            $result = CoursesDAO::getAll($page, $pageSize, 'course_id', 'DESC');
            if (!empty($result)) {
                return true;
            }
        }
        $result = CoursesDAO::getAllCoursesAdminedByIdentity(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        $students = CoursesDAO::getStudentsInCourseWithProgressPerAssignment(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null(GroupsIdentitiesDAO::getByPK(
            $course->group_id,
            $resolvedIdentity->identity_id
        ))) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($r['assignment'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        $problems = ProblemsetProblemsDAO::getProblemsByProblemset(
            $r['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $runsArray = RunsDAO::getForProblemDetails(
                (int)$problem['problem_id'],
                (int)$r['assignment']->problemset_id,
                (int)$resolvedIdentity->identity_id
            );
            $problem['runs'] = [];
            foreach ($runsArray as $run) {
                $run['time'] = (int)$run['time'];
                $run['contest_score'] = (float)$run['contest_score'];
                try {
                    $run['source'] = SubmissionController::getSource($run['guid']);
                } catch (Exception $e) {
                    self::$log->error("Error fetching source for {$run['guid']}", $e);
                }
                array_push($problem['runs'], $run);
            }
            unset($problem['problem_id']);
            $problem['letter'] = ContestController::columnName($letter++);
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = CoursesDAO::getAssignmentsProgress(
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        // Only course admins or users adding themselves when the course is public
        if (!Authorization::isCourseAdmin($r->identity, $course)
            && ($course->public == false
            || $resolvedIdentity->identity_id !== $r->identity->identity_id)
            && $course->requests_user_information == 'no'
            && is_null($r['accept_teacher'])
        ) {
            throw new ForbiddenAccessException();
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
                $privacystatement_id = PrivacyStatementsDAO::getId($r['privacy_git_object_id'], $r['statement_type']);
                if (!PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement($resolvedIdentity->identity_id, $privacystatement_id)) {
                    $privacystatement_consent_id = PrivacyStatementConsentLogDAO::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacystatement_id
                    );
                } else {
                    $privacystatement_consent_id = PrivacyStatementConsentLogDAO::getId($resolvedIdentity->identity_id, $privacystatement_id);
                }

                $groupIdentity->privacystatement_consent_id = $privacystatement_consent_id;
            }
            if ($resolvedIdentity->identity_id === $r->identity->identity_id
                 && !empty($r['accept_teacher'])) {
                $privacystatement_id = PrivacyStatementsDAO::getId($r['accept_teacher_git_object_id'], 'accept_teacher');
                if (!PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement($resolvedIdentity->identity_id, $privacystatement_id)) {
                    PrivacyStatementConsentLogDAO::saveLog(
                        $resolvedIdentity->identity_id,
                        $privacystatement_id
                    );
                }
            }
            GroupsIdentitiesDAO::replace($groupIdentity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        if (is_null(GroupsIdentitiesDAO::getByPK(
            $course->group_id,
            $resolvedIdentity->identity_id
        ))) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseStudentNotInCourse');
        }

        GroupsIdentitiesDAO::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
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
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $course = CoursesDAO::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        return [
            'status' => 'ok',
            'admins' => UserRolesDAO::getCourseAdmins($course),
            'group_admins' => GroupRolesDAO::getCourseAdmins($course)
        ];
    }

    /**
     * Adds an admin to a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiAddAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $resolvedUser = UserController::resolveUser($r['usernameOrEmail']);

        $course = CoursesDAO::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only director is allowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addUser($course->acl_id, $resolvedUser->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $resolvedIdentity = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($resolvedIdentity->user_id)) {
            // Unassociated identities can't be course admins
            throw new ForbiddenAccessException();
        }
        $resolvedUser = UsersDAO::getByPK($resolvedIdentity->user_id);

        $course = CoursesDAO::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::isCourseAdmin($resolvedIdentity, $course)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        ACLController::removeUser($course->acl_id, $resolvedUser->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = GroupsDAO::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $course = CoursesDAO::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admins are allowed to modify course
        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a course
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = GroupsDAO::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $course = CoursesDAO::getByAlias($r['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::removeGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     * @param  \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\NotFoundException Course not found or trying to directly access a private course.
     * @throws ForbiddenAccessException
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
            throw new ForbiddenAccessException('lockdown');
        }
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);
        $showAssignment = !empty($r['assignment_alias']);
        $shouldShowIntro = !Authorization::canViewCourse(
            $r->identity,
            $course,
            $group
        );
        $isFirstTimeAccess = false;
        $shouldShowAcceptTeacher = false;
        if (!Authorization::isGroupAdmin($r->identity, $group)) {
            $sharingInformation = CoursesDAO::getSharingInformation(
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
            throw new ForbiddenAccessException();
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
                && !is_null($r->identity) && (!is_null($r->identity->country_id)
                || !is_null($r->identity->state_id) || !is_null($r->identity->school_id));

            // Privacy Statement Information
            $privacyStatementMarkdown = PrivacyStatement::getForProblemset(
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
                    PrivacyStatementsDAO::getLatestPublishedStatement(
                        $statementType
                    )['git_object_id'];
                $privacyStatement['statementType'] = $statementType;
            }

            $markdown = PrivacyStatement::getForConsent(
                $r->identity->language_id,
                'accept_teacher'
            );
            if (is_null($markdown)) {
                throw new InvalidFilesystemOperationException();
            }
            $acceptTeacherStatement = [
                'gitObjectId' =>
                    PrivacyStatementsDAO::getLatestPublishedStatement(
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
                'showRanking' => !is_null($r->identity) &&
                    CourseController::shouldShowScoreboard(
                        $r->identity,
                        $course,
                        $group
                    ),
                'payload' => ['shouldShowFirstAssociatedIdentityRunWarning' =>
                    !is_null($r->user) &&
                    !UserController::isMainIdentity(
                        $r->user,
                        $r->identity
                    ) &&
                    ProblemsetsDAO::shouldShowFirstAssociatedIdentityRunWarning(
                        $r->user
                    ),
                ],
            ];
            $template = 'arena.contest.course.tpl';
        } else {
            $smartyProperties = [
                'showRanking' => !is_null($r->identity) &&
                    Authorization::isCourseAdmin($r->identity, $course)
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
        $isAdmin = Authorization::isCourseAdmin($identity, $course);

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
                'assignments' => CoursesDAO::getAllAssignments($course->alias, $isAdmin),
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
                $group = GroupsDAO::getByPK($course->group_id);
                if (is_null($group)) {
                    throw new \OmegaUp\Exceptions\NotFoundException('courseGroupNotFound');
                }
                $result['student_count'] = GroupsIdentitiesDAO::GetMemberCountById(
                    $group->group_id
                );
            }
            if (!is_null($course->school_id)) {
                $school = SchoolsDAO::getByPK($course->school_id);
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
            throw new ForbiddenAccessException('lockdown');
        }
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
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
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException();
        }

        $accesses = ProblemsetAccessLogDAO::GetAccessForCourse($course->course_id);
        $submissions = SubmissionLogDAO::GetSubmissionsForCourse($course->course_id);

        return ActivityReport::getActivityReport($accesses, $submissions);
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
     * @throws ForbiddenAccessException
     */
    private static function authenticateAndValidateToken(
        string $courseAlias,
        string $assignmentAlias,
        ?string $token,
        \OmegaUp\Request $r
    ) : array {
        if (is_null($token)) {
            self::authenticateRequest($r);
            [$course, $assignment] = self::validateAssignmentDetails(
                $courseAlias,
                $assignmentAlias,
                $r->identity
            );

            return [
                'hasToken' => false,
                'courseAdmin' => Authorization::isCourseAdmin(
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

        $assignmentProblemset = AssignmentsDAO::getByIdWithScoreboardUrls($assignment->assignment_id);
        if (is_null($assignmentProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        if ($token === $assignmentProblemset['scoreboard_url_admin']) {
            $courseAdmin = true;
        } elseif ($token !== $assignmentProblemset['scoreboard_url']) {
            throw new ForbiddenAccessException('invalidScoreboardUrl');
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
        string $courseAlias,
        string $assignmentAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : array {
        Validators::validateStringNonEmpty($courseAlias, 'course', true /* is_required */);
        Validators::validateStringNonEmpty($assignmentAlias, 'assignment', true /* is_required */);
        $course = CoursesDAO::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $assignment = AssignmentsDAO::getByAliasAndCourse($assignmentAlias, $course->course_id);
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        // Admins are almighty, no need to check anything else.
        if (Authorization::isCourseAdmin($identity, $course)) {
            return [$course, $assignment];
        }

        if ($assignment->start_time > \OmegaUp\Time::get() ||
            !GroupRolesDAO::isContestant($identity->identity_id, $assignment->acl_id)
        ) {
            throw new ForbiddenAccessException();
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
            throw new ForbiddenAccessException('lockdown');
        }

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );

        $problems = ProblemsetProblemsDAO::getProblemsByProblemset(
            $tokenAuthenticationResult['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $problem['letter'] = ContestController::columnName($letter++);
            unset($problem['problem_id']);
        }

        $director = null;
        $acl = ACLsDAO::getByPK($tokenAuthenticationResult['course']->acl_id);
        $director = UsersDAO::getByPK($acl->owner_id)->username;

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            ProblemsetAccessLogDAO::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
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
        self::authenticateRequest($r);

        // Validate request
        self::validateRuns($r);

        // Get our runs
        $runs = RunsDAO::getAllRuns(
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
            $run['time'] = (int)$run['time'];
            $run['score'] = (float)$run['score'];
            $run['contest_score'] = (float)$run['contest_score'];
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
     * @throws ForbiddenAccessException
     */
    private static function validateRuns(\OmegaUp\Request $r) : void {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }
        Validators::validateStringNonEmpty($r['assignment_alias'], 'assignment_alias');

        $course = self::validateCourseExists($r['course_alias']);

        $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($r['assignment'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('assignmentNotFound');
        }

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);

            if (is_null($r['problem'])) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
            }
        }

        Validators::validateInEnum($r['language'], 'language', array_keys(RunController::$kSupportedLanguages), false);

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            $r['identity'] = IdentityController::resolveIdentity($r['username']);
        }
    }

    /**
     * Returns details of a given course
     * @param  \OmegaUp\Request $r
     * @return array
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse($r->identity, $course, $group)) {
            throw new ForbiddenAccessException();
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
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $originalCourse = self::validateUpdate($r, $r['course_alias']);
        if (!Authorization::isCourseAdmin($r->identity, $originalCourse)) {
            throw new ForbiddenAccessException();
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
        CoursesDAO::update($originalCourse);

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
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course'], $r['group']);

        if (!$tokenAuthenticationResult['hasToken'] &&
            !Authorization::canViewCourse(
                $r->identity,
                $tokenAuthenticationResult['course'],
                $group
            )) {
            throw new ForbiddenAccessException();
        }

        $scoreboard = new Scoreboard(
            new ScoreboardParams([
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

        $scoreboard = new Scoreboard(
            ScoreboardParams::fromAssignment(
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
     * @return \OmegaUp\DAO\VO\Problems array
     */
    public static function apiListSolvedProblems(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
        $solvedProblems = ProblemsDAO::getSolvedProblemsByUsersOfCourse($r['course_alias']);
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
     * @return \OmegaUp\DAO\VO\Problems array
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $unsolvedProblems = ProblemsDAO::getUnsolvedProblemsByUsersOfCourse($r['course_alias']);
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
        return Authorization::canViewCourse($identity, $course, $group) &&
               $course->show_scoreboard;
    }
}
