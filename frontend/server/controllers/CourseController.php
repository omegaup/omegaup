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

        $course = new Courses($r);
        $course->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $course->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $course->id_owner = $r['current_user_id'];

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
}
