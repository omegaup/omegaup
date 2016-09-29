<?php

class CourseController extends Controller {

    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        $is_required = true;

        Validators::isStringNonEmpty($r['name'], 'title', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($r['contest']->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($r['contest']->finish_time);

        // Validate start & finish time
        if ($start_time > $finish_time) {
            throw new InvalidParameterException('contestNewInvalidStartTime');
        }

        // Calculate the actual contest length
        $contest_length = $finish_time - $start_time;

        Validators::isValidAlias($r['alias'], 'alias', $is_required);

        // Show scoreboard is always optional
        Validators::isInEnum($r['show_scoreboard_after'], 'show_scoreboard_after', array('0', '1'), false /*is_required*/);

        if ($is_update) {
            // @TODO(alan): Prevent date changes if a contest already has runs
        }
    }

    public static function apiCreate(Request $r) {
        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r);

        $r['id_owner'] = $r['current_user_id'];

        $course = new Courses($r);

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

