<?php

class UserRankTest extends OmegaupTestCase {
    /**
     * Tests apiRankByProblemsSolved
     */
    public function testFullRankByProblemSolved() {
        // Create a user and sumbit a run with him
        ['user' => $contestant, 'identity' => $contestantIdentity] = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request()
        );

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $contestantIdentity->username) {
                $found = true;
                $this->assertEquals($entry['name'], $contestantIdentity->name);
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
        ['user' => $contestantPrivate, 'identity' => $identityPrivate] = UserFactory::createUser(
            new UserParams(
                ['is_private' => true]
            )
        );
        // Create one problem and a submission by the private user
        $problemData = ProblemsFactory::createProblem();
        $runDataPrivate = RunsFactory::createRunToProblem(
            $problemData,
            $identityPrivate
        );
        RunsFactory::gradeRun($runDataPrivate);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request()
        );

        // Contestants should not appear in the rank as they're private.
        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $identityPrivate->username) {
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
        ['user' => $contestant, 'identity' => $contestantIdentity] = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        RunsFactory::gradeRun($runData);

        // Create a user and sumbit a run with him
        ['user' => $contestant2, 'identity' => $identity2] = UserFactory::createUser();
        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));
        $runDataPrivate = RunsFactory::createRunToProblem(
            $problemDataPrivate,
            $identity2
        );
        RunsFactory::gradeRun($runDataPrivate);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request()
        );

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] === $contestantIdentity->username) {
                $found = true;
                $this->assertEquals($entry['name'], $contestantIdentity->name);
                $this->assertEquals($entry['problems_solved'], 1);
                $this->assertEquals($entry['score'], 100);
            }

            if ($entry['username'] === $identity2->username) {
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
        ['user' => $contestant, 'identity' => $contestantIdentity] = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'username' => $contestantIdentity->username
        ]));

        $this->assertEquals($response['name'], $contestantIdentity->name);
        $this->assertEquals($response['problems_solved'], 1);
    }

    /**
     * Tests apiRankByProblemsSolved for a specific user with no runs
     */
    public function testUserRankByProblemsSolvedWith0Runs() {
        // Create a user with no runs
        ['user' => $contestant, 'identity' => $contestantIdentity] = UserFactory::createUser();

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'username' => $contestantIdentity->username
        ]));

        $this->assertEquals($response['name'], $contestantIdentity->name);
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
        ['user' => $contestantWithNoCountry, 'identity' => $identityWithNoCountry] = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runDataContestantWithNoCountry = RunsFactory::createRunToProblem(
            $problemData,
            $identityWithNoCountry
        );
        RunsFactory::gradeRun($runDataContestantWithNoCountry);

        // Create a user with country, state and school
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        $states = \OmegaUp\DAO\States::getByCountry('MX');
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        // create runs
        $runDataContestant = RunsFactory::createRunToProblem(
            $problemData,
            $contestant,
            $login
        );
        RunsFactory::gradeRun($runDataContestant);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => 'country'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
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
        [
            'user' => $contestantFromMaranhao1,
            'identity' => $identityFromMaranhao1
        ] = UserFactory::createUser();
        $maranhao1Login = self::login($identityFromMaranhao1);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
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

        [
            'user' => $contestantFromMaranhao2,
            'identity' => $identityFromMaranhao2
        ] = UserFactory::createUser();
        $maranhao2Login = self::login($identityFromMaranhao2);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
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
        [
            'user' => $contestantFromMassachusetts,
            'identity' => $identityFromMassachusetts
        ] = UserFactory::createUser();
        $massachusettsLogin = self::login($identityFromMassachusetts);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
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
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'auth_token' => $maranhao1Login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'auth_token' => $maranhao2Login->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(2, $response['rank']);

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'auth_token' => $massachusettsLogin->auth_token,
            'filter' => 'state'
        ]));
        $this->assertCount(1, $response['rank']);
    }

    public function testUserRankingClassName() {
        // Create a user and sumbit a run with them
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        $problemData = ProblemsFactory::createProblem();
        $runData = RunsFactory::createRunToProblem($problemData, $identity);
        RunsFactory::gradeRun($runData);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        // Call API
        $response = \OmegaUp\Controllers\User::apiProfile(new \OmegaUp\Request([
            'username' => $identity->username
        ]));

        $this->assertNotEquals(
            $response['userinfo']['classname'],
            'user-rank-unranked'
        );
    }

    public function testUserRankWithForfeitedProblem() {
        ['user' => $firstPlaceUser, 'identity' => $firstPlaceIdentity] = UserFactory::createUser();
        $login = self::login($firstPlaceIdentity);
        $problems = [];
        $extraProblem = ProblemsFactory::createProblem();
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problems[] = ProblemsFactory::createProblem();
            $run = RunsFactory::createRunToProblem(
                $problems[$i],
                $firstPlaceUser,
                $login
            );
            RunsFactory::gradeRun($run);
        }
        $run = RunsFactory::createRunToProblem(
            $extraProblem,
            $firstPlaceUser,
            $login
        );
        RunsFactory::gradeRun($run);

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $run = RunsFactory::createRunToProblem(
                $problems[$i],
                $user,
                $login
            );
            RunsFactory::gradeRun($run);
        }

        \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $extraProblem['problem']->alias,
            'forfeit_problem' => true,
        ]));

        $run = RunsFactory::createRunToProblem($extraProblem, $user, $login);
        RunsFactory::gradeRun($run);

        // Refresh Rank
        Utils::RunUpdateUserRank();

        $firstPlaceUserRank = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'username' => $firstPlaceIdentity->username
        ]));
        $userRank = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'username' => $identity->username
        ]));

        $this->assertTrue($firstPlaceUserRank['rank'] < $userRank['rank']);
        $this->assertEquals(sizeof($problems), $userRank['problems_solved']);
        $this->assertEquals(
            sizeof($problems) + 1 /* extraProblem */,
            $firstPlaceUserRank['problems_solved']
        );
    }
}
