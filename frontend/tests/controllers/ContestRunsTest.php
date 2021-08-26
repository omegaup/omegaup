<?php
/**
 * Description of ContestRunsTest
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

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
     * Contestant submits runs and admin is able to get them into the contest
     * details for typescript
     */
    public function testGetRunsForContestDetails() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get problems and add them to the contest
        $problems = [];
        foreach (range(0, 4) as $index) {
            $problems[$index] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$index],
                $contestData
            );
        }

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create one run for every problem
        $runs = [];
        foreach ($problems as $index => $problem) {
            $runs[$index] = \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contestData,
                $identity
            );

            // Grade the run
            \OmegaUp\Test\Factories\Run::gradeRun($runs[$index]);
        }

        // Create request
        $login = self::login($contestData['director']);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Call API
        $response = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload']['adminPayload'];

        // Assert
        $this->assertCount(5, $response['allRuns']);

        $guidsExpected = array_map(
            fn ($run) => $run['response']['guid'],
            $runs
        );
        $guidsActual = array_reverse(
            array_map(
                fn ($run) => $run['guid'],
                $response['allRuns']
            )
        );

        $this->assertEquals($guidsExpected, $guidsActual);
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ])
        );

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
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ])
        );

        $this->assertEquals(80, $response['runs'][0]['contest_score']);
    }
}
