<?php

namespace OmegaUp\Test\Factories;

use OmegaUp\Exceptions\NotFoundException;

/**
 * @psalm-type LimitsSettings=array{ExtraWallTime: string, MemoryLimit: int|string, OutputLimit: int|string, OverallWallTimeLimit: string, TimeLimit: string}
 * @psalm-type InteractiveSettingsDistrib=array{idl: string, module_name: string, language: string, main_source: string, templates: array<string, string>}
 * @psalm-type ProblemsetterInfo=array{classname: string, creation_date: \OmegaUp\Timestamp|null, name: string, username: string}
 * @psalm-type ProblemSettingsDistrib=array{cases: array<string, array{in: string, out: string, weight?: float}>, interactive?: InteractiveSettingsDistrib, limits: LimitsSettings, validator: array{custom_validator?: array{language: string, limits?: LimitsSettings, source: string}, name: string, tolerance?: float}}
 * @psalm-type ProblemStatement=array{images: array<string, string>, sources: array<string, string>, language: string, markdown: string}
 * @psalm-type Run=array{alias: string, classname: string, contest_alias: null|string, contest_score: float|null, country: string, execution: null|string, guid: string, language: string, memory: int, output: null|string, penalty: int, runtime: int, score: float, score_by_group?: array<string, float|null>, status: string, status_memory: null|string, status_runtime: null|string, submit_delay: int, suggestions?: int, time: \OmegaUp\Timestamp, type: null|string, username: string, verdict: string}
 * @psalm-type ProblemDetailsForTesting=array{accepted: int, admin?: bool, alias: string, allow_user_add_tags: bool, commit: string, creation_date: \OmegaUp\Timestamp, difficulty: float|null, email_clarifications: bool, input_limit: int, languages: list<string>, order: string, points: float, preferred_language?: string, problem_id: int, problemsetter?: ProblemsetterInfo, quality_seal: bool, runs?: list<Run>, score: float, settings: ProblemSettingsDistrib, solvers?: list<array{language: string, memory: float, runtime: float, time: \OmegaUp\Timestamp, username: string}>, source?: string, statement: ProblemStatement, submissions: int, title: string, version: string, visibility: int, visits: int}
 */
class Run {
    const RUN_SOLUTIONS = [
        'c11-gcc' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        'py3' => 'print(3)',
    ];

    /**
     * Builds and returns a request object to be used for \OmegaUp\Controllers\Run::apiCreate
     *
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     * @param ?array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
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
     * @param \OmegaUp\DAO\VO\Identities $participant
     * @param string $language
     * @return \OmegaUp\Request
     */
    private static function createRequestCourseAssignmentCommon(
        $problemData,
        ?\OmegaUp\DAO\VO\Assignments $assignment,
        $participant,
        \OmegaUp\Test\ScopedLoginToken $login = null,
        $language = 'c11-gcc'
    ) {
        if (is_null($login)) {
            // Login as participant
            $login = \OmegaUp\Test\ControllerTestCase::login($participant);
        }
        // Build request
        if (is_null($assignment)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'assignmentNotFound'
            );
        }

        return new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $assignment->problemset_id,
            'problem_alias' => $problemData['problem']->alias,
            'language' => $language,
            'source' => self::RUN_SOLUTIONS[$language],
        ]);
    }

    /**
     * Creates a run
     *
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param array{admin: \OmegaUp\DAO\VO\Identities, assignment: \OmegaUp\DAO\VO\Assignments|null, assignment_alias: string, course: \OmegaUp\DAO\VO\Courses, course_alias: string, problemset_id: int|null, request: \OmegaUp\Request} $courseAssignmentData
     * @param \OmegaUp\DAO\VO\Identities $participant
     * @param string $language
     *
     * @return array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}
     */
    public static function createCourseAssignmentRun(
        $problemData,
        $courseAssignmentData,
        $participant,
        $language = 'c11-gcc'
    ): array {
        if (!is_string($courseAssignmentData['request']['course_alias'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'course_alias'
            );
        }

        if (!is_string($courseAssignmentData['request']['alias'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'alias'
            );
        }

        // Our participant has to open the course before sending a run
        \OmegaUp\Test\Factories\Course::openCourse(
            strval($courseAssignmentData['request']['course_alias']),
            $participant
        );

        // Our participant has to open the assignment in a course before sending a run
        \OmegaUp\Test\Factories\Course::openAssignmentCourse(
            strval($courseAssignmentData['request']['course_alias']),
            strval($courseAssignmentData['request']['alias']),
            $participant
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            strval($courseAssignmentData['request']['course_alias']),
            strval($courseAssignmentData['request']['alias']),
            $problemData,
            $participant
        );

        $r = self::createRequestCourseAssignmentCommon(
            $problemData,
            $courseAssignmentData['assignment'],
            $participant,
            login: null,
            language: $language,
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
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     * @param \OmegaUp\DAO\VO\Identities $participant
     * @param string $language
     *
     * @return array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}
     */
    public static function createAssignmentRun(
        string $courseAlias,
        string $assignmentAlias,
        $problemData,
        $participant,
        $language = 'c11-gcc'
    ): array {
        // Our participant has to open the course before sending a run
        \OmegaUp\Test\Factories\Course::openCourse(
            $courseAlias,
            $participant
        );

        // Our participant has to open the assignment in a course before sending a run
        \OmegaUp\Test\Factories\Course::openAssignmentCourse(
            $courseAlias,
            $assignmentAlias,
            $participant
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            $courseAlias,
            $assignmentAlias,
            $problemData,
            $participant
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course)) {
            throw new NotFoundException('courseNotFound');
        }

        $r = self::createRequestCourseAssignmentCommon(
            $problemData,
            \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                $assignmentAlias,
                intval($course->course_id)
            ),
            participant: $participant,
            login: null,
            language: $language,
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
     * @param array{contest: \OmegaUp\DAO\VO\Contests, director: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, userDirector: \OmegaUp\DAO\VO\Users} $contestData
     * @param \OmegaUp\DAO\VO\Identities $contestant
     * @return array{contestant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}, details: ProblemDetailsForTesting}
     */
    public static function createRun(
        array $problemData,
        array $contestData,
        \OmegaUp\DAO\VO\Identities $contestant,
        bool $inPracticeMode = false
    ): array {
        // Our contestant has to open the contest before sending a run
        if (!$inPracticeMode) {
            \OmegaUp\Test\Factories\Contest::openContest(
                $contestData['contest'],
                $contestant
            );
        }

        // Then we need to open the problem
        $details = \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $contestant
        );

        $r = self::createRequestCommon(
            $problemData,
            $inPracticeMode ? null : $contestData,
            $contestant
        );

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
     * @param array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request} $problemData
     *
     * @return array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}
     */
    public static function createRunForSpecificProblem(
        \OmegaUp\DAO\VO\Identities $identity,
        array $problemData,
        string $runCreationDate,
        float $points = 1,
        string $verdict = 'AC'
    ): array {
        $r = self::createRequestCommon(
            $problemData,
            contestData: null,
            contestant: $identity
        );

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $runData = [
            'request' => $r,
            'participant' => $identity,
            'response' => $response,
        ];

        \OmegaUp\Test\Factories\Run::gradeRun($runData, $points, $verdict);

        // Force the submission to be in any date
        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );
        if (is_null($submission) || is_null($submission->current_run_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $submission->time = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $runCreationDate
        );
        \OmegaUp\DAO\Submissions::update($submission);
        $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
        if (is_null($run)) {
            throw new \OmegaUp\Exceptions\NotFoundException('runNotFound');
        }
        $run->time = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $runCreationDate
        );
        \OmegaUp\DAO\Runs::update($run);

        return $runData;
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
     * @param ?array{participant: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{guid: string, submission_deadline: \OmegaUp\Timestamp, nextSubmissionTimestamp: \OmegaUp\Timestamp}}  $runData     The run.
     * @param float   $points              The score of the run
     * @param string  $verdict             The verdict of the run.
     * @param ?int    $submitDelay         The number of minutes worth of penalty.
     * @param ?string $runGuid             The GUID of the submission.
     * @param ?int    $runID               The ID of the run.
     * @param int     $problemsetPoints    The max score of the run for the problemset.
     * @param ?string $outputFilesContent  The content to compress in files.zip.
     * @param string  $problemsetScoreMode The score mode for a problemset. The
     *                                     points will be calulated in a different
     *                                     way when score mode is `max_per_group`.
     * @param list<array{group_name: string, score: float, verdict: string}>   $runScoreByGroups    The score by groups.
     */
    public static function gradeRun(
        ?array $runData = null,
        float $points = 1,
        string $verdict = 'AC',
        ?int $submitDelay = null,
        ?string $runGuid = null,
        ?int $runId = null,
        int $problemsetPoints = 100,
        ?string $outputFilesContent = null,
        string $problemsetScoreMode = 'partial',
        array $runScoreByGroups = []
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
            $problemsetPoints,
            $outputFilesContent,
            $problemsetScoreMode,
            $runScoreByGroups
        );
    }

    /**
     * Gets the sum of the maximum scores obtained in each group for the
     * submissions list. In case of the submissions list is empty, the function
     * will return 0.0
     *
     * @param list<Run> $runs
     */
    public static function getMaxPerGroupScore($runs): float {
        if (empty($runs)) {
            return 0.0;
        }
        $maxPerGroupScore = array_reduce(array_keys(
            $runs[0]['score_by_group'] ?? []
        ), function (
            $acc,
            $key
        ) use ($runs) {
            $values = array_map(function ($run) use ($key) {
                return $run['score_by_group'][$key] ?? 0.0;
            }, array_filter($runs, function ($run) {
                return isset($run['score_by_group']);
            }));
            if (!empty($values)) {
                $acc[$key] = max($values);
            } else {
                $acc[$key] = 0.0;
            }
                return $acc;
        }, []);

        return array_sum($maxPerGroupScore);
    }
}
