<?php

class UserRankTest extends OmegaupTestCase {
    private function refreshUserRank() {
        $r = new Request();
        $admin = UserFactory::createAdminUser();

        $r['auth_token'] = $this->login($admin);
        UserController::apiRefreshUserRank($r);
    }

    /**
     * Tests apiRankByProblemsSolved
     */
    public function testFullRankByProblemSolved() {
        // Create a user and sumbit a run with him
        $contestant = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request());

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $contestant->getUsername()) {
                $found = true;
                $this->assertEquals($entry['name'], $contestant->getName());
                $this->assertEquals($entry['problems_solved'], 1);
                $this->assertEquals($entry['score'], 100);
            }
        }
        // TODO(joe): Fix.
        // $this->assertTrue($found);
    }

    /**
     * Tests apiRankByProblemsSolved
     */
    public function testFullRankByProblemSolvedNoPrivateProblems() {
        // Create a user and sumbit a run with him
        $contestant = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Create a user and sumbit a run with him
        $contestant2 = UserFactory::createUser();
        $problemDataPrivate = ProblemsFactory::createProblem(null, null, 0);
        $runDataPrivate = RunsFactory::createRunToProblem($problemDataPrivate, $contestant2);
        RunsFactory::gradeRun($runDataPrivate);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request());

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $contestant->getUsername()) {
                $found = true;
                $this->assertEquals($entry['name'], $contestant->getName());
                $this->assertEquals($entry['problems_solved'], 1);
                $this->assertEquals($entry['score'], 100);
            }

            if ($entry['username'] == $contestant2->getUsername()) {
                $this->fail('User with private problem solved showed in rank.');
            }
        }
        // TODO(joe): Fix.
        // $this->assertTrue($found);
    }

    /**
     * Tests apiRankByProblemsSolved for a specific user
     */
    public function testUserRankByProblemsSolved() {
        // Create a user and sumbit a run with him
        $contestant = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request(array(
            'username' => $contestant->getUsername()
        )));

        $this->assertEquals($response['name'], $contestant->getName());
        $this->assertEquals($response['problems_solved'], 1);
    }

    /**
     * Tests apiRankByProblemsSolved for a specific user with no runs
     */
    public function testUserRankByProblemsSolvedWith0Runs() {
        // Create a user with no runs
        $contestant = UserFactory::createUser();

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request(array(
            'username' => $contestant->getUsername()
        )));

        $this->assertEquals($response['name'], $contestant->getName());
        $this->assertEquals($response['problems_solved'], 0);
        $this->assertEquals($response['rank'], 0);
    }
}
