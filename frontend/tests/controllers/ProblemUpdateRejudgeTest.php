<?php
/**
 * Description of ProblemUpdateRejudgeTest
 */

class ProblemUpdateRejudgeTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );
    }

    /**
     * Data provider for problem edit tests.
     *
     * This provides various fields and values to test if they cause a rejudge
     * when edited.
     *
     * @return list<array{string, int|float|string|list<string>, bool}>
     */
    public static function problemEditProvider() {
        return [
            ['allow_user_add_tags', true, false],
            ['email_clarifications', true, false],
            ['extra_wall_time', 1000, true],
            ['input_limit', 20480, false],
            ['languages', ['cpp11', 'py3'], false],
            ['memory_limit', 64000, true],
            ['output_limit', 20480, true],
            ['overall_wall_time_limit', 60000, false],
            ['problem_level', 'intermediate', false],
            ['redirect', false, false],
            ['selected_tags', 'math,dp', false],
            ['show_diff', 'examples', false],
            [
                'group_score_policy',
                \OmegaUp\ProblemParams::GROUP_SCORE_POLICY_SUM_IF_NOT_ZERO,
                false,
            ],
            ['source', 'Nueva fuente', false],
            ['time_limit', 2000, true],
            ['title', 'Nuevo título', false],
            [
                'update_published',
                \OmegaUp\ProblemParams::UPDATE_PUBLISHED_EDITABLE_PROBLEMSETS,
                false,
            ],
            ['validator', \OmegaUp\ProblemParams::VALIDATOR_TOKEN, false],
            ['validator_time_limit', 500, false],
            ['visibility', 'public', false],
        ];
    }

    /**
     * @dataProvider problemEditProvider
     *
     * @param string $field The field being edited.
     * @param int|float|string|list<string> $newValue The new value for the field.
     * @param bool $shouldRejudge Whether the edit should trigger a rejudge.
     */
    public function testRejudgeOnProblemEdit(
        string $field,
        $newValue,
        bool $shouldRejudge
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $runData = [];
        // Create 2 runs
        $runData[0] = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runData[1] = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );

        // Grade the runs
        \OmegaUp\Test\Factories\Run::gradeRun($runData[0]);
        \OmegaUp\Test\Factories\Run::gradeRun($runData[1]);

        // Update Problem calls grader to rejudge, we need to detour grader calls
        // We will submit 2 runs to the problem, a call to grader to rejudge them
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Login as problem admin
        $login = self::login($problemData['author']);

        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            $field => $newValue,
            'message' => "Update {$field} for testing",
            'problem_alias' => $problemData['request']['problem_alias'],
        ]);

        if ($field === 'memory_limit') {
            $request['overall_wall_time_limit'] = 30000; // 30 seconds
        }

        \OmegaUp\Controllers\Problem::apiUpdate($request);

        $graderCalls = $shouldRejudge ? 2 : 0;
        $this->assertEquals(
            $graderCalls,
            $detourGrader->getGraderCallCount(),
            "Expected {$graderCalls} grader calls, got " . $detourGrader->getGraderCallCount()
        );
    }
}
