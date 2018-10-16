<?php

/**
 * Description of RunsFactory
 *
 * @author joemmanuel
 */

/**
 * Detours the Grader calls.
 * Problem: Submiting a new run invokes the Grader::grade() function which makes
 * a HTTP call to official grader using CURL. This call will fail if grader is
 * not turned on. We are not testing the Grader functionallity itself, we are
 * only validating that we populate the DB correctly and that we make a call
 * to the function Grader::grade(), without executing the contents.
 *
 * Solution: We create a phpunit mock of the Grader class. We create a fake
 * object Grader with the function grade() which will always return true
 * and expects to be excecuted once.
 *
 */
class GraderMock extends Grader {
    public function Grade($runGuids, $rejudge, $debug) {
        return;
    }
}

class RunsFactory {
    /**
     * Builds and returns a request object to be used for RunController::apiCreate
     *
     * @param type $problemData
     * @param type $contestData
     * @param type $contestant
     * @return Request
     */
    private static function createRequestCommon($problemData, $contestData, $contestant, ScopedLoginToken $login = null) {
        if ($login == null) {
            // Login as contestant
            $login = OmegaupTestCase::login($contestant);
        }

        // Build request
        if (!is_null($contestData)) {
            return new Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        }

        return new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);
    }

    /**
     * Builds and returns a request object to be used for RunController::apiCreate
     *
     * @param type $problemData
     * @param type $courseAssignmentData
     * @param type $participant
     * @return Request
     */
    private static function createRequestCourseAssignmentCommon(
        $problemData,
        $courseAssignmentData,
        $participant,
        ScopedLoginToken $login = null
    ) {
        if ($login == null) {
            // Login as participant
            $login = OmegaupTestCase::login($participant);
        }
        // Build request
        if (!is_null($courseAssignmentData)) {
            return new Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $courseAssignmentData['assignment']->problemset_id,
                'problem_alias' => $problemData['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        }

        return new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);
    }

    /**
     * Creates a run
     *
     * @param type $problemData
     * @param type $courseAssignmentData
     * @param $participant
     * @return array
     */
    public static function createCourseAssignmentRun($problemData, $courseAssignmentData, $participant) {
        // Our participant has to open the course before sending a run
        CoursesFactory::openCourse($courseAssignmentData, $participant);

        // Our participant has to open the assignment in a course before sending a run
        CoursesFactory::openAssignmentCourse($courseAssignmentData, $participant);

        // Then we need to open the problem
        CoursesFactory::openProblemInCourseAssignment($courseAssignmentData, $problemData, $participant);

        $r = self::createRequestCourseAssignmentCommon($problemData, $courseAssignmentData, $participant);

        // Call API
        RunController::$grader = new GraderMock();
        $response = RunController::apiCreate($r);

        // Clean up
        unset($_REQUEST);

        return [
            'request' => $r,
            'participant' => $participant,
            'response' => $response
        ];
    }

    /**
     * Creates a run
     *
     * @param type $problemData
     * @param type $contestData
     * @param Users $contestant
     * @return array
     */
    public static function createRun($problemData, $contestData, $contestant) {
        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        $r = self::createRequestCommon($problemData, $contestData, $contestant);

        // Call API
        RunController::$grader = new GraderMock();
        $response = RunController::apiCreate($r);

        // Clean up
        unset($_REQUEST);

        return [
            'request' => $r,
            'contestant' => $contestant,
            'response' => $response
        ];
    }

    /**
     * Creates a run to the given problem
     *
     * @param type $problemData
     * @param type $contestant
     */
    public static function createRunToProblem($problemData, $contestant, ScopedLoginToken $login = null) {
        $r = self::createRequestCommon($problemData, null, $contestant, $login);

        // Call API
        RunController::$grader = new GraderMock();
        $response = RunController::apiCreate($r);

        // Clean up
        unset($_REQUEST);

        return [
            'request' => $r,
            'contestant' => $contestant,
            'response' => $response
        ];
    }

    /**
     * Given a run id, set a score to a given run
     *
     * @param type $runData
     * @param int $points
     * @param string $verdict
     */
    public static function gradeRun($runData, $points = 1, $verdict = 'AC', $submitDelay = null, $runGuid = null) {
        $run = RunsDAO::getByAlias($runGuid === null ? $runData['response']['guid'] : $runGuid);

        $run->verdict = $verdict;
        $run->score = $points;
        $run->contest_score = $points * 100;
        $run->status = 'ready';
        $run->judged_by = 'J1';

        if (!is_null($submitDelay)) {
            $run->submit_delay = $submitDelay;
            $run->penalty = $submitDelay;
        }

        RunsDAO::save($run);

        $gradeDir = RunController::getGradePath($run->guid);
        mkdir($gradeDir, 0755, true);
        file_put_contents("$gradeDir/details.json", json_encode([
            'verdict' => $verdict,
            'contest_score' => $points,
            'score' => $points,
            'judged_by' => 'RunsFactory.php',
        ]));
    }
}
