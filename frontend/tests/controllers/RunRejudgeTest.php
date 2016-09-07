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
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Detour grader calls expecting one call
        $this->detourGraderCalls($this->once());

        // Build request
        $r = new Request();
        $r['run_alias'] = $runData['response']['guid'];
        $r['auth_token'] = self::login($contestData['director']);

        // Call API
        $response = RunController::apiRejudge($r);

        $this->assertEquals('ok', $response['status']);
    }
}
