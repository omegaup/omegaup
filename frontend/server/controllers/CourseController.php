<?php

class CourseController extends Controller {
    /**
     * Validates create or update Courses
     *
     * @throws InvalidParameterException
     */
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        $is_required = true;

        Validators::isStringNonEmpty($r['name'], 'title', $is_required);
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
            $existing = SchoolsDAO::findByName($r['name']);
            if (count($existing) > 0) {
                $course_id = $existing[0]->course_id;
            } else {
                CoursesDAO::save($course);
                $course_id = $course->course_id;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return array('status' => 'ok', 'course_id' => $course_id);
    }
}
