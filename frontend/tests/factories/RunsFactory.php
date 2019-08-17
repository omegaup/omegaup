<?php

/**
 * Description of RunsFactory
 *
 * @author joemmanuel
 */

class RunsFactory {
    /**
     * Builds and returns a request object to be used for RunController::apiCreate
     *
     * @param type $problemData
     * @param type $contestData
     * @param type $contestant
     * @return \OmegaUp\Request
     */
    private static function createRequestCommon($problemData, $contestData, $contestant, ScopedLoginToken $login = null) {
        if ($login == null) {
            // Login as contestant
            $login = OmegaupTestCase::login($contestant);
        }

        // Build request
        if (!is_null($contestData)) {
            return new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        }

        return new \OmegaUp\Request([
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
     * @return \OmegaUp\Request
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
            return new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $courseAssignmentData['assignment']->problemset_id,
                'problem_alias' => $problemData['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        }

        return new \OmegaUp\Request([
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
     * @param \OmegaUp\DAO\VO\Users $contestant
     * @return array
     */
    public static function createRun($problemData, $contestData, $contestant) {
        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        $r = self::createRequestCommon($problemData, $contestData, $contestant);

        // Call API
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
     * Given a run, set a score to a given run
     *
     * @param ?array  $runData     The run.
     * @param float   $points      The score of the run
     * @param string  $verdict     The verdict of the run.
     * @param ?int    $submitDelay The number of minutes worth of penalty.
     * @param ?string $runGuid     The GUID of the submission.
     * @param ?int    $runID       The ID of the run.
     */
    public static function gradeRun(
        ?array $runData,
        float $points = 1,
        string $verdict = 'AC',
        ?int $submitDelay = null,
        ?string $runGuid = null,
        ?int $runId = null
    ) : void {
        $guid = $runGuid === null ? $runData['response']['guid'] : $runGuid;
        Utils::gradeRun($runId, $guid, $points, $verdict, $submitDelay);
    }
}
