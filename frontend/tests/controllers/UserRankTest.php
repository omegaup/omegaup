<?php

class UserRankTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests apiRankByProblemsSolved
     */
    public function testFullRankByProblemSolved() {
        // Create a user and sumbit a run with him
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
        ['user' => $contestantPrivate, 'identity' => $identityPrivate] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['isPrivate' => true]
            )
        );
        // Create one problem and a submission by the private user
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runDataPrivate = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identityPrivate
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPrivate);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create a user and sumbit a run with him
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 0
        ]));
        $runDataPrivate = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataPrivate,
            $identity2
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPrivate);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Call API
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(new \OmegaUp\Request([
            'username' => $contestantIdentity->username
        ]));

        $this->assertEquals($response['name'], $contestantIdentity->name);
        $this->assertEquals($response['problems_solved'], 0);
        $this->assertEquals($response['rank'], 0);
    }

    /**
     * Testing filters via API and Smarty
     */
    public function testUserRankFiltered() {
        // Create a school
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        // Create a user with no country, state and school
        [
            'identity' => $identityWithNoCountry,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identityWithNoCountry);

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runDataContestantWithNoCountry = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identityWithNoCountry
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataContestantWithNoCountry);

        // User should not have filters
        $availableFilters = \OmegaUp\Controllers\User::getRankForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload']['availableFilters'];
        $this->assertArrayNotHasKey('country', $availableFilters);
        $this->assertArrayNotHasKey('state', $availableFilters);
        $this->assertArrayNotHasKey('school', $availableFilters);

        // Create a user with country, state and school
        [
            'identity' => $identityWithCountryAndSchool,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identityWithCountryAndSchool);

        $states = \OmegaUp\DAO\States::getByCountry('MX');
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_id' => $school['school']->school_id
        ]));

        // create runs
        $runDataContestant = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identityWithCountryAndSchool,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataContestant);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Getting available filters from identities
        $availableFilters = \OmegaUp\Controllers\User::getRankForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload']['availableFilters'];

        // Call API
        $this->assertArrayHasKey('country', $availableFilters);
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'filter' => 'country',
            ])
        );
        $this->assertCount(1, $response['rank']);
        $this->assertArrayHasKey('state', $availableFilters);
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'filter' => 'state',
            ])
        );
        $this->assertCount(1, $response['rank']);
        $this->assertArrayHasKey('school', $availableFilters);
        $response = \OmegaUp\Controllers\User::apiRankByProblemsSolved(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'filter' => 'school',
            ])
        );
        $this->assertCount(1, $response['rank']);
    }

    /**
     * Tests apiRankByProblemsSolved with state collision
     */
    public function testUserRankWithStateCollision() {
        // Create two problems
        $problemData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData[] = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create two users from Maranhao, Brasil
        [
            'user' => $contestantFromMaranhao1,
            'identity' => $identityFromMaranhao1
        ] = \OmegaUp\Test\Factories\User::createUser();
        $maranhao1Login = self::login($identityFromMaranhao1);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $maranhao1Login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]));

        // Create two runs of different problems
        $runDataContestantFromMaranhao1 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[0],
            $identityFromMaranhao1,
            $maranhao1Login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataContestantFromMaranhao1);
        $runDataContestantFromMaranhao1 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[1],
            $identityFromMaranhao1,
            $maranhao1Login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataContestantFromMaranhao1);

        [
            'user' => $contestantFromMaranhao2,
            'identity' => $identityFromMaranhao2
        ] = \OmegaUp\Test\Factories\User::createUser();
        $maranhao2Login = self::login($identityFromMaranhao2);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $maranhao2Login->auth_token,
            'country_id' => 'BR',
            'state_id' => 'MA'
        ]));

        // Create o run of one problem
        $runDataContestantFromMaranhao2 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[0],
            $identityFromMaranhao2,
            $maranhao2Login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataContestantFromMaranhao2);

        // Create a user from Massachusetts, USA
        [
            'user' => $contestantFromMassachusetts,
            'identity' => $identityFromMassachusetts
        ] = \OmegaUp\Test\Factories\User::createUser();
        $massachusettsLogin = self::login($identityFromMassachusetts);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $massachusettsLogin->auth_token,
            'country_id' => 'US',
            'state_id' => 'MA'
        ]));

        // create a run of one problem
        $runDataContestantFromMassachusetts = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[0],
            $identityFromMassachusetts,
            $massachusettsLogin
        );
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runDataContestantFromMassachusetts
        );

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Call API
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'username' => $identity->username
            ])
        );

        $this->assertNotEquals(
            $response['classname'],
            'user-rank-unranked'
        );
    }

    public function testUserRankWithForfeitedProblem() {
        ['user' => $firstPlaceUser, 'identity' => $firstPlaceIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($firstPlaceIdentity);
        $problems = [];
        $extraProblem = \OmegaUp\Test\Factories\Problem::createProblem();
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $firstPlaceIdentity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }
        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $extraProblem,
            $firstPlaceIdentity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLVED_PROBLEMS_PER_ALLOWED_SOLUTION; $i++
        ) {
            $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $identity,
                $login
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run);
        }

        \OmegaUp\Controllers\Problem::apiSolution(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $extraProblem['problem']->alias,
            'forfeit_problem' => true,
        ]));

        $run = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $extraProblem,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

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
