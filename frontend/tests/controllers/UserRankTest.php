<?php

class UserRankTest extends OmegaupTestCase {
    private static function commit() {
        global $conn;
        $conn->Execute('COMMIT');
    }

    private function refreshUserRank() {
        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();
        shell_exec('python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/cron/update_user_rank.py' .
                 ' --quiet ' .
                 ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
                 ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
                 ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
                 ' --password ' . escapeshellarg(OMEGAUP_DB_PASS));

        UserController::deleteProblemsSolvedRankCacheList();
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
     * Tests refreshUserRank not displaying private profiles
     */
    public function testPrivateUserInRanking() {
        // Create a private user
        $contestantPrivate = UserFactory::createUser(new UserParams(['is_private' => true]));
        // Create one problem and a submission by the private user
        $problemData = ProblemsFactory::createProblem();
        $runDataPrivate = RunsFactory::createRunToProblem($problemData, $contestantPrivate);
        RunsFactory::gradeRun($runDataPrivate);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request());

        // Contestants should not appear in the rank as they're private.
        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $contestantPrivate->username) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
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
        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));
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

        $states = StatesDAO::getByCountry('MX');
        UserController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        // create runs
        $runDataContestant = RunsFactory::createRunToProblem($problemData, $contestant, $login);
        RunsFactory::gradeRun($runDataContestant);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
            'filter' => 'country'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $login->auth_token,
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
        $maranhao1Login = self::login($contestantFromMaranhao1);

        UserController::apiUpdate(new Request([
            'auth_token' => $maranhao1Login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]));

        // Create two runs of different problems
        $runDataContestantFromMaranhao1 = RunsFactory::createRunToProblem(
            $problemData[0],
            $contestantFromMaranhao1,
            $maranhao1Login
        );
        RunsFactory::gradeRun($runDataContestantFromMaranhao1);
        $runDataContestantFromMaranhao1 = RunsFactory::createRunToProblem(
            $problemData[1],
            $contestantFromMaranhao1,
            $maranhao1Login
        );
        RunsFactory::gradeRun($runDataContestantFromMaranhao1);

        $contestantFromMaranhao2 = UserFactory::createUser();
        $maranhao2Login = self::login($contestantFromMaranhao2);

        UserController::apiUpdate(new Request([
            'auth_token' => $maranhao2Login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]));

        // Create o run of one problem
        $runDataContestantFromMaranhao2 = RunsFactory::createRunToProblem(
            $problemData[0],
            $contestantFromMaranhao2,
            $maranhao2Login
        );
        RunsFactory::gradeRun($runDataContestantFromMaranhao2);

        // Create a user from Massachusetts, USA
        $contestantFromMassachusetts = UserFactory::createUser();
        $massachusettsLogin = self::login($contestantFromMassachusetts);

        UserController::apiUpdate(new Request([
            'auth_token' => $massachusettsLogin->auth_token,
            'country_id' => 'US',
            'state_id' => 'MA'
        ]));

        // create a run of one problem
        $runDataContestantFromMassachusetts = RunsFactory::createRunToProblem(
            $problemData[0],
            $contestantFromMassachusetts,
            $massachusettsLogin
        );
        RunsFactory::gradeRun($runDataContestantFromMassachusetts);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $maranhao1Login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $maranhao2Login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        // Call API
        $response = UserController::apiRankByProblemsSolved(new Request([
            'auth_token' => $massachusettsLogin->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
    }
    public function testUserRankingClassName() {
        // Create a user and sumbit a run with them
        $contestant = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        $this->refreshUserRank();

        // Call API
        $response = UserController::apiProfile(new Request([
            'username' => $contestant->username
        ]));

        $this->assertNotEquals($response['userinfo']['classname'], 'user-rank-unranked');
    }
}
