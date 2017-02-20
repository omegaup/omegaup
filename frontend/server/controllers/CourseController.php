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

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($r['course']->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($r['course']->finish_time);
        if ($start_time > $finish_time) {
            throw new InvalidParameterException('InvalidStartTime');
        }

        Validators::isInEnum($r['assignment_type'], 'assignment_type', ['test', 'homework'], $is_required);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);

        $parent_course = null;
        try {
            $parent_course = CoursesDAO::getByAlias($r['course_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($parent_course) || count($parent_course) !== 1) {
            throw new InvalidParameterException('parameterInvalid');
        }

        $r['course'] = $parent_course;
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

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($r['course']->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($r['course']->finish_time);
        if ($start_time > $finish_time) {
            throw new InvalidParameterException('contestNewInvalidStartTime');
        }

        Validators::isValidAlias($r['alias'], 'alias', $is_required);

        // Show scoreboard is always optional
        Validators::isInEnum($r['show_scoreboard'], 'show_scoreboard', ['0', '1'], false /*is_required*/);

        if ($is_update) {
            try {
                $r['course'] = CoursesDAO::findByAlias($r['course_alias']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        }
    }

    /**
     * Validates course exists. Expects $r['course_alias'], returns
     * course found on $r['course']. Throws if not found.
     *
     * @throws InvalidParameterException
     */
    private static function validateCourseExists(Request $r) {
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');
        $courses = null;
        try {
            $courses = CoursesDAO::search(new Courses([
                'alias' => $r['course_alias']
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($courses) || !is_array($courses) || count($courses) !== 1) {
            throw new InvalidParameterException('invalidParamater', 'course_alias');
        }

        $r['course'] = $courses[0];
    }

    /**
     * Validates params needed for details APIs
     *
     * @param  Request $r
     * @throws NotFoundException
     */
    private static function validateCourseDetails(Request $r) {
        Validators::isStringNonEmpty($r['alias'], 'alias', true /*is_required*/);

        $r['course'] = CoursesDAO::findByAlias($r['alias']);
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

        if (!is_null(CoursesDAO::findByAlias($r['alias']))) {
            throw new DuplicatedEntryInDatabaseException('alias');
        }

        // Create the associated group
        $groupRequest = new Request([
            'auth_token' => $r['auth_token'],
            'alias' => $r['alias'],
            'name' => 'for-' . $r['alias']
        ]);

        GroupController::apiCreate($groupRequest);
        $group = GroupsDAO::FindByAlias($groupRequest['alias']);

        $acl = new ACLs(['owner_id' => $r['current_user_id']]);
        ACLsDAO::save($acl);

        // Create the actual course
        $course = new Courses($r);
        $course->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $course->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $course->group_id = $group->group_id;
        $course->acl_id = $acl->acl_id;

        $course_id = -1;
        try {
            $existing = CoursesDAO::findByName($r['name']);
            if (count($existing) > 0) {
                $course_id = $existing[0]->course_id;
            } else {
                CoursesDAO::save($course);
                $course_id = $course->course_id;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
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

        $assignment = new Assignments($r);
        $assignment->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $assignment->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $assignment->acl_id = $r['course']->acl_id;

        try {
            // Create the backing problemset
            $problemset = new Problemsets([
                'acl_id' => $r['course']->acl_id
            ]);
            ProblemsetsDAO::save($problemset);

            $assignment->problemset_id = $problemset->problemset_id;
            $assignment->course_id = $r['course']->course_id;

            GroupRolesDAO::save(new GroupRoles([
                'group_id' => $r['course']->group_id,
                'role_id' => Authorization::CONTESTANT_ROLE,
                'acl_id' => $r['course']->acl_id,
            ]));
            AssignmentsDAO::save($assignment);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
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
        self::validateAssignmentDetails($r, true/*is_required*/);

        // Update contest DAO
        $valueProperties = [
            'name',
            'description',
            'start_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
            'finish_time' => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }]
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
        self::validateCourseExists($r);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Get the associated problemset with this assignment
        $problemSet = AssignmentsDAO::GetProblemset($r['assignment_alias']);

        if (is_null($problemSet)) {
            throw new InvalidDatabaseOperationException();
        }

        // Get this problem
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);

        $problemSetProblem = new ProblemsetProblems();
        $problemSetProblem->problemset_id = $problemSet->problemset_id;
        $problemSetProblem->problem_id = $problem->problem_id;

        ProblemsetProblemsDAO::save($problemSetProblem);

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
        self::validateCourseExists($r);
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse($r['current_user_id'], $r['course'], $r['group'])) {
            throw new ForbiddenAccessException();
        }

        $assignments = [];
        try {
            $assignments = AssignmentsDAO::search(new Assignments([
                'course_id' => $r['course']->course_id
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        $response['assignments'] = [];
        foreach ($assignments as $a) {
            $response['assignments'][] = [
                'name' => $a->name,
                'alias' => $a->alias,
                'description' => $a->description,
                'start_time' => $a->start_time,
                'finish_time' => $a->finish_time,
                'assignment_type' => $a->assignment_type
            ];
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Converts a Course object into an array
     * @param  Course $course
     * @return array
     */
    private static function convertCourseToArray(Courses $course) {
        $course->toUnixTime();
        $relevant_columns = ['alias', 'name', 'finish_time'];
        $arr = $course->asFilteredArray($relevant_columns);

        $counts = AssignmentsDAO::getAssignmentCountsForCourse($course->course_id);
        $arr['counts'] = $counts;
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
        self::validateCourseExists($r);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        $students = null;
        $counts = null;
        try {
            $students = CoursesDAO::getStudentsForCourseWithProgress(
                $r['course']->course_id,
                $r['course']->group_id
            );
            $counts = AssignmentsDAO::getAssignmentCountsForCourse($r['course']->course_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'students' => $students,
            'counts' => $counts,
            'status' => 'ok'
            ];
    }

    /**
     * Add Student to Course
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
        self::validateCourseExists($r);

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
        self::validateCourseExists($r);

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

        try {
            GroupsUsersDAO::delete($groupUser);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Returns course details common between admin & non-admin
     * @param  Request $r
     * @return array
     */
    private static function getCommonCourseDetails(Request $r) {
        $result = [];
        $result['status'] = 'ok';
        $result['assignments'] = CoursesDAO::getAllAssignments($r['alias']);

        $result['name'] = $r['course']->name;
        $result['description'] = $r['course']->description;
        $result['alias'] = $r['course']->alias;
        $result['start_time'] = strtotime($r['course']->start_time);
        $result['finish_time'] = strtotime($r['course']->finish_time);
        $result['is_admin'] = Authorization::isCourseAdmin(
            $r['current_user_id'],
            $r['course']
        );

        if ($result['is_admin']) {
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
        } else {
            // Non-admins should not be able to see assignments that have not
            // started.
            $time = time();
            $result['assignments'] = array_values(array_filter(
                $result['assignments'],
                function ($v) use ($time) {
                    return $v['start_time'] <= $time;
                }
            ));
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
        self::validateCourseDetails($r);
        self::resolveGroup($r);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r);
    }

    private static function validateAssignmentDetails(Request $r, $is_required = false) {
        Validators::isStringNonEmpty($r['course'], 'course', $is_required);
        Validators::isStringNonEmpty($r['assignment'], 'assignment', $is_required);
        $r['course'] = CoursesDAO::findByAlias($r['course']);
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
        if ($r['assignment']->start_time > time() &&
            !Authorization::isCourseAdmin($r['current_user_id'], $r['course'])
        ) {
            throw new ForbiddenAccessException();
        }
        // TODO: Access check
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

        $problems = ProblemsetProblemsDAO::getProblems($r['assignment']->problemset_id);
        $letter = 0;
        foreach ($problems as &$problem) {
            $problem['letter'] = ContestController::columnName($letter++);
        }

        $director = null;
        try {
            $acl = ACLsDAO::getByPK($r['course']->acl_id);
            $director = UsersDAO::getByPK($acl->owner_id)->username;
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok',
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
        self::validateCourseDetails($r);
        self::resolveGroup($r);

        // Only Course Admins or Group Members (students) can see these results
        if (!Authorization::canViewCourse($r['current_user_id'], $r['course'], $r['group'])) {
            throw new ForbiddenAccessException();
        }

        return self::getCommonCourseDetails($r);
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

        // Validate request
        self::validateCreateOrUpdate($r, true /* is update */);

        if (!Authorization::isCourseAdmin($r['current_user_id'], $r['course'])) {
            throw new ForbiddenAccessException();
        }

        // Update contest DAO
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
        ];
        self::updateValueProperties($r, $r['course'], $valueProperties);

        // Push changes
        try {
            // Begin a new transaction
            CoursesDAO::transBegin();

            // Save the contest object with data sent by user to the database
            CoursesDAO::save($r['course']);

            // End transaction
            CoursesDAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            CoursesDAO::transRollback();

            throw new InvalidDatabaseOperationException($e);
        }

        // TODO: Expire cache

        // Happy ending
        $response = [];
        $response['status'] = 'ok';

        self::$log->info('Contest updated (alias): ' . $r['contest_alias']);

        return $response;
    }
}
