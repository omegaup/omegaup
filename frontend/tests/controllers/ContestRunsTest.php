<?php

/**
 * Description of ContestRunsTest
 *
 * @author joemmanuel
 */

class ContestRunsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Contestant submits runs and admin is able to get them
     */
    public function testGetRunsForContest() {
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

        // Create request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns($r);

        // Assert
        $this->assertEquals(1, count($response['runs']));
        $this->assertEquals(
            $runData['response']['guid'],
            $response['runs'][0]['guid']
        );
        $this->assertEquals(
            $identity->username,
            $response['runs'][0]['username']
        );

        // Contest admin should be able to view run, even if not problem admin.
        $directorIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $contestData['director']->username
        );
        $this->assertFalse(\OmegaUp\Authorization::isProblemAdmin(
            $directorIdentity,
            $problemData['problem']
        ));
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
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

        // Build request
        $directorLogin = self::login($contestData['director']);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals(100, $response['runs'][0]['contest_score']);

        $r = new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 80,
            'order_in_contest' => 1,
        ]);

        // Call API with different points value
        \OmegaUp\Controllers\Contest::apiAddProblem($r);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals(80, $response['runs'][0]['contest_score']);
    }
}
