<?php

/**
 * Description of ContestRunsTest
 *
 * @author joemmanuel
 */

class ContestRunsTest extends OmegaupTestCase {
    /**
     * Contestant submits runs and admin is able to get them
     */
    public function testGetRunsForContest() {
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

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call API
        $response = ContestController::apiRuns($r);

        // Assert
        $this->assertEquals(1, count($response['runs']));
        $this->assertEquals($runData['response']['guid'], $response['runs'][0]['guid']);
        $this->assertEquals($contestant->username, $response['runs'][0]['username']);
        $this->assertEquals('J1', $response['runs'][0]['judged_by']);

        // Contest admin should be able to view run, even if not problem admin.
        $identity = IdentitiesDAO::getByPK($contestData['director']->main_identity_id);
        $this->assertFalse(Authorization::isProblemAdmin(
            $identity,
            $contestData['director'],
            $problemData['problem']
        ));
        $response = RunController::apiDetails(new Request([
            'problemset_id' => $contestData['contest']->problemset_id,
            'run_alias' => $response['runs'][0]['guid'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals($runData['request']['source'], $response['source']);
    }

    /**
     * Contestant submits runs and admin edits the points of a problem
     * while the contest is active
     */
    public function testEditProblemsetPointsDuringAContest() {
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

        // Build request
        $directorLogin = self::login($contestData['director']);

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals(100, $response['runs'][0]['contest_score']);

        $r = new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 80,
            'order_in_contest' => 1,
        ]);

        // Call API with different points value
        ContestController::apiAddProblem($r);

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals(80, $response['runs'][0]['contest_score']);
    }
}
