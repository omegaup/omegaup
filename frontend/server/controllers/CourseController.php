<?php

class CourseController extends Controller {
    /**
     * Validates request for Assignment on Course operations
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    private static function validateCreateOrUpdateAssignment(Request $r, $is_update = false) {
        $is_required = true;

        // Does this assignment need to be within the time constraints of
        // the course it belongs to?

        Validators::isStringNonEmpty($r['name'], 'name', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias', $is_required);
        self::validateCourseExists($r);

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($r['course']->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($r['course']->finish_time);
        if ($start_time > $finish_time) {
            throw new InvalidParameterException('InvalidStartTime');
        }

        $parent_course = CoursesDAO::search(array('alias' => $r['course_alias']));
        if (is_null($parent_course) || count($parent_course) != 1) {
            throw new InvalidParameterException('parameterInvalid');
        }

        $r['id_course'] = $parent_course[0]->course_id;

        Validators::isValidAlias($r['alias'], 'alias', $is_required);
        Validators::isInEnum($r['assignment_type'], 'assignment_type', array('test', 'homework'), true /*is_required*/);
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
        Validators::isInEnum($r['show_scoreboard'], 'show_scoreboard', array('0', '1'), false /*is_required*/);

        if ($is_update) {
            // @TODO(alan): Prevent date changes if a contest already has runs
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
        $courses = null;
        try {
            $courses = CoursesDAO::search(new Courses(array(
                'alias' => $r['course_alias']
            )));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($courses) || !is_array($courses) || count($courses) !== 1) {
            throw new InvalidParameterException('invalidParamater', 'course_alias');
        }

        $r['course'] = $courses[0];
    }

    /**
     * Create new course
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r);

        if (!is_null(CoursesDAO::findByAlias($r['alias']))) {
            throw new DuplicatedEntryInDatabaseException('alias');
        }

        // Create the associated group
        $groupRequest = new Request(array(
            'alias' => $r['alias'],
            'name' => 'for-' . $r['alias']
        ));

        GroupController::apiCreate($groupRequest);
        $group = GroupsDAO::FindByAlias($groupRequest['alias']);

        // Create the actual course
        $course = new Courses($r);
        $course->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $course->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $course->id_owner = $r['current_user_id'];
        $course->id_admingroup = $group->group_id;

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

        return array('status' => 'ok');
    }

    /**
     * Create assignment
     *
     */
    public static function apiCreateAssignment(Request $r) {
        self::authenticateRequest($r);

        self::validateCreateOrUpdateAssignment($r);

        $problemSet = new Problemsets();

        $assignment = new Assignments($r);
        $assignment->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $assignment->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);

        try {
            // Create the backing problemset
            ProblemsetsDAO::save($problemSet);

            $assignment->id_problemset = $problemSet->problemset_id;

            AssignmentsDAO::save($assignment);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return array('status' => 'ok');
    }

    /**
     * Adds a problem to an assignment
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAddProblem(Request $r) {
        self::authenticateRequest($r);

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

        return array('status' => 'ok');
    }

    /**
     * List course assignments
     *
     * @throws InvalidParameterException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListAssignments(Request $r) {
        self::authenticateRequest($r);
        Validators::isStringNonEmpty($r['course_alias'], 'course_alias');
        self::validateCourseExists($r);

        $assignments = array();
        try {
            $assignments = AssignmentsDAO::search(new Assignments(array(
                'id_course' => $r['course']->course_id
            )));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = array();
        foreach ($assignments as $a) {
            $response['assignments'][] = array(
                'name' => $a->name,
                'alias' => $a->alias,
                'description' => $a->description,
                'start_time' => $a->start_time,
                'finish_time' => $a->finish_time,
                'assignment_type' => $a->assignment_type
            );
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     *
     */
    public static function apiAdminDetails(Request $r) {
        self::authenticateRequest($r);
        Validators::isStringNonEmpty($r['alias'], 'alias', true /*is_required*/);

        $result = array();

        // TODO(pablo): What should this return beyond apiDetails?
        $course = CoursesDAO::findByAlias($r['alias']);
        if (is_null($course)) {
            throw new NotFoundException('courseNotFound');
        }

        $result['status'] = 'ok';
        $result['assignments'] = CoursesDAO::getAllAssignments($r['alias']);

        $result['name'] = $course->name;
        $result['description'] = $course->description;
        $result['alias'] = $course->alias;
        $result['start_time'] = strtotime($course->start_time);
        $result['finish_time'] = strtotime($course->finish_time);

        return $result;
    }

    public static function apiDetails(Request $r) {
        self::authenticateRequest($r);
        Validators::isStringNonEmpty($r['alias'], 'alias', true /*is_required*/);

        $result = array();

        // @TODO(alan): cache?

        $course = CoursesDAO::findByAlias($r['alias']);
        if (is_null($course)) {
            throw new NotFoundException('courseNotFound');
        }

        $result['status'] = 'ok';
        $result['assignments'] = CoursesDAO::getAllAssignments($r['alias']);

        $result['name'] = $course->name;
        $result['description'] = $course->description;
        $result['alias'] = $course->alias;
        $result['start_time'] = strtotime($course->start_time);
        $result['finish_time'] = strtotime($course->finish_time);
        $result['is_admin'] = Authorization::IsCourseAdmin(
            $r['current_user_id'],
            $course
        );
        if ($result['is_admin']) {
            try {
                $group = GroupsDAO::findByAlias($r['alias']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
            if (is_null($group)) {
                throw new NotFoundException('courseGroupNotFound');
            }
            $result['student_count'] = GroupsUsersDAO::GetMemberCountById($group->group_id);
        }
        return $result;
    }

    public static function apiUpdate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate request
        self::authenticateRequest($r);

        // Validate request
        self::validateCreateOrUpdate($r, true /* is update */);

        // Update contest DAO
        $valueProperties = array(
            'alias',
            'name',
            'description',
            'start_time' => array('transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }),
            'finish_time' => array('transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }),
            'show_scoreboard',
        );
        self::updateValueProperties($r, $r['course'], $valueProperties);

        // Push changes
        try {
            // Begin a new transaction
            CoursesDAO::transBegin();

            // Save the contest object with data sent by user to the database
            CoursesDAO::save($r['course']);
/*
            // If the contest is private, add the list of allowed users
            if (!is_null($r['public']) && $r['public'] != 1 && $r['hasPrivateUsers']) {
                // Get current users
                $cu_key = new ContestsUsers(array('contest_id' => $r['contest']->contest_id));
                $current_users = ContestsUsersDAO::search($cu_key);
                $current_users_id = array();

                foreach ($current_users as $cu) {
                    array_push($current_users_id, $current_users->user_id);
                }

                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_users_id, $r['private_users_list']);
                $to_add = array_diff($r['private_users_list'], $current_users_id);

                // Add users in the request
                foreach ($to_add as $userkey) {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers(array(
                                'contest_id' => $r['contest']->contest_id,
                                'user_id' => $userkey,
                                'access_time' => '0000-00-00 00:00:00',
                                'score' => 0,
                                'time' => 0
                            ));

                    // Save the relationship in the DB
                    ContestsUsersDAO::save($temp_user_contest);
                }

                // Delete users
                foreach ($to_delete as $userkey) {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers(array(
                                'contest_id' => $r['contest']->contest_id,
                                'user_id' => $userkey,
                            ));

                    // Delete the relationship in the DB
                    ContestsUsersDAO::delete(ContestProblemsDAO::search($temp_user_contest));
                }
            }

            if (!is_null($r['problems'])) {
                // Get current problems
                $p_key = new Problems(array('contest_id' => $r['contest']->contest_id));
                $current_problems = ProblemsDAO::search($p_key);
                $current_problems_id = array();

                foreach ($current_problems as $p) {
                    array_push($current_problems_id, $p->problem_id);
                }

                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_problems_id, self::$problems_id);
                $to_add = array_diff(self::$problems_id, $current_problems_id);

                foreach ($to_add as $problem) {
                    $contest_problem = new ContestProblems(array(
                                'contest_id' => $r['contest']->contest_id,
                                'problem_id' => $problem,
                                'points' => $r['problems'][$problem]['points']
                            ));

                    ContestProblemsDAO::save($contest_problem);
                }

                foreach ($to_delete as $problem) {
                    $contest_problem = new ContestProblems(array(
                                'contest_id' => $r['contest']->contest_id,
                                'problem_id' => $problem,
                            ));

                    ContestProblemsDAO::delete(ContestProblemsDAO::search($contest_problem));
                }
            }
 */
            // End transaction
            CoursesDAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            CoursesDAO::transRollback();

            throw new InvalidDatabaseOperationException($e);
        }

        // TODO: Expire cache

        // Happy ending
        $response = array();
        $response['status'] = 'ok';

        self::$log->info('Contest updated (alias): ' . $r['contest_alias']);

        return $response;
    }
}
