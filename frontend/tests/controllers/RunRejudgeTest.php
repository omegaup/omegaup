<?php

/**
 * Description of RunRejudgeTest
 *
 * @author joemmanuel
 */

class RunRejudgeTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test of rerun
     */
    public function testRejudgeWithoutCompileError() {
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Build request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiRejudge($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, $detourGrader->getGraderCallCount());
    }
}
