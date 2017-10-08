<?php

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
        Validators::isStringNonEmpty($r['name'], 'name', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
        }

        Validators::isInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $is_required);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);

        self::validateCourseAlias($r);
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
        Validators::isInEnum($r['show_scoreboard'], 'show_scoreboard', ['0', '1'], false /*is_required*/);

        Validators::isInEnum($r['public'], 'public', ['0', '1'], false /*is_required*/);

        // Get the actual start and finish time of the contest, considering that
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
            && !Authorization::canCreatePublicCourse($r['current_user_id'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Validates course exists. Expects $r[$column_name], returns
     * course found on $r['course']. Throws if not found.
     *
     * @throws InvalidParameterException
     */
    private static function validateCourseExists(Request $r, $column_name) {
        /*
         * TODO: This is used by the many calls of course.php. Could be removed.
         * https://github.com/omegaup/omegaup/issues/1401
         */
        if (!is_null($r['course']) && is_a($r['course'], 'Courses')) {
            return;
        }

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
     * Create new course
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCreateOrUpdate($r);

        if ($r['alias'] == 'new') {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        if (!is_null(CoursesDAO::getByAlias($r['alias']))) {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }

        CoursesDAO::transBegin();

        $group = GroupController::createGroup(
            $r['alias'],
            'students-' . $r['alias'],
            'students-' . $r['alias'],
            $r['current_user_id']
        );

        try {
            $acl = new ACLs(['owner_id' => $r['current_user_id']]);
            ACLsDAO::save($acl);

            GroupRolesDAO::save(new GroupRoles([
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
                'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
                'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
                'public' => is_null($r['public']) ? false : $r['public'],
            ]));

            CoursesDAO::transEnd();
        } catch (Exception $e) {
            CoursesDAO::transRollback();

            if (strpos($e->getMessage(), '1062') !== false) {
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCreateAssignment($r);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        AssignmentsDAO::transBegin();
        try {
            // Create the backing problemset
            $problemset = new Problemsets([
                'acl_id' => $r['course']->acl_id
            ]);
            ProblemsetsDAO::save($problemset);

            AssignmentsDAO::save(new Assignments([
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
            ]));

            AssignmentsDAO::transEnd();
        } catch (Exception $e) {
            AssignmentsDAO::transRollback();
            if (strpos($e->getMessage(), '1062') !== false) {
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateAssignmentDetails($r, true /*is_required*/);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        Validators::isNumber($r['start_time'], 'start_time', false /*is_required*/);
        Validators::isNumber($r['finish_time'], 'finish_time', false /*is_required*/);

        if (is_null($r['start_time'])) {
            $r['start_time'] = $r['assignment']->start_time;
        }
        if (is_null($r['finish_time'])) {
            $r['finish_time'] = $r['assignment']->finish_time;
        }
        if ($r['start_time'] > $r['finish_time']) {
            throw new InvalidParameterException('courseInvalidStartTime');
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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

        ProblemsetController::addProblem(
            $problemSet->problemset_id,
            $problem,
            $r['current_user_id'],
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

    public static function apiGetProblemUsers(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        $users = ProblemsDAO::getUsersInGroupWhoAttemptedProblem(
            $r['course']->group_id,
            $problem->problem_id
        );

        return ['status' => 'ok', 'users' => $users];
    }

    /**
     * Remove a problem from an assignment
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRemoveProblem(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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

        // Construct the relationship entity between the problemset and the problem
        $problemsetProblem = new ProblemsetProblems([
            'problemset_id' => $problemSet->problemset_id,
            'problem_id' => $problem->problem_id,
        ]);

        $problemsetProblem = ProblemsetProblemsDAO::getByPK(
            $problemsetProblem->problemset_id,
            $problemsetProblem->problem_id
        );

        if (is_null($problemsetProblem)) {
            throw new NotFoundException('problemNotPartOfAssignment');
        }

        // Delete the entry from the database
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_user_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = [];
        try {
            $assignments = AssignmentsDAO::search(new Assignments([
                'course_id' => $r['course']->course_id
            ]), 'start_time');
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $isAdmin = Authorization::isCourseAdmin(
            $r['current_user_id'],
            $r['course']
        );

        $response = [
            'status' => 'ok',
            'assignments' => [],
        ];
        $time = time();
        foreach ($assignments as $a) {
            $a->toUnixTime();
            if (!$isAdmin && $v['start_time'] > $time) {
                // Non-admins should not be able to see the assignments ahead
                // of time.
                continue;
            }
            $response['assignments'][] = [
                'name' => $a->name,
                'alias' => $a->alias,
                'description' => $a->description,
                'start_time' => $a->start_time,
                'finish_time' => $a->finish_time,
                'assignment_type' => $a->assignment_type
            ];
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // TODO(pablo): Cache
        // Courses the user is an admin for.
        $admin_courses = [];
        try {
            if (Authorization::isSystemAdmin($r['current_user_id'])) {
                $admin_courses = CoursesDAO::getAll(
                    $page,
                    $pageSize,
                    'course_id',
                    'DESC'
                );
            } else {
                $admin_courses = CoursesDAO::getAllCoursesAdminedByUser(
                    $r['current_user_id'],
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
            $student_courses = CoursesDAO::getCoursesForStudent($r['current_user_id']);
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        $groupUser = new GroupsUsers([
            'group_id' => $r['course']->group_id,
            'user_id' => $r['user']->user_id,
        ]);
        if (is_null(GroupsUsersDAO::getByPK(
            $groupUser->group_id,
            $groupUser->user_id
        ))) {
            throw new NotFoundException(
                'courseStudentNotInCourse'
            );
        }

        $assignments = AssignmentsDAO::search(new Assignments([
            'course_id' => $r['course']->course_id,
            'alias' => $r['assignment'],
        ]));
        if (count($assignments) != 1) {
            throw new NotFoundException('assignmentNotFound');
        }
        $r['assignment'] = $assignments[0];
        $r['assignment']->toUnixTime();

        $problems = ProblemsetProblemsDAO::getProblems($r['assignment']->problemset_id);
        $letter = 0;
        $relevant_run_columns = ['guid', 'language', 'status', 'verdict',
            'runtime', 'penalty', 'memory', 'score', 'contest_score', 'time',
            'submit_delay'];
        foreach ($problems as &$problem) {
            $keyrun = new Runs([
                'user_id' => $r['user']->user_id,
                'problem_id' => $problem['problem_id'],
                'problemset_id' => $r['assignment']->problemset_id,
            ]);
            $runs_array = RunsDAO::search($keyrun);
            $runs_filtered_array = [];
            foreach ($runs_array as $run) {
                $run->toUnixTime();
                $filtered_run = $run->asFilteredArray($relevant_run_columns);
                try {
                    $filtered_run['source'] = file_get_contents(RunController::getSubmissionPath($run));
                } catch (Exception $e) {
                    self::$log->error('Error fetching source for {$run->guid}: ' . $e);
                }
                $runs_filtered_array[] = $filtered_run;
            }
            $problem['runs'] = $runs_filtered_array;
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_user_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        $assignments = null;

        try {
            $assignments = CoursesDAO::getAssignmentsProgress(
                $r['course']->course_id,
                $r['current_user_id']
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        // Only course admins or users adding themselves when the course is public
        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])
            && ($r['course']->public == false
            || $r['user']->user_id !== $r['current_user_id'])) {
            throw new ForbiddenAccessException();
        }

        $groupUser = new GroupsUsers([
            'group_id' => $r['course']->group_id,
            'user_id' => $r['user']->user_id,
        ]);
        if (!is_null(GroupsUsersDAO::getByPK(
            $groupUser->group_id,
            $groupUser->user_id
        ))) {
            throw new DuplicatedEntryInDatabaseException(
                'courseStudentAlreadyPresent'
            );
        }

        try {
            GroupsUsersDAO::save($groupUser);
        } catch (Exception $e) {
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        $groupUser = new GroupsUsers([
            'group_id' => $r['course']->group_id,
            'user_id' => $r['user']->user_id,
        ]);
        if (is_null(GroupsUsersDAO::getByPK(
            $groupUser->group_id,
            $groupUser->user_id
        ))) {
            throw new NotFoundException('courseStudentNotInCourse');
        }

        try {
            GroupsUsersDAO::delete($groupUser);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Show course intro only on public courses when user is not yet registered
     * @param  Request $r
     * @throws NotFoundException Course not found or trying to directly access a private course.
     * @return Boolean
     */
    public static function shouldShowIntro(Request $r) {
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'course_alias');
        self::resolveGroup($r);

        // If canViewCourse is true, then user is already inside the course...
        if (Authorization::canViewCourse($r['current_user_id'], $r['course'], $r['group'])) {
            return false;
        }

        // If not previously registered and course is private, hide its existence
        if (!$r['course']->public) {
            throw new NotFoundException('courseNotFound');
        }

        return true;
    }

    /**
     * Returns course details common between admin & non-admin
     * @param  Request $r
     * @return array
     */
    private static function getCommonCourseDetails(Request $r, $onlyIntroDetails) {
        $isAdmin = Authorization::isCourseAdmin(
            $r['current_user_id'],
            $r['course']
        );

        if ($onlyIntroDetails) {
            $result = [
                'status' => 'ok',
                'name' => $r['course']->name,
                'description' => $r['course']->description,
                'alias' => $r['course']->alias,
            ];
        } else {
            $result = [
                'status' => 'ok',
                'assignments' => CoursesDAO::getAllAssignments($r['alias'], $isAdmin),
                'name' => $r['course']->name,
                'description' => $r['course']->description,
                'alias' => $r['course']->alias,
                'start_time' => strtotime($r['course']->start_time),
                'finish_time' => strtotime($r['course']->finish_time),
                'is_admin' => $isAdmin,
                'public' => $r['course']->public,
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
                $result['student_count'] = GroupsUsersDAO::GetMemberCountById(
                    $group->group_id
                );
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r, false /*onlyIntroDetails*/);
    }

    private static function validateAssignmentDetails(Request $r, $is_required = false) {
        Validators::isStringNonEmpty($r['course'], 'course', $is_required);
        Validators::isStringNonEmpty($r['assignment'], 'assignment', $is_required);
        $r['course'] = CoursesDAO::getByAlias($r['course']);
        if (is_null($r['course'])) {
            throw new NotFoundException('courseNotFound');
        }
        $assignments = AssignmentsDAO::search(new Assignments([
            'course_id' => $r['course']->course_id,
            'alias' => $r['assignment'],
        ]));
        if (count($assignments) != 1) {
            throw new NotFoundException('assignmentNotFound');
        }
        $r['assignment'] = $assignments[0];
        $r['assignment']->toUnixTime();

        // Admins are almighty, no need to check anything else.
        if (Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            return;
        }

        if ($r['assignment']->start_time > time() ||
            !GroupRolesDAO::isContestant($r['current_user_id'], $r['assignment']->acl_id)
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
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
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
        ];
    }

    /**
     * Returns details of a given course
     * @param  Request $r
     * @return array
     */
    public static function apiDetails(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_user_id'],
            $r['course'],
            $r['group']
        )) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r, false /*onlyIntroDetails*/);
    }

    /**
     * Returns public details of a given course
     * @param  Request $r
     * @return array
     */
    public static function apiIntroDetails(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateCourseExists($r, 'alias');
        self::resolveGroup($r);

        // Details available for public courses, otherwise Either only Course Admins or
        // Group Members (students) can see these results
        if (!Authorization::canViewCourse(
            $r['current_user_id'],
            $r['course'],
            $r['group']
        ) && !$r['course']->public) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r, true /*onlyIntroDetails*/);
    }

    /**
     * Edit Course contents
     *
     * @param  Request $r
     * @return array
     */
    public static function apiUpdate(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::SCHOOLS);
        self::authenticateRequest($r);
        self::validateCreateOrUpdate($r, true /* is update */);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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
            'show_scoreboard',
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

        self::$log->info('Course updated (alias): ' . $r['contest_alias']);
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

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
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
                'show_all_runs' => true
            ])
        );

        return $scoreboard->generate(
            false /*withRunDetails*/,
            true /*sortByName*/
        );
    }
}
