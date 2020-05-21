<?php

namespace OmegaUp\Test\Factories;

/**
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type InteractiveSettings=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type ProblemSettings=array{cases: array<string, array{in: string, out: string, weight?: float}>, limits: LimitsSettings, interactive?: InteractiveSettings, validator: array{name: string, tolerance?: float, custom_validator?: array{source: string, language: string, limits?: LimitsSettings}}}
 * @psalm-type ProblemStatement=array{images: array<string, string>, language: string, markdown: string}
 * @psalm-type Run=array{guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float|null, time: \OmegaUp\Timestamp, submit_delay: int, type: null|string, username: string, classname: string, alias: string, country: string, contest_alias: null|string}
 */
class Run {
    /**
     * Builds and returns a request object to be used for \OmegaUp\Controllers\Run::apiCreate
     *
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     * @param ?array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $contestant
     */
    private static function createRequestCommon(
        array $problemData,
        ?array $contestData,
        $contestant,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ): \OmegaUp\Request {
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
                'language' => 'c11-gcc',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);
        }

        return new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
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
            'language' => 'c11-gcc',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);
    }

    /**
     * Creates a run
     *
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{admin: \OmegaUp\DAO\VO\Identities, assignment: \OmegaUp\DAO\VO\Assignments|null, assignment_alias: string, course: \OmegaUp\DAO\VO\Courses, course_alias: string, problemset_id: int|null, request: \OmegaUp\Request} $courseAssignmentData
     * @param \OmegaUp\DAO\VO\Identities $participant
     *
     * @return array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}
     */
    public static function createCourseAssignmentRun(
        $problemData,
        $courseAssignmentData,
        $participant
    ) {
        // Our participant has to open the course before sending a run
        \OmegaUp\Test\Factories\Course::openCourse(
            $courseAssignmentData,
            $participant
        );

        // Our participant has to open the assignment in a course before sending a run
        \OmegaUp\Test\Factories\Course::openAssignmentCourse(
            $courseAssignmentData,
            $participant
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
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

        return [
            'request' => $r,
            'participant' => $participant,
            'response' => $response
        ];
    }

    /**
     * Creates a run
     *
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     * @param array{contest: \OmegaUp\DAO\VO\Contests|null, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $contestant
     * @return array{contestant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}, details: array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, exists: true, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problemsetter?: array{creation_date: \OmegaUp\Timestamp|null, name: string, username: string}, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettings, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}|array{exists: false}}
     */
    public static function createRun(
        array $problemData,
        array $contestData,
        \OmegaUp\DAO\VO\Identities $contestant
    ): array {
        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest($contestData, $contestant);

        // Then we need to open the problem
        $details = \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        $r = self::createRequestCommon($problemData, $contestData, $contestant);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        return [
            'request' => $r,
            'contestant' => $contestant,
            'response' => $response,
            'details' => $details,
        ];
    }

    /**
     * Creates a run to the given problem
     *
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     * @param \OmegaUp\DAO\VO\Identities $contestant
     * @return array{contestant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}
     */
    public static function createRunToProblem(
        array $problemData,
        \OmegaUp\DAO\VO\Identities $contestant,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ): array {
        $r = self::createRequestCommon($problemData, null, $contestant, $login);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        return [
            'request' => $r,
            'contestant' => $contestant,
            'response' => $response
        ];
    }

    public static function updateRunTime(
        string $runGuid,
        \OmegaUp\Timestamp $time
    ): void {
        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runGuid
        );
        if (is_null($submission)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $submission->time = $time;
        \OmegaUp\DAO\Submissions::update($submission);

        $run = \OmegaUp\DAO\Runs::getByPK(intval($submission->current_run_id));
        if (is_null($run)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $run->time = $time;
        \OmegaUp\DAO\Runs::update($run);
    }

    /**
     * Given a run, set a score to a given run
     *
     * @param ?array{contestant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}  $runData     The run.
     * @param float   $points           The score of the run
     * @param string  $verdict          The verdict of the run.
     * @param ?int    $submitDelay      The number of minutes worth of penalty.
     * @param ?string $runGuid          The GUID of the submission.
     * @param ?int    $runID            The ID of the run.
     * @param int     $problemsetPoints The max score of the run for the problemset.
     */
    public static function gradeRun(
        ?array $runData,
        float $points = 1,
        string $verdict = 'AC',
        ?int $submitDelay = null,
        ?string $runGuid = null,
        ?int $runId = null,
        int $problemsetPoints = 100
    ): void {
        if (!is_null($runGuid)) {
            $guid = $runGuid;
        } elseif (!is_null($runData)) {
            $guid = $runData['response']['guid'];
        } else {
            $guid = null;
        }
        \OmegaUp\Test\Utils::gradeRun(
            $runId,
            $guid,
            $points,
            $verdict,
            $submitDelay,
            $problemsetPoints
        );
    }
}
