<?php

class UserRankTest extends OmegaupTestCase {
    private function refreshUserRank() {
        $admin = UserFactory::createAdminUser();

        $adminLogin = self::login($admin);
        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
        ]);
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
            if ($entry['username'] == $contestant->username) {
                $found = true;
                $this->assertEquals($entry['name'], $contestant->name);
                $this->assertEquals($entry['problems_solved'], 1);
                $this->assertEquals($entry['score'], 100);
            }
        }

        $this->assertTrue($found);
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
            if ($entry['username'] == $contestant->username) {
                $found = true;
                $this->assertEquals($entry['name'], $contestant->name);
                $this->assertEquals($entry['problems_solved'], 1);
                $this->assertEquals($entry['score'], 100);
            }

            if ($entry['username'] == $contestant2->username) {
                $this->fail('User with private problem solved showed in rank.');
            }
        }

        $this->assertTrue($found);
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
        $response = UserController::apiRankByProblemsSolved(new Request([
            'username' => $contestant->username
        ]));

        $this->assertEquals($response['name'], $contestant->name);
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
        $response = UserController::apiRankByProblemsSolved(new Request([
            'username' => $contestant->username
        ]));

        $this->assertEquals($response['name'], $contestant->name);
        $this->assertEquals($response['problems_solved'], 0);
        $this->assertEquals($response['rank'], 0);
    }

    /**
     * Tests apiRankByProblemsSolved filters
     */
    public function testUserRankFiltered() {
        // Create a school
        $school = SchoolsFactory::createSchool();
        // Create a user with no country, state and school
        $contestantWithNoCountry = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runDataContestantWithNoCountry = RunsFactory::createRunToProblem($problemData, $contestantWithNoCountry);
        RunsFactory::gradeRun($runDataContestantWithNoCountry);

        // Create a user with country, state and school
        $contestant = UserFactory::createUser();
        $login = self::login($contestant);

        $states = StatesDAO::search(['country_id' => 'MX']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]);

        UserController::apiUpdate($r);

        // create runs
        $runDataContestant = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runDataContestant);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $runDataContestant['request']['auth_token'],
            'filter' => 'country'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $runDataContestant['request']['auth_token'],
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $runDataContestant['request']['auth_token'],
            'filter' => 'school'
        ]));
        $this->assertCount(1, $response['rank']);
    }

    /**
     * Tests apiRankByProblemsSolved with state collision
     */
    public function testUserRankWithStateCollision() {
        // Create two problems
        $problemData[] = ProblemsFactory::createProblem();
        $problemData[] = ProblemsFactory::createProblem();

        // Create two users from Maranhao, Brasil
        $contestantFromMaranhao1 = UserFactory::createUser();
        $login = self::login($contestantFromMaranhao1);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]);

        UserController::apiUpdate($r);

        // Create two runs of different problems
        $runDataContestantFromMaranhao = RunsFactory::createRunToProblem($problemData[0], $contestantFromMaranhao1);
        RunsFactory::gradeRun($runDataContestantFromMaranhao);
        $runDataContestantFromMaranhao = RunsFactory::createRunToProblem($problemData[1], $contestantFromMaranhao1);
        RunsFactory::gradeRun($runDataContestantFromMaranhao);

        $contestantFromMaranhao2 = UserFactory::createUser();
        $login = self::login($contestantFromMaranhao2);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]);

        UserController::apiUpdate($r);

        // Create o run of one problem
        $runDataContestantFromMaranhao = RunsFactory::createRunToProblem($problemData[0], $contestantFromMaranhao2);
        RunsFactory::gradeRun($runDataContestantFromMaranhao);

        // Create a user from Massachusetts, USA
        $contestantFromMassachusetts = UserFactory::createUser();
        $login = self::login($contestantFromMassachusetts);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'US',
            'state_id' => 'MA'
        ]);

        UserController::apiUpdate($r);

        // create a run of one problem
        $runDataContestantFromMassachusetts = RunsFactory::createRunToProblem($problemData[0], $contestantFromMassachusetts);
        RunsFactory::gradeRun($runDataContestantFromMassachusetts);

        // Refresh Rank
        $this->refreshUserRank();

        $login = self::login($contestantFromMaranhao1);

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        $login = self::login($contestantFromMaranhao2);

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        $login = self::login($contestantFromMassachusetts);

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
    }
}
