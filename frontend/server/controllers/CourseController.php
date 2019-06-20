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
     * @param Courses $course
     * @param string $assignmentAlias
     * @return Assignments
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function validateCourseAssignmentAlias(Courses $course, string $assignmentAlias) : Assignments {
        try {
            $assignment = CoursesDAO::getAssignmentByAlias($course, $assignmentAlias);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($assignment)) {
            throw new NotFoundException('assignmentNotFound');
        }

        return $assignment;
    }

    /**
     * Validates request for creating a new Assignment
     *
     * @param Courses $course
     * @param Assignments $assignment
     * @throws InvalidParameterException
     */
    private static function validateCreateAssignment(Request $r, Courses $course) : void {
        $isRequired = true;
        $courseStartTime = strtotime($course->start_time);
        $courseFinishTime = strtotime($course->finish_time);

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
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        Validators::validateInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $isRequired);
        Validators::validateValidAlias($r['alias'], 'alias', $isRequired);
    }

    /**
     * Validates clone Courses
     */
    private static function validateClone(Request $r) {
        Validators::validateStringNonEmpty($r['name'], 'name', true);
        $r->ensureInt('start_time', null, null, true);
        Validators::validateValidAlias($r['alias'], 'alias', true);
    }

    /**
     * Validates create Courses
     *
     * @param Request $r
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateCreate(
        Request $r
    ) : void {
        self::validateBasicCreateOrUpdate($r);

        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }
    }

    /**
     * Validates update Courses
     *
     * @param Request $r
     * @param string $courseAlias
     * @return Courses
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateUpdate(
        Request $r,
        string $courseAlias
    ) : Courses {
        self::validateBasicCreateOrUpdate($r, true /*is update*/);

        // Get the actual start and finish time of the course, considering that
        // in case of update, parameters can be optional.
        $originalCourse = self::validateCourseExists($courseAlias);

        $originalCourse->toUnixTime();
        if (is_null($r['start_time'])) {
            $r['start_time'] = $originalCourse->start_time;
        }
        if (is_null($r['finish_time'])) {
            $r['finish_time'] = $originalCourse->finish_time;
        }

        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        return $originalCourse;
    }

    /**
     * Validates basic information of a course
     * @param Request $r
     * @param bool $isUpdate
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateBasicCreateOrUpdate(Request $r, bool $isUpdate = false) : void {
        $isRequired = true;

        Validators::validateStringNonEmpty($r['name'], 'name', $isRequired);
        Validators::validateStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt('start_time', null, null, !$isUpdate);
        $r->ensureInt('finish_time', null, null, !$isUpdate);

        Validators::validateValidAlias($r['alias'], 'alias', $isRequired);

        // Show scoreboard is always optional
        $r->ensureBool('show_scoreboard', false /*isRequired*/);
        $r->ensureBool('public', false /*isRequired*/);

        if (is_null($r['school_id'])) {
            $school = null;
        } else {
            $school = SchoolsDAO::getByPK($r['school_id']);
            if (is_null($school)) {
                throw new InvalidParameterException('schoolNotFound');
            }
        }

        // Only curator can set public
        if (!is_null($r['public'])
            && $r['public'] == true
            && !Authorization::canCreatePublicCourse($r->identity->identity_id)) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects course alias, returns
     * course. Throws if not found.
     * @param string $courseAlias
     * @return Courses
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function validateCourseExists(string $courseAlias) : Courses {
        Validators::validateStringNonEmpty($courseAlias, 'course_alias', true /*is_required*/);
        try {
            $course = CoursesDAO::getByAlias($courseAlias);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($course)) {
            throw new NotFoundException('courseNotFound');
        }
        return $course;
    }

    /**
     * Gets the Group assigned to the Course.
     * @param Courses $course
     * @param Groups $group
     * @return Groups
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function resolveGroup(Courses $course, ?Groups $group) : Groups {
        if (!is_null($group)) {
            return $group;
        }

        try {
            $group = GroupsDAO::getByPK($course->group_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($group)) {
            throw new NotFoundException();
        }
        return $group;
    }

    /**
     * Clone a course
     *
     * @return array
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiClone(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateClone($r);
        $originalCourse = self::validateCourseExists($r['course_alias']);

        $offset = round($r['start_time']) - strtotime($originalCourse->start_time);

        DAO::transBegin();

        try {
            // Create the course (and group)
            $course = CourseController::createCourseAndGroup(new Courses([
                'name' => $r['name'],
                'description' => $originalCourse->description,
                'alias' => $r['alias'],
                'school_id' => is_null($r['school']) ? null : $r['school']->school_id,
                'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
                'finish_time' => gmdate('Y-m-d H:i:s', strtotime($originalCourse->finish_time) + $offset),
                'public' => 0,
                'show_scoreboard' => boolval($r['show_scoreboard']),
                'needs_basic_information' => $r->ensureBool('needs_basic_information', false /*isRequired*/),
                'requests_user_information' => $r['requests_user_information']
            ]), $r->user->user_id);

            $assignmentsProblems = ProblemsetProblemsDAO::getProblemsAssignmentByCourseAlias($originalCourse);

            foreach ($assignmentsProblems as $assignment => $assignmentProblems) {
                // Create and assign homeworks and tests to new course
                $problemset = self::createAssignment($originalCourse, new Assignments([
                    'course_id' => $course->course_id,
                    'acl_id' => $course->acl_id,
                    'name' => $assignmentProblems['name'],
                    'description' => $assignmentProblems['description'],
                    'alias' => $assignmentProblems['assignment_alias'],
                    'publish_time_delay' => $assignmentProblems['publish_time_delay'],
                    'assignment_type' => $assignmentProblems['assignment_type'],
                    'start_time' => gmdate('Y-m-d H:i:s', strtotime($assignmentProblems['start_time']) + $offset),
                    'finish_time' => gmdate('Y-m-d H:i:s', strtotime($assignmentProblems['finish_time']) + $offset),
                ]));

                foreach ($assignmentProblems['problems'] as $problem) {
                    // Create and assign problems to new course
                    self::addProblemToAssignment(
                        $problem['problem_alias'],
                        $problemset->problemset_id,
                        $r->user->user_id,
                        false, /* validateVisibility */
                        100,
                        null,
                        1
                    );
                }
            }
            DAO::transEnd();
        } catch (ApiException $e) {
            DAO::transRollback();
            throw $e;
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    /**
     * Create new course API
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateCreate($r);

        self::createCourseAndGroup(new Courses([
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'school_id' => $r['school_id'] ?? null,
            'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
            'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
            'public' => is_null($r['public']) ? false : $r['public'],
            'show_scoreboard' => boolval($r['show_scoreboard']),
            'needs_basic_information' => $r->ensureBool('needs_basic_information', false /*isRequired*/),
            'requests_user_information' => $r['requests_user_information'],
        ]), $r->user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Function to create a new course with its corresponding group
     *
     * @param Courses $course
     * @param $creatorUserId
     * @return Courses
     */
    private static function createCourseAndGroup(
        Courses $course,
        int $creatorUserId
    ) : Courses {
        if ($course->alias == 'new') {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        if (!is_null(CoursesDAO::getByAlias($course->alias))) {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        DAO::transBegin();

        $group = GroupController::createGroup(
            $course->alias,
            'students-' . $course->alias,
            'students-' . $course->alias,
            $creatorUserId
        );

        try {
            $acl = new ACLs(['owner_id' => $creatorUserId]);

            ACLsDAO::save($acl);

            GroupRolesDAO::create(new GroupRoles([
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'role_id' => Authorization::CONTESTANT_ROLE,
            ]));

            $course->group_id = $group->group_id;
            $course->acl_id = $acl->acl_id;

            CoursesDAO::create($course);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();

            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('titleInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        return $course;
    }

    /**
     * Function to create a new assignment
     *
     * @param Courses $course
     * @param Assignment $assignment
     * @return Problemsets
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    private static function createAssignment(
        Courses $course,
        Assignments $assignment
    ) : Problemsets {
        DAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new Problemsets([
                'acl_id' => $course->acl_id,
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

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }
        return $problemset;
    }

    /**
     * Function to add problems to a specific assignment
     *
     * @param string $problemAlias,
     * @param int $problemsetId,
     * @param int $userId,
     * @param ?int $points = 100,
     * @param ?string $commit
     */
    private static function addProblemToAssignment(
        string $problemAlias,
        int $problemsetId,
        int $userId,
        bool $validateVisibility,
        ?int $points = 100,
        ?string $commit = null,
        ?int $order = 1
    ) : void {
        // Get this problem
        $problem = ProblemsDAO::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
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
            $userId,
            $points,
            $order,
            $validateVisibility
        );
    }

    /**
     * API to Create an assignment
     *
     * @param  Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCreateAssignment(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        self::validateCreateAssignment($r, $course);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        self::createAssignment($course, new Assignments([
            'course_id' => $course->course_id,
            'acl_id' => $course->acl_id,
            'name' => $r['name'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'publish_time_delay' => $r['publish_time_delay'],
            'assignment_type' => $r['assignment_type'],
            'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
            'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Update an assignment
     *
     * @param  Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdateAssignment(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        [$course, $assignment] = self::validateAssignmentDetails(
            $r['course'],
            $r['assignment'],
            $r->identity->identity_id
        );
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
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
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        // Prevent date changes if a course already has runs
        if ($r['start_time'] != $assignment->start_time) {
            $runCount = 0;

            try {
                $runCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                    (int)$assignment->problemset_id
                );
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if ($runCount > 0) {
                throw new InvalidParameterException('courseUpdateAlreadyHasRuns');
            }
        }

        $valueProperties = [
            'name',
            'description',
            'start_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
            'finish_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
            'assignment_type',
        ];
        self::updateValueProperties($r, $assignment, $valueProperties);

        try {
            AssignmentsDAO::update($assignment);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds a problem to an assignment
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAddProblem(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemset = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemset)) {
            throw new NotFoundException('problemsetNotFound');
        }

        $points = 100;
        if (is_numeric($r['points'])) {
            $points = (int)$r['points'];
        }

        self::addProblemToAssignment(
            $r['problem_alias'],
            $problemset->problemset_id,
            $r->identity->identity_id,
            true, /* validateVisibility */
            $points,
            $r['commit']
        );

        try {
            CoursesDAO::updateAssignmentMaxPoints(
                $course,
                $r['assignment_alias']
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdateProblemsOrder(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new NotFoundException('problemsetNotFound');
        }

        // Update problems order
        $problems = $r['problems'];
        foreach ($problems as $problem) {
            $currentProblem = ProblemsDAO::getByAlias($problem['alias']);
            if (is_null($problem)) {
                throw new NotFoundException('problemNotFound');
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdateAssignmentsOrder(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Update assignments order
        foreach ($r['assignments'] as $assignment) {
            $currentAssignment = AssignmentsDAO::getByAliasAndCourse($assignment['alias'], $course->course_id);

            if (empty($currentAssignment)) {
                throw new NotFoundException('assignmentNotFound');
            }

            AssignmentsDAO::updateAssignmentsOrder(
                $currentAssignment->assignment_id,
                (int)$assignment['order']
            );
        }

        return ['status' => 'ok'];
    }

    public static function apiGetProblemUsers(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRemoveProblem(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new NotFoundException('problemsetNotFound');
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        // Delete the entry from the database.
        $problemsetProblem = ProblemsetProblemsDAO::getByPK(
            $problemSet->problemset_id,
            $problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new NotFoundException('problemNotPartOfAssignment');
        }
        if (SubmissionsDAO::countTotalRunsOfProblemInProblemset(
            (int)$problem->problem_id,
            (int)$problemSet->problemset_id
        ) > 0 &&
            !Authorization::isSystemAdmin($r->identity->identity_id)) {
            throw new ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }
        ProblemsetProblemsDAO::delete($problemsetProblem);

        try {
            CoursesDAO::updateAssignmentMaxPoints(
                $course,
                $r['assignment_alias']
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * List course assignments
     *
     * @throws InvalidParameterException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListAssignments(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r->identity->identity_id,
            $course,
            $group
        )) {
            throw new ForbiddenAccessException();
        }

        try {
            $assignments = AssignmentsDAO::getSortedCourseAssignments(
                $course->course_id
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $isAdmin = Authorization::isCourseAdmin(
            $r->identity->identity_id,
            $course
        );

        $response = [
            'status' => 'ok',
            'assignments' => [],
        ];
        $time = Time::get();
        foreach ($assignments as $assignment) {
            try {
                $assignment['has_runs'] = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                    (int)$assignment['problemset_id']
                ) > 0;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
            unset($assignment['problemset_id']);
            if (!$isAdmin && $assignment['start_time'] > $time) {
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRemoveAssignment(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::getProblemset(
            $$course->course_id,
            $r['assignment_alias']
        );
        if (is_null($problemSet)) {
            throw new NotFoundException('problemsetNotFound');
        }

        throw new UnimplementedException();
    }

    /**
     * Converts a Course object into an array
     * @param  Course $course
     * @return array
     */
    private static function convertCourseToArray(Courses $course) : array {
        $course->toUnixTime();
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
     * @throws InvalidParameterException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListCourses(Request $r) {
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
        try {
            if (Authorization::isSystemAdmin($r->identity->identity_id)) {
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
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // Courses the user is a student in.
        $student_courses = [];
        try {
            $student_courses = CoursesDAO::getCoursesForStudent($r->identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

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
     * List students in a course
     *
     * @param  Request $r
     * @return array response
     */
    public static function apiListStudents(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        $students = null;
        try {
            $students = CoursesDAO::getStudentsInCourseWithProgressPerAssignment(
                $course->course_id,
                $course->group_id
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'students' => $students,
            'status' => 'ok',
        ];
    }

    public static function apiStudentProgress(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        if (is_null(GroupsIdentitiesDAO::getByPK(
            $course->group_id,
            $r['identity']->identity_id
        ))) {
            throw new NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
            $r['assignment_alias'],
            $course->course_id
        );
        if (is_null($r['assignment'])) {
            throw new NotFoundException('assignmentNotFound');
        }
        $r['assignment']->toUnixTime();

        $problems = ProblemsetProblemsDAO::getProblems($r['assignment']->problemset_id);
        $letter = 0;
        foreach ($problems as &$problem) {
            $runsArray = RunsDAO::getForProblemDetails(
                (int)$problem['problem_id'],
                (int)$r['assignment']->problemset_id,
                (int)$r['identity']->identity_id
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
     * @param  Request $r
     * @return array
     */
    public static function apiMyProgress(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r->identity->identity_id,
            $course,
            $group
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = null;

        try {
            $assignments = CoursesDAO::getAssignmentsProgress(
                $course->course_id,
                $r->identity->identity_id
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'assignments' => $assignments,
        ];
    }

    /**
     * Add Student to Course.
     *
     * @param  Request $r
     * @return array
     */
    public static function apiAddStudent(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        // Only course admins or users adding themselves when the course is public
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)
            && ($course->public == false
            || $r['identity']->identity_id !== $r->identity->identity_id)
            && $course->requests_user_information == 'no'
            && is_null($r['accept_teacher'])
        ) {
            throw new ForbiddenAccessException();
        }

        $groupIdentity = new GroupsIdentities([
            'group_id' => $course->group_id,
            'identity_id' => $r['identity']->identity_id,
            'share_user_information' => $r['share_user_information'],
            'accept_teacher' => $r['accept_teacher'],
        ]);

        DAO::transBegin();

        try {
            GroupsIdentitiesDAO::save(new GroupsIdentities([
                'group_id' => $course->group_id,
                'identity_id' => $r['identity']->identity_id
            ]));

            // Only users adding themselves are saved in consent log
            if ($r['identity']->identity_id === $r->identity->identity_id
                 && $course->requests_user_information != 'no') {
                $privacystatement_id = PrivacyStatementsDAO::getId($r['privacy_git_object_id'], $r['statement_type']);
                if (!PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement($r['identity']->identity_id, $privacystatement_id)) {
                    $privacystatement_consent_id = PrivacyStatementConsentLogDAO::saveLog(
                        $r['identity']->identity_id,
                        $privacystatement_id
                    );
                } else {
                    $privacystatement_consent_id = PrivacyStatementConsentLogDAO::getId($r['identity']->identity_id, $privacystatement_id);
                }

                $groupIdentity->privacystatement_consent_id = $privacystatement_consent_id;
            }
            if ($r['identity']->identity_id === $r->identity->identity_id
                 && !empty($r['accept_teacher'])) {
                $privacystatement_id = PrivacyStatementsDAO::getId($r['accept_teacher_git_object_id'], 'accept_teacher');
                if (!PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement($r['identity']->identity_id, $privacystatement_id)) {
                    PrivacyStatementConsentLogDAO::saveLog(
                        $r['identity']->identity_id,
                        $privacystatement_id
                    );
                }
            }
            GroupsIdentitiesDAO::save($groupIdentity);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Remove Student from Course
     *
     * @param  Request $r
     * @return array
     */
    public static function apiRemoveStudent(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        if (is_null(GroupsIdentitiesDAO::getByPK(
            $course->group_id,
            $r['identity']->identity_id
        ))) {
            throw new NotFoundException('courseStudentNotInCourse');
        }

        try {
            GroupsIdentitiesDAO::delete(new GroupsIdentities([
                'group_id' => $course->group_id,
                'identity_id' => $r['identity']->identity_id,
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns all course administrators
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdmins(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddAdmin(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only director is allowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addUser($course->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a course
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::isCourseAdmin($user->main->user_id, $course)) {
            throw new NotFoundException();
        }

        ACLController::removeUser($course->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a course
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admins are allowed to modify course
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::addGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a course
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check course_alias
        Validators::validateStringNonEmpty($r['course_alias'], 'course_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        ACLController::removeGroup($course->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     * @param  Request $r
     * @throws NotFoundException Course not found or trying to directly access a private course.
     * @throws ForbiddenAccessException
     * @return array
     */
    public static function apiIntroDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);
        $group = self::resolveGroup($course, $r['group']);

        $shouldShowIntro = !Authorization::canViewCourse($r->identity->identity_id, $course, $group);
        $isFirstTimeAccess = false;
        $showAcceptTeacher = false;
        if (!Authorization::isGroupAdmin($r->identity->identity_id, $group)) {
            $sharingInformation = CoursesDAO::getSharingInformation($r->identity->identity_id, $course, $group);
            $isFirstTimeAccess = $sharingInformation['share_user_information'] == null;
            $showAcceptTeacher = $sharingInformation['accept_teacher'] == null;
        }
        if ($shouldShowIntro && !$course->public) {
            throw new ForbiddenAccessException();
        }

        $user_session = SessionController::apiCurrentSession($r)['session']['user'];
        $result = self::getCommonCourseDetails($course, $r->identity->identity_id, true /*onlyIntroDetails*/);
        $result['showAcceptTeacher'] = $showAcceptTeacher;

        // Privacy Statement Information
        $result['privacy_statement_markdown'] = PrivacyStatement::getForProblemset(
            $user_session->language_id,
            'course',
            $result['requests_user_information']
        );
        $result['git_object_id'] = null;
        $result['statement_type'] = null;
        if (!is_null($result['privacy_statement_markdown'])) {
            $statement_type = "course_{$result['requests_user_information']}_consent";
            $result['git_object_id'] = PrivacyStatementsDAO::getLatestPublishedStatement($statement_type)['git_object_id'];
            $result['statement_type'] = $statement_type;
        }

        $markdown = PrivacyStatement::getForConsent($user_session->language_id, 'accept_teacher');
        if (is_null($markdown)) {
            throw new InvalidFilesystemOperationException();
        }
        $result['accept_teacher_statement'] = [
            'git_object_id' => PrivacyStatementsDAO::getLatestPublishedStatement('accept_teacher')['git_object_id'],
            'markdown' => $markdown,
            'statement_type' => 'accept_teacher',
        ];
        $result['shouldShowResults'] = $shouldShowIntro;
        $result['isFirstTimeAccess'] = $isFirstTimeAccess;
        $result['requests_user_information'] = $result['requests_user_information'];

        return $result;
    }

    /**
     * Returns course details common between admin & non-admin
     * @param Courses $course
     * @param int $currentIdentityId
     * @param bool $onlyIntroDetails
     * @return array
     */
    private static function getCommonCourseDetails(
        Courses $course,
        int $currentIdentityId,
        bool $onlyIntroDetails
    ) : array {
        $isAdmin = Authorization::isCourseAdmin(
            $currentIdentityId,
            $course
        );

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
                'start_time' => strtotime($course->start_time),
                'finish_time' => strtotime($course->finish_time),
                'is_admin' => $isAdmin,
                'public' => $course->public,
                'basic_information_required' => boolval($course->needs_basic_information),
                'show_scoreboard' => boolval($course->show_scoreboard),
                'requests_user_information' => $course->requests_user_information
            ];

            if ($isAdmin) {
                try {
                    $group = GroupsDAO::getByPK($course->group_id);
                } catch (Exception $e) {
                    throw new InvalidDatabaseOperationException($e);
                }
                if (is_null($group)) {
                    throw new NotFoundException('courseGroupNotFound');
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
     * @param  Request $r
     * @return array
     */
    public static function apiAdminDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity->identity_id, false /*onlyIntroDetails*/);
    }

    /**
     * Returns a report with all user activity for a course.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiActivityReport(Request $r) {
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
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
     * @param  Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function authenticateAndValidateToken(
        string $courseAlias,
        string $assignmentAlias,
        ?string $token,
        Request $r
    ) : array {
        if (is_null($token)) {
            self::authenticateRequest($r);
            [$course, $assignment] = self::validateAssignmentDetails(
                $courseAlias,
                $assignmentAlias,
                $r->identity->identity_id
            );

            return [
                'hasToken' => false,
                'courseAdmin' => Authorization::isCourseAdmin($r->identity->identity_id, $course),
                'assignment' => $assignment,
                'course' => $course,
            ];
        }

        $courseAdmin = false;

        $course = self::validateCourseExists($courseAlias);
        $course->toUnixTime();
        $assignment = self::validateCourseAssignmentAlias($course, $assignmentAlias);
        $assignment->toUnixTime();

        try {
            $assignmentProblemset = AssignmentsDAO::getByIdWithScoreboardUrls($assignment->assignment_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($assignmentProblemset)) {
            throw new NotFoundException('assignmentNotFound');
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
     * @param  int $currentIdentityId
     * @return array
     */
    private static function validateAssignmentDetails(
        string $courseAlias,
        string $assignmentAlias,
        int $currentIdentityId
    ) : array {
        Validators::validateStringNonEmpty($courseAlias, 'course', true /* is_required */);
        Validators::validateStringNonEmpty($assignmentAlias, 'assignment', true /* is_required */);
        $course = CoursesDAO::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new NotFoundException('courseNotFound');
        }
        $course->toUnixTime();
        $assignment = AssignmentsDAO::getByAliasAndCourse($assignmentAlias, $course->course_id);
        if (is_null($assignment)) {
            throw new NotFoundException('assignmentNotFound');
        }
        $assignment->toUnixTime();

        // Admins are almighty, no need to check anything else.
        if (Authorization::isCourseAdmin($currentIdentityId, $course)) {
            return [$course, $assignment];
        }

        if ($assignment->start_time > Time::get() ||
            !GroupRolesDAO::isContestant($currentIdentityId, $assignment->acl_id)
        ) {
            throw new ForbiddenAccessException();
        }
        return [$course, $assignment];
    }

    /**
     * Returns details of a given assignment
     * @param  Request $r
     * @return array
     */
    public static function apiAssignmentDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );

        $problems = ProblemsetProblemsDAO::getProblems(
            $tokenAuthenticationResult['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $problem['letter'] = ContestController::columnName($letter++);
            unset($problem['problem_id']);
        }

        $director = null;
        try {
            $acl = ACLsDAO::getByPK($tokenAuthenticationResult['course']->acl_id);
            $director = UsersDAO::getByPK($acl->owner_id)->username;
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Log the operation only when there is not a token in request
        if (!$tokenAuthenticationResult['hasToken']) {
            ProblemsetAccessLogDAO::create(new ProblemsetAccessLog([
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRuns(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        // Validate request
        self::validateRuns($r);

        // Get our runs
        try {
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
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateRuns(Request $r) : void {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }
        Validators::validateStringNonEmpty($r['assignment_alias'], 'assignment_alias');

        $course = self::validateCourseExists($r['course_alias']);

        try {
            $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
                $r['assignment_alias'],
                $course->course_id
            );
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['assignment'])) {
            throw new NotFoundException('assignmentNotFound');
        }

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            try {
                $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['problem'])) {
                throw new NotFoundException('problemNotFound');
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
     * @param  Request $r
     * @return array
     */
    public static function apiDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['alias']);
        $group = self::resolveGroup($course, $r['group']);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r->identity->identity_id,
            $course,
            $group
        )) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($course, $r->identity->identity_id, false /*onlyIntroDetails*/);
    }

    /**
     * Edit Course contents
     *
     * @param  Request $r
     * @return array
     */
    public static function apiUpdate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $originalCourse = self::validateUpdate($r, $r['course_alias']);
        if (!Authorization::isCourseAdmin($r->identity->identity_id, $originalCourse)) {
            throw new ForbiddenAccessException();
        }

        $valueProperties = [
            'alias',
            'name',
            'description',
            'start_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
            'finish_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
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
        try {
            CoursesDAO::update($originalCourse);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // TODO: Expire cache

        self::$log->info('Course updated (alias): ' . $r['course_alias']);
        return ['status' => 'ok'];
    }

    /**
     * Gets Scoreboard for an assignment
     *
     * @param  Request $r
     * @return array
     */
    public static function apiAssignmentScoreboard(Request $r) {
        $tokenAuthenticationResult = self::authenticateAndValidateToken(
            $r['course'],
            $r['assignment'],
            $r['token'],
            $r
        );
        $group = self::resolveGroup($tokenAuthenticationResult['course'], $r['group']);

        if (!$tokenAuthenticationResult['hasToken'] &&
            !Authorization::canViewCourse($r->identity->identity_id, $tokenAuthenticationResult['course'], $group)) {
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function apiAssignmentScoreboardEvents(Request $r) {
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
     * @param Request $r
     * @return Problems array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListSolvedProblems(Request $r) {
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
        try {
            $solvedProblems = ProblemsDAO::getSolvedProblemsByUsersOfCourse($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        $userProblems = [];
        foreach ($solvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
        }
        return ['status' => 'ok', 'user_problems' => $userProblems];
    }

    /**
     * Get Problems unsolved by users of a course
     *
     * @param Request $r
     * @return Problems array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListUnsolvedProblems(Request $r) {
        self::authenticateRequest($r);
        $course = self::validateCourseExists($r['course_alias']);

        if (!Authorization::isCourseAdmin($r->identity->identity_id, $course)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        try {
            $unsolvedProblems = ProblemsDAO::getUnsolvedProblemsByUsersOfCourse($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        $userProblems = [];
        foreach ($unsolvedProblems as $problem) {
            $userProblems[$problem['username']][] = $problem;
        }
        return ['status' => 'ok', 'user_problems' => $userProblems];
    }

    /**
     * @param $identity_id
     * @param Courses $course
     * @param Groups $group
     */
    public static function shouldShowScoreboard(
        int $identityId,
        Courses $course,
        Groups $group
    ) : bool {
        return Authorization::canViewCourse($identityId, $course, $group) &&
            $course->show_scoreboard;
    }
}
