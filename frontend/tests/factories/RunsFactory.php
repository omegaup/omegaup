<?php

/**
 * Description of RunsFactory
 *
 * @author joemmanuel
 */

class RunsFactory {
    /**
     * Builds and returns a request object to be used for \OmegaUp\Controllers\Run::apiCreate
     *
     * @param type $problemData
     * @param type $contestData
     * @param type $contestant
     * @return \OmegaUp\Request
     */
    private static function createRequestCommon(
        $problemData,
        $contestData,
        $contestant,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ) {
        if (is_null($login)) {
            // Login as contestant
            $login = \OmegaUp\Test\ControllerTestCase::login($contestant);
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
     * Builds and returns a request object to be used for \OmegaUp\Controllers\Run::apiCreate
     *
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{admin: \OmegaUp\DAO\VO\Identities, assignment: \OmegaUp\DAO\VO\Assignments|null, assignment_alias: string, course: \OmegaUp\DAO\VO\Courses, course_alias: string, problemset_id: int|null, request: \OmegaUp\Request} $courseAssignmentData
     * @param \OmegaUp\DAO\VO\Identities $participant
     * @return \OmegaUp\Request
     */
    private static function createRequestCourseAssignmentCommon(
        $problemData,
        $courseAssignmentData,
        $participant,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ) {
        if (is_null($login)) {
            // Login as participant
            $login = \OmegaUp\Test\ControllerTestCase::login($participant);
        }
        // Build request
        if (is_null($courseAssignmentData['assignment'])) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        return new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $courseAssignmentData['assignment']->problemset_id,
            'problem_alias' => $problemData['problem']->alias,
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);
    }

    /**
     * Creates a run
     *
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{admin: \OmegaUp\DAO\VO\Identities, assignment: \OmegaUp\DAO\VO\Assignments|null, assignment_alias: string, course: \OmegaUp\DAO\VO\Courses, course_alias: string, problemset_id: int|null, request: \OmegaUp\Request} $courseAssignmentData
     * @param \OmegaUp\DAO\VO\Identities $participant
     * @return array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string, guid: string, submission_deadline: int, nextSubmissionTimestamp: int}}
     */
    public static function createCourseAssignmentRun(
        $problemData,
        $courseAssignmentData,
        $participant
    ) {
        // Our participant has to open the course before sending a run
        CoursesFactory::openCourse($courseAssignmentData, $participant);

        // Our participant has to open the assignment in a course before sending a run
        CoursesFactory::openAssignmentCourse(
            $courseAssignmentData,
            $participant
        );

        // Then we need to open the problem
        CoursesFactory::openProblemInCourseAssignment(
            $courseAssignmentData,
            $problemData,
            $participant
        );

        $r = self::createRequestCourseAssignmentCommon(
            $problemData,
            $courseAssignmentData,
            $participant
        );

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

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
     * @param \OmegaUp\DAO\VO\Identities $contestant
     * @return array
     */
    public static function createRun($problemData, $contestData, $contestant) {
        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest($contestData, $contestant);

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        $r = self::createRequestCommon($problemData, $contestData, $contestant);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

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
    public static function createRunToProblem(
        $problemData,
        $contestant,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ) {
        $r = self::createRequestCommon($problemData, null, $contestant, $login);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

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
    ): void {
        $guid = is_null($runGuid) ? $runData['response']['guid'] : $runGuid;
        \OmegaUp\Test\Utils::gradeRun(
            $runId,
            $guid,
            $points,
            $verdict,
            $submitDelay
        );
    }
}
