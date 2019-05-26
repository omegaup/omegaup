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
     * Validate course_alias exists and set into $r['course']
     *
     * @param  Request $r
     * @return array
     */
    private static function validateCourseAlias(Request $r) {
        try {
            $r['course'] = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['course'])) {
            throw new NotFoundException('courseNotFound');
        }
        return $r['course'];
    }

    /**
     * Validate assignment_alias existis into the course and set into $r['assignment']
     *
     * @param  Request $r
     * @return array
     */
    private static function validateCourseAssignmentAlias(Request $r) {
        try {
            $r['assignment'] = CoursesDAO::getAssignmentByAlias($r['course'], $r['assignment_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['assignment'])) {
            throw new NotFoundException('assignmentNotFound');
        }
    }

    /**
     * Validates request for creating a new Assignment
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    private static function validateCreateAssignment(Request $r) {
        $is_required = true;
        $course = self::validateCourseAlias($r);
        $course_start_time = strtotime($course->start_time);
        $course_finish_time = strtotime($course->finish_time);

        Validators::isStringNonEmpty($r['name'], 'name', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        Validators::isNumberInRange(
            $r['start_time'],
            'start_time',
            $course_start_time,
            $course_finish_time,
            $is_required
        );
        Validators::isNumberInRange(
            $r['finish_time'],
            'finish_time',
            $course_start_time,
            $course_finish_time,
            $is_required
        );

        Validators::isInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $is_required);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);
    }

    /**
     * Validates create or update Courses
     *
     * @throws InvalidParameterException
     */
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        $is_required = true;

        Validators::isStringNonEmpty($r['name'], 'name', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);

        Validators::isNumber($r['start_time'], 'start_time', !$is_update);
        Validators::isNumber($r['finish_time'], 'finish_time', !$is_update);

        Validators::isValidAlias($r['alias'], 'alias', $is_required);

        // Show scoreboard is always optional
        Validators::isInEnum($r['show_scoreboard'], 'show_scoreboard', ['false', 'true'], false /*is_required*/);

        Validators::isInEnum($r['public'], 'public', ['0', '1'], false /*is_required*/);

        if (empty($r['school_id'])) {
            $r['school'] = null;
            $r['school_id'] = null;
        } else {
            $r['school'] = SchoolsDAO::getByPK($r['school_id']);
            if (is_null($r['school'])) {
                throw new InvalidParameterException('schoolNotFound');
            }
            $r['school_id'] = $r['school']->school_id;
        }

        // Get the actual start and finish time of the course, considering that
        // in case of update, parameters can be optional.
        $start_time = null;
        $finish_time = null;
        if ($is_update) {
            self::validateCourseAlias($r);

            $r['course']->toUnixTime();
            if (is_null($r['start_time'])) {
                $r['start_time'] = $r['course']->start_time;
            }
            if (is_null($r['finish_time'])) {
                $r['finish_time'] = $r['course']->finish_time;
            }
        }
        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        // Only curator can set public
        if (!is_null($r['public'])
            && $r['public'] == true
            && !Authorization::canCreatePublicCourse($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects $r[$column_name], returns
     * course found on $r['course']. Throws if not found.
     *
     * @throws NotFoundException
     */
    private static function validateCourseExists(Request $r, $column_name) {
        Validators::isStringNonEmpty($r[$column_name], $column_name, true /*is_required*/);
        $r['course'] = CoursesDAO::getByAlias($r[$column_name]);
        if (is_null($r['course'])) {
            throw new NotFoundException('courseNotFound');
        }
    }

    /**
     * Gets the Group assigned to the Course. Assumes r['course'] has been set
     * @param  Request $r
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws NotFoundException
     */
    private static function resolveGroup(Request $r) {
        if (!is_a($r['course'], 'Courses')) {
            throw new InvalidParameterException('parameterNotFound', 'course');
        }

        if (!is_null($r['group']) && is_a($r['group'], 'Groups')) {
            return;
        }

        try {
            $r['group'] = GroupsDAO::getByPK($r['course']->group_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['group'])) {
            throw new NotFoundException();
        }
    }

    /**
     * Clone a course
     *
     * @return Array
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiClone(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }
        $original_course = CoursesDAO::getByAlias($r['course_alias']);
        $offset = round($r['start_time']) - strtotime($original_course->start_time);
        $auth_token = isset($r['auth_token']) ? $r['auth_token'] : null;

        DAO::transBegin();
        $response = [];
        try {
            // Create the course (and group)
            $response[] = self::apiCreate(new Request([
                'name' => $r['name'],
                'description' => $original_course->description,
                'alias' => $r['alias'],
                'start_time' => $r['start_time'],
                'finish_time' => strtotime($original_course->finish_time) + $offset,
                'public' => 0, // All cloned courses start in private mode
                'auth_token' => $auth_token
            ]));

            $assignments = self::apiListAssignments($r);
            foreach ($assignments['assignments'] as $assignment) {
                $problems[$assignment['alias']] = self::apiAssignmentDetails(new Request([
                    'assignment' => $assignment['alias'],
                    'course' => $original_course->alias,
                    'auth_token' => $auth_token
                ]));
            }

            foreach ($assignments['assignments'] as $assignment) {
                // Create and assign homeworks and tests to new course
                $response[] = self::apiCreateAssignment(new Request([
                    'course_alias' => $r['alias'],
                    'name' => $assignment['name'],
                    'description' => $assignment['description'],
                    'start_time' => $assignment['start_time'] + $offset,
                    'finish_time' => $assignment['finish_time'] + $offset,
                    'alias' => $assignment['alias'],
                    'assignment_type' => $assignment['assignment_type'],
                    'auth_token' => $auth_token
                ]));
                foreach ($problems[$assignment['alias']]['problems'] as $problem) {
                    // Create and assign problems to new course
                    $response[] = self::apiAddProblem(new Request([
                        'course_alias' => $r['alias'],
                        'assignment_alias' => $assignment['alias'],
                        'problem_alias' => $problem['alias'],
                        'auth_token' => $auth_token
                    ]));
                }
            }
            DAO::transEnd();
        } catch (InvalidParameterException $e) {
            DAO::transRollback();
            throw $e;
        } catch (DuplicatedEntryInDatabaseException $e) {
            DAO::transRollback();
            throw $e;
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    /**
     * Create new course
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
        self::validateCreateOrUpdate($r);

        if ($r['alias'] == 'new') {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        if (!is_null(CoursesDAO::getByAlias($r['alias']))) {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        DAO::transBegin();

        $group = GroupController::createGroup(
            $r['alias'],
            'students-' . $r['alias'],
            'students-' . $r['alias'],
            $r['current_user_id']
        );

        try {
            $acl = new ACLs(['owner_id' => $r['current_user_id']]);
            ACLsDAO::save($acl);

            GroupRolesDAO::create(new GroupRoles([
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'role_id' => Authorization::CONTESTANT_ROLE,
            ]));

            // Create the actual course
            CoursesDAO::save(new Courses([
                'name' => $r['name'],
                'description' => $r['description'],
                'alias' => $r['alias'],
                'group_id' => $group->group_id,
                'acl_id' => $acl->acl_id,
                'school_id' => is_null($r['school']) ? null : $r['school']->school_id,
                'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
                'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
                'public' => is_null($r['public']) ? false : $r['public'],
                'show_scoreboard' =>$r['show_scoreboard'] == 'true',
                'needs_basic_information' => $r['needs_basic_information'] == 'true',
                'requests_user_information' => $r['requests_user_information'],
            ]));

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();

            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('titleInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Create an assignment
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
        self::validateCreateAssignment($r);

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        DAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new Problemsets([
                'acl_id' => $r['course']->acl_id,
                'type' => 'Assignment',
                'scoreboard_url' => SecurityTools::randomString(30),
                'scoreboard_url_admin' => SecurityTools::randomString(30),
            ]);
            ProblemsetsDAO::save($problemset);
            $assignment = new Assignments([
                'course_id' => $r['course']->course_id,
                'problemset_id' => $problemset->problemset_id,
                'acl_id' => $r['course']->acl_id,
                'name' => $r['name'],
                'description' => $r['description'],
                'alias' => $r['alias'],
                'publish_time_delay' => $r['publish_time_delay'],
                'assignment_type' => $r['assignment_type'],
                'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
                'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
            ]);

            AssignmentsDAO::save($assignment);

            // Update assignment_id in problemset object
            $problemset->assignment_id = $assignment->assignment_id;
            ProblemsetsDAO::save($problemset);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

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
        self::validateAssignmentDetails($r);
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        if (is_null($r['start_time'])) {
            $r['start_time'] = $r['assignment']->start_time;
        } else {
            Validators::isNumberInRange(
                $r['start_time'],
                'start_time',
                $r['course']->start_time,
                $r['course']->finish_time,
                true /* is_required */
            );
        }
        if (is_null($r['start_time'])) {
            $r['finish_time'] = $r['assignment']->finish_time;
        } else {
            Validators::isNumberInRange(
                $r['finish_time'],
                'finish_time',
                $r['course']->start_time,
                $r['course']->finish_time,
                true /* is_required */
            );
        }

        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        // Prevent date changes if a course already has runs
        if ($r['start_time'] != $r['assignment']->start_time) {
            $runCount = 0;

            try {
                $runCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                    (int)$r['assignment']->problemset_id
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
        self::updateValueProperties($r, $r['assignment'], $valueProperties);

        try {
            AssignmentsDAO::save($r['assignment']);
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::GetProblemset(
            $r['course']->course_id,
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

        $points = 100;
        if (is_numeric($r['points'])) {
            $points = (int)$r['points'];
        }

        [$masterCommit, $currentVersion] = ProblemController::resolveCommit(
            $problem,
            $r['commit']
        );

        ProblemsetController::addProblem(
            $problemSet->problemset_id,
            $problem,
            $masterCommit,
            $currentVersion,
            $r['current_identity_id'],
            $points
        );

        try {
            CoursesDAO::updateAssignmentMaxPoints(
                $r['course'],
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::GetProblemset(
            $r['course']->course_id,
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Update assignments order
        foreach ($r['assignments'] as $assignment) {
            $currentAssignment = AssignmentsDAO::getByAliasAndCourse($assignment['alias'], $r['course']->course_id);

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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        $identities = ProblemsDAO::getIdentitiesInGroupWhoAttemptedProblem(
            $r['course']->group_id,
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::GetProblemset(
            $r['course']->course_id,
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
            !Authorization::isSystemAdmin($r['current_identity_id'])) {
            throw new ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }
        ProblemsetProblemsDAO::delete($problemsetProblem);

        try {
            CoursesDAO::updateAssignmentMaxPoints(
                $r['course'],
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
        self::validateCourseExists($r, 'course_alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_identity_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        try {
            $assignments = AssignmentsDAO::getSortedCourseAssignments(
                $r['course']->course_id
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $isAdmin = Authorization::isCourseAdmin(
            $r['current_identity_id'],
            $r['course']
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::GetProblemset(
            $r['course']->course_id,
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
    private static function convertCourseToArray(Courses $course) {
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

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // TODO(pablo): Cache
        // Courses the user is an admin for.
        $admin_courses = [];
        try {
            if (Authorization::isSystemAdmin($r['current_identity_id'])) {
                $admin_courses = CoursesDAO::getAll(
                    $page,
                    $pageSize,
                    'course_id',
                    'DESC'
                );
            } else {
                $admin_courses = CoursesDAO::getAllCoursesAdminedByIdentity(
                    $r['current_identity_id'],
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
            $student_courses = CoursesDAO::getCoursesForStudent($r['current_identity_id']);
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
     * @return Array response
     */
    public static function apiListStudents(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $students = null;
        try {
            $students = CoursesDAO::getStudentsInCourseWithProgressPerAssignment(
                $r['course']->course_id,
                $r['course']->group_id
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        if (is_null(GroupsIdentitiesDAO::getByPK(
            $r['course']->group_id,
            $r['identity']->identity_id
        ))) {
            throw new NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
            $r['assignment_alias'],
            $r['course']->course_id
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
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_identity_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = null;

        try {
            $assignments = CoursesDAO::getAssignmentsProgress(
                $r['course']->course_id,
                $r['current_identity_id']
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
        self::validateCourseExists($r, 'course_alias');

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        // Only course admins or users adding themselves when the course is public
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])
            && ($r['course']->public == false
            || $r['identity']->identity_id !== $r['current_identity_id'])
            && $r['course']->requests_user_information == 'no'
            && is_null($r['accept_teacher'])
        ) {
            throw new ForbiddenAccessException();
        }

        $groupIdentity = new GroupsIdentities([
            'group_id' => $r['course']->group_id,
            'identity_id' => $r['identity']->identity_id,
            'share_user_information' => $r['share_user_information'],
            'accept_teacher' => $r['accept_teacher'],
        ]);

        DAO::transBegin();

        try {
            GroupsIdentitiesDAO::save(new GroupsIdentities([
                'group_id' => $r['course']->group_id,
                'identity_id' => $r['identity']->identity_id
            ]));

            // Only users adding themselves are saved in consent log
            if ($r['identity']->identity_id === $r['current_identity_id']
                 && $r['course']->requests_user_information != 'no') {
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
            if ($r['identity']->identity_id === $r['current_identity_id']
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $r['identity'] = IdentityController::resolveIdentity($r['usernameOrEmail']);
        if (is_null($r['identity'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        if (is_null(GroupsIdentitiesDAO::getByPK(
            $r['course']->group_id,
            $r['identity']->identity_id
        ))) {
            throw new NotFoundException('courseStudentNotInCourse');
        }

        try {
            GroupsIdentitiesDAO::delete(new GroupsIdentities([
                'group_id' => $r['course']->group_id,
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

        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $course)) {
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
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only director is allowed to make modifications
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $course)) {
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
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $course)) {
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
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');

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
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $course)) {
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
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');

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
        if (!Authorization::isCourseAdmin($r['current_identity_id'], $course)) {
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
        self::validateCourseExists($r, 'course_alias');
        self::resolveGroup($r);

        $shouldShowIntro = !Authorization::canViewCourse($r['current_identity_id'], $r['course'], $r['group']);
        $isFirstTimeAccess = false;
        $showAcceptTeacher = false;
        if (!Authorization::isGroupAdmin($r['current_identity_id'], $r['group'])) {
            $sharingInformation = CoursesDAO::getSharingInformation($r['current_identity_id'], $r['course'], $r['group']);
            $isFirstTimeAccess = $sharingInformation['share_user_information'] == null;
            $showAcceptTeacher = $sharingInformation['accept_teacher'] == null;
        }
        if ($shouldShowIntro && !$r['course']->public) {
            throw new ForbiddenAccessException();
        }

        $user_session = SessionController::apiCurrentSession($r)['session']['user'];
        $result = self::getCommonCourseDetails($r, true /*onlyIntroDetails*/);
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
     * @param  Request $r
     * @return array
     */
    private static function getCommonCourseDetails(Request $r, $onlyIntroDetails) {
        $isAdmin = Authorization::isCourseAdmin(
            $r['current_identity_id'],
            $r['course']
        );

        if ($onlyIntroDetails) {
            $result = [
                'status' => 'ok',
                'name' => $r['course']->name,
                'description' => $r['course']->description,
                'alias' => $r['course']->alias,
                'basic_information_required' => $r['course']->needs_basic_information == '1',
                'requests_user_information' => $r['course']->requests_user_information
            ];
        } else {
            $result = [
                'status' => 'ok',
                'assignments' => CoursesDAO::getAllAssignments($r['alias'], $isAdmin),
                'name' => $r['course']->name,
                'description' => $r['course']->description,
                'alias' => $r['course']->alias,
                'school_id' => $r['course']->school_id,
                'start_time' => strtotime($r['course']->start_time),
                'finish_time' => strtotime($r['course']->finish_time),
                'is_admin' => $isAdmin,
                'public' => $r['course']->public,
                'basic_information_required' => $r['course']->needs_basic_information == '1',
                'show_scoreboard' => $r['course']->show_scoreboard == '1',
                'requests_user_information' => $r['course']->requests_user_information
            ];

            if ($isAdmin) {
                try {
                    $group = GroupsDAO::getByPK($r['course']->group_id);
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
            if (!is_null($r['course']->school_id)) {
                $school = SchoolsDAO::getByPK($r['course']->school_id);
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
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r, false /*onlyIntroDetails*/);
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
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $accesses = ProblemsetAccessLogDAO::GetAccessForCourse($r['course']->course_id);
        $submissions = SubmissionLogDAO::GetSubmissionsForCourse($r['course']->course_id);

        return ActivityReport::getActivityReport($accesses, $submissions);
    }

    private static function validateAssignmentDetails(Request $r) {
        Validators::isStringNonEmpty($r['course'], 'course', true /* is_required */);
        Validators::isStringNonEmpty($r['assignment'], 'assignment', true /* is_required */);
        $r['course'] = CoursesDAO::getByAlias($r['course']);
        if (is_null($r['course'])) {
            throw new NotFoundException('courseNotFound');
        }
        $r['course']->toUnixTime();
        $r['assignment'] = AssignmentsDAO::getByAliasAndCourse($r['assignment'], $r['course']->course_id);
        if (is_null($r['assignment'])) {
            throw new NotFoundException('assignmentNotFound');
        }
        $r['assignment']->toUnixTime();

        // Admins are almighty, no need to check anything else.
        if (Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            return;
        }

        if ($r['assignment']->start_time > Time::get() ||
            !GroupRolesDAO::isContestant($r['current_identity_id'], $r['assignment']->acl_id)
        ) {
            throw new ForbiddenAccessException();
        }
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

        self::authenticateRequest($r);
        self::validateAssignmentDetails($r);

        $problems = ProblemsetProblemsDAO::getProblems(
            $r['assignment']->problemset_id
        );
        $letter = 0;
        foreach ($problems as &$problem) {
            $problem['letter'] = ContestController::columnName($letter++);
            unset($problem['problem_id']);
        }

        $director = null;
        try {
            $acl = ACLsDAO::getByPK($r['course']->acl_id);
            $director = UsersDAO::getByPK($acl->owner_id)->username;
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        // Log the operation.
        ProblemsetAccessLogDAO::create(new ProblemsetAccessLog([
            'identity_id' => $r['current_identity_id'],
            'problemset_id' => $r['assignment']->problemset_id,
            'ip' => ip2long($_SERVER['REMOTE_ADDR']),
        ]));
        return [
            'status' => 'ok',
            'name' => $r['assignment']->name,
            'description' => $r['assignment']->description,
            'assignment_type' => $r['assignment']->assignment_type,
            'start_time' => $r['assignment']->start_time,
            'finish_time' => $r['assignment']->finish_time,
            'problems' => $problems,
            'director' => $director,
            'problemset_id' => $r['assignment']->problemset_id,
            'admin' => Authorization::isCourseAdmin($r['current_identity_id'], $r['course']),
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
    private static function validateRuns(Request $r) {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');
        Validators::isStringNonEmpty($r['assignment_alias'], 'assignment_alias');

        try {
            $r['course'] = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['course'])) {
            throw new NotFoundException('courseNotFound');
        }

        try {
            $r['assignment'] = AssignmentsDAO::getByAliasAndCourse(
                $r['assignment_alias'],
                $r['course']->course_id
            );
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['assignment'])) {
            throw new NotFoundException('assignmentNotFound');
        }

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);
        Validators::isInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::isInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::isStringNonEmpty($r['problem_alias'], 'problem');

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

        Validators::isInEnum($r['language'], 'language', array_keys(RunController::$kSupportedLanguages), false);

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
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_identity_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r, false /*onlyIntroDetails*/);
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
        self::validateCreateOrUpdate($r, true /* is update */);

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
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
        self::updateValueProperties($r, $r['course'], $valueProperties);

        // Push changes
        try {
            CoursesDAO::save($r['course']);
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
        self::authenticateRequest($r);
        self::validateCourseAlias($r);
        self::validateCourseAssignmentAlias($r);
        self::resolveGroup($r);

        if (!Authorization::canViewCourse($r['current_identity_id'], $r['course'], $r['group'])) {
            throw new ForbiddenAccessException();
        }

        $scoreboard = new Scoreboard(
            new ScoreboardParams([
                'alias' => $r['assignment']->alias,
                'title' => $r['assignment']->name,
                'problemset_id' => $r['assignment']->problemset_id,
                'start_time' => $r['assignment']->start_time,
                'finish_time' => $r['assignment']->finish_time,
                'acl_id' => $r['assignment']->acl_id,
                'group_id' => $r['course']->group_id,
                'admin' => true
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
        // Get the current user
        self::authenticateRequest($r);
        self::validateCourseAlias($r);
        self::validateCourseAssignmentAlias($r);

        $scoreboard = new Scoreboard(
            ScoreboardParams::fromAssignment(
                $r['assignment'],
                $r['course']->group_id,
                Authorization::isCourseAdmin($r['current_user_id'], $r['course'])/*show_all_runs*/
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
        self::validateCourseAlias($r);

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
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
        self::validateCourseAlias($r);

        if (!Authorization::isCourseAdmin($r['current_identity_id'], $r['course'])) {
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
    public static function shouldShowScoreboard($identity_id, Courses $course, Groups $group) {
        Validators::isNumber($identity_id, 'identity_id', true);
        return Authorization::canViewCourse($identity_id, $course, $group) &&
            $course->show_scoreboard;
    }
}
