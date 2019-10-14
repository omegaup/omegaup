<?php

/**
 * Description of RunRejudgeTest
 *
 * @author joemmanuel
 */

class RunRejudgeTest extends OmegaupTestCase {
    /**
     * Basic test of rerun
     */
    public function testRejudgeWithoutCompileError() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestant
        );

        // Grade the run
        RunsFactory::gradeRun($runData);

        $detourGrader = new ScopedGraderDetour();

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
