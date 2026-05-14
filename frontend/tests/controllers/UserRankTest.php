<?php

class UserRankTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests getRankByProblemsSolved
     */
    public function testFullRankByProblemSolved() {
        // Create a user and submit a run with him
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Call function
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            null,
            '',
            1,
            100
        );

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] == $contestantIdentity->username) {
                $found = true;
                $this->assertSame(
                    $entry['name'],
                    $contestantIdentity->name
                );
                $this->assertSame($entry['problems_solved'], 1);
                $this->assertSame($entry['score'], 100.0);
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Tests refreshUserRank not displaying private profiles
     */
    public function testPrivateUserInRanking() {
        // Create a private user
        ['identity' => $identityPrivate] = \OmegaUp\Test\Factories\User::createUser(
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

        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            null,
            '',
            1,
            100
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
     * Tests getRankByProblemsSolved
     */
    public function testFullRankByProblemSolvedNoPrivateProblems() {
        // Create a user and submit a run with him
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create a user and submit a run with him
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
        ]));
        $runDataPrivate = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataPrivate,
            $identity2
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPrivate);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Call API
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            null,
            '',
            1,
            100
        );

        $found = false;
        foreach ($response['rank'] as $entry) {
            if ($entry['username'] === $contestantIdentity->username) {
                $found = true;
                $this->assertSame(
                    $entry['name'],
                    $contestantIdentity->name
                );
                $this->assertSame($entry['problems_solved'], 1);
                $this->assertSame($entry['score'], 100.0);
            }

            if ($entry['username'] === $identity2->username) {
                $this->fail(
                    'User with private problem solved showed in rank.'
                );
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Tests getUserRankInfo
     */
    public function testGetUserRankInfo() {
        // Create a user and submit a run with him/her
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        $response = \OmegaUp\Controllers\User::getUserRankInfo(
            $contestantIdentity
        );
        $this->assertSame($response['name'], $contestantIdentity->name);
        $this->assertSame($response['problems_solved'], 1);
    }

    /**
     * Tests getUserRankInfo for a specific user with no runs
     */
    public function testUserRankByProblemsSolvedWith0Runs() {
        // Create a user with no runs
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        $response = \OmegaUp\Controllers\User::getUserRankInfo(
            $contestantIdentity
        );

        $this->assertSame($response['name'], $contestantIdentity->name);
        $this->assertSame($response['problems_solved'], 0);
        $this->assertSame($response['rank'], 0);
    }

    public function testUserRankFilterPager() {
        $mappingSchoolUser = [
            'school_1' => ['studentsCount' => 3],
            'school_2' => ['studentsCount' => 6],
            'school_3' => ['studentsCount' => 2],
        ];

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        foreach ($mappingSchoolUser as $name => $studentsSchool) {
            $school = \OmegaUp\Test\Factories\Schools::createSchool($name);
            foreach (range(0, $studentsSchool['studentsCount'] - 1) as $id) {
                ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
                    new \OmegaUp\Test\Factories\UserParams(
                        ['username' => "user_{$name}_{$id}"]
                    )
                );
                $login = self::login($identity);

                \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'school_id' => $school['school']->school_id,
                ]));

                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $identity
                );

                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
        }

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school['school']->school_id,
        ]));

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        \OmegaUp\Test\Utils::runUpdateRanks();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::getRankForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'length' => 5,
            ])
        )['templateProperties']['payload'];

        $this->assertCount(5, $response['pagerItems']);

        // When school filter is enabled, the pager should show only 1 page
        // plus navigation buttons (3 in total)
        $response = \OmegaUp\Controllers\User::getRankForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'length' => 5,
                'filter' => 'school',
            ])
        )['templateProperties']['payload'];
        $this->assertCount(3, $response['pagerItems']);
    }

    /**
     * Testing filters via API and TypeScript.
     */
    public function testUserRankFiltered() {
        // Create a school
        $school = \OmegaUp\Test\Factories\Schools::createSchool();
        // Create a user with no country, state and school
        [
            'identity' => $identityWithNoCountry,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runDataContestantWithNoCountry = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identityWithNoCountry
        );
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runDataContestantWithNoCountry
        );

        // User should not have filters
        $login = self::login($identityWithNoCountry);
        $availableFilters = \OmegaUp\Controllers\User::getRankForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['availableFilters'];
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
        $availableFilters = \OmegaUp\Controllers\User::getRankForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['availableFilters'];

        // Call API
        $identity = \OmegaUp\DAO\Identities::getByPK(
            $identityWithCountryAndSchool->identity_id
        );
        $this->assertArrayHasKey('country', $availableFilters);
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $identity,
            'country',
            1,
            100
        );
        $this->assertCount(1, $response['rank']);
        $this->assertArrayHasKey('state', $availableFilters);
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $identity,
            'state',
            1,
            100
        );
        $this->assertCount(1, $response['rank']);
        $this->assertArrayHasKey('school', $availableFilters);
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $identity,
            'school',
            1,
            100
        );
        $this->assertCount(1, $response['rank']);
    }

    /**
     * Tests getRankByProblemsSolved with state collision
     */
    public function testUserRankWithStateCollision() {
        // Create two problems
        $problemData = [
            \OmegaUp\Test\Factories\Problem::createProblem(),
            \OmegaUp\Test\Factories\Problem::createProblem(),
        ];

        // Create two users from Maranhao, Brasil
        [
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
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runDataContestantFromMaranhao1
        );
        $runDataContestantFromMaranhao1 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData[1],
            $identityFromMaranhao1,
            $maranhao1Login
        );
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runDataContestantFromMaranhao1
        );

        [
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
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runDataContestantFromMaranhao2
        );

        // Create a user from Massachusetts, USA
        [
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
        $maranhao1UpdatedIdentity = \OmegaUp\DAO\Identities::getByPK(
            $identityFromMaranhao1->identity_id
        );
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $maranhao1UpdatedIdentity,
            'state',
            1,
            100
        );
        $this->assertCount(2, $response['rank']);

        // Call API
        $maranhao2UpdatedIdentity = \OmegaUp\DAO\Identities::getByPK(
            $identityFromMaranhao2->identity_id
        );
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $maranhao2UpdatedIdentity,
            'state',
            1,
            100
        );
        $this->assertCount(2, $response['rank']);

        // Call API
        $massachusettsUpdatedIdentity = \OmegaUp\DAO\Identities::getByPK(
            $identityFromMassachusetts->identity_id
        );
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            $massachusettsUpdatedIdentity,
            'state',
            1,
            100
        );
        $this->assertCount(1, $response['rank']);
    }

    public function testUserRankingClassName() {
        // Create a user and submit a run with them
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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
        ['identity' => $firstPlaceIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($firstPlaceIdentity);
        $problems = [];
        $extraProblem = \OmegaUp\Test\Factories\Problem::createProblem();
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY; $i++
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

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        for (
            $i = 0; $i < \OmegaUp\Controllers\ProblemForfeited::SOLUTIONS_ALLOWED_TO_SEE_PER_DAY; $i++
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

        $firstPlaceUserRank = \OmegaUp\Controllers\User::getUserRankInfo(
            $firstPlaceIdentity
        );
        $userRank = \OmegaUp\Controllers\User::getUserRankInfo(
            $identity
        );

        $this->assertTrue($firstPlaceUserRank['rank'] < $userRank['rank']);
        $this->assertSame(sizeof($problems), $userRank['problems_solved']);
        $this->assertSame(
            sizeof($problems) + 1 /* extraProblem */,
            $firstPlaceUserRank['problems_solved']
        );
    }

    /**
     * Creates 6 users and 4 problems
     * - user0 is author of problem0 and problem1
     * - user1 is author of problem2 and problem3
     * - The other users just solve the problems
     * - Problems of user1 receive less quality score than problems of user0
     * - update_ranks is executed, some users receive their user_score (as usual)
     * - user0 receive only an author_score
     * - user1 receive both: author_score and user_score
     */
    public function testAuthorsRank() {
        $users = [];
        $identities = [];
        $problems = [];

        ['user' => $users[0], 'identity' => $identities[0]] = \OmegaUp\Test\Factories\User::createUser();
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identities[0]
        );
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identities[0]
        );

        ['user' => $users[1], 'identity' => $identities[1]] = \OmegaUp\Test\Factories\User::createUser();
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identities[1]
        );
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $identities[1]
        );

        // user1 qualifies also problems of user0 (for receiving a user score)
        for ($i = 0; $i < 2; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problems[$i],
                $identities[1]
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.5, 'AC');
            \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                $identities[1],
                $problems[$i]['request']['problem_alias'],
                1, /* difficulty */
                4, /* quality */
                [],
                false
            );
        }

        // The other users just solve problems of user0 and user1
        for ($i = 2; $i < 7; $i++) {
            ['user' => $users[$i], 'identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();

            for ($j = 0; $j < 4; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.5, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $identities[$i],
                    $problems[$j]['request']['problem_alias'],
                    1, /* difficulty */
                    3, /* quality */
                    [],
                    false
                );
            }
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();

        $problem0 = \Omegaup\DAO\Problems::getByPK(
            $problems[0]['problem']->problem_id
        );
        $problem1 = \Omegaup\DAO\Problems::getByPK(
            $problems[1]['problem']->problem_id
        );
        $problem2 = \Omegaup\DAO\Problems::getByPK(
            $problems[2]['problem']->problem_id
        );
        $problem3 = \Omegaup\DAO\Problems::getByPK(
            $problems[3]['problem']->problem_id
        );

        \OmegaUp\Test\Utils::runUpdateRanks();

        $user2 = \OmegaUp\DAO\UserRank::getByPK($users[2]->user_id);
        $this->assertNull($user2->author_ranking);

        $results = \OmegaUp\Controllers\User::getAuthorsRank(
            1,
            100
        )['ranking'];

        $this->assertSame(
            $problem0->quality + $problem1->quality,
            $results[0]['author_score']
        );
        $this->assertSame(
            $problem2->quality + $problem3->quality,
            $results[1]['author_score']
        );
        $this->assertGreaterThan(
            $results[1]['author_score'],
            $results[0]['author_score']
        );
        $this->assertGreaterThan(
            $results[0]['author_ranking'],
            $results[1]['author_ranking']
        );
    }

    public function testUserWithIdentitiesRank() {
        // Adding some problems
        $problemsData = [];
        foreach (range(0, 2) as $_) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Identity creator group member will upload csv file
        [
            'identity' => $creator,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creator,
            login: $creatorLogin
        );
        $password = \OmegaUp\Test\Utils::createRandomString();

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias,
                $password
            ),
            'group_alias' => $group['group']->alias,
        ]));

        // Getting all identity members associated to the group
        $membersResponse = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $associatedIdentity = $membersResponse['identities'][0];
        $unassociatedIdentity = $membersResponse['identities'][1];

        $usersRankExpected = [];

        // Create the user to associate with an identity
        ['identity' => $contestant] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($contestant);

        $usersRankExpected[0] = [
            'username' => $contestant->username,
            'problems_solved' => 0,
        ];

        // Associate first identity to contestant
        $response = \OmegaUp\Controllers\User::apiAssociateIdentity(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $associatedIdentity['username'],
                'password' => $password,
            ])
        );

        // User submits a run for a problem 0
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $contestant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $usersRankExpected[0]['problems_solved'] += 1;

        // Associated identity submits a run for problem 1
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $associatedIdentity['username']
        );
        $identity->password = $password;
        $login = self::login($identity);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[1],
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $usersRankExpected[0]['problems_solved'] += 1;

        // Unassociated identity submits a run for problem 2
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $unassociatedIdentity['username']
        );
        $identity->password = $password;
        $login = self::login($identity);

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[2],
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // A new user, with no associated identities, submits a run
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($user);

        // User submits a run for a problem 0
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemsData[0],
            $user
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $usersRankExpected[1] = [
            'username' => $user->username,
            'problems_solved' => 1,
        ];

        // Refresh Rank
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Call function
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            loggedIdentity: null,
            filteredBy: '',
            offset: 1,
            rowCount: 100,
        );

        // Unassociated identities do not appear in user rank report
        $this->assertSame($response['total'], count($usersRankExpected));
        foreach ($response['rank'] as $i => $entry) {
            $this->assertSame(
                $entry['username'],
                $usersRankExpected[$i]['username']
            );
            $this->assertSame(
                $entry['problems_solved'],
                $usersRankExpected[$i]['problems_solved']
            );
            $this->assertSame($entry['ranking'], $i + 1);
        }
    }

    /**
     * Test that site-admins are excluded from user rankings
     * even if they solve more problems than regular users.
     */
    public function testSiteAdminExcludedFromUserRanking() {
        // Create a regular user who solves few problems
        ['identity' => $regularIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a site-admin user who solves many problems
        ['identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Admin solves 10 problems
        for ($i = 0; $i < 10; $i++) {
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $adminIdentity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        // Regular user solves only 2 problems
        for ($i = 0; $i < 2; $i++) {
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $regularIdentity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        // Update ranks
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Get user rankings
        $response = \OmegaUp\Controllers\User::getRankByProblemsSolved(
            null,
            '',
            1,
            100
        );

        // Verify regular user appears in ranking but admin doesn't
        $rankedUsers = array_column($response['rank'], 'username');
        $this->assertContains(
            $regularIdentity->username,
            $rankedUsers,
            'Regular user should appear in user ranking'
        );
        $this->assertNotContains(
            $adminIdentity->username,
            $rankedUsers,
            'Site-admin should not appear in user ranking'
        );
    }

    /**
     * Test that site-admins are excluded from author rankings
     * even if they have more quality problems than regular authors.
     */
    public function testSiteAdminExcludedFromAuthorRanking() {
        // Create a regular author who creates few quality problems
        ['identity' => $regularAuthor] = \OmegaUp\Test\Factories\User::createUser();

        // Create a site-admin author who creates many quality problems
        ['identity' => $adminAuthor] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Admin creates 5 quality problems
        $adminProblems = [];
        for ($i = 0; $i < 5; $i++) {
            $adminProblems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $adminAuthor
            );
        }

        // Regular author creates 2 quality problems
        $regularProblems = [];
        for ($i = 0; $i < 2; $i++) {
            $regularProblems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $regularAuthor
            );
        }

        // Get some users to solve and rate these problems
        for ($i = 0; $i < 5; $i++) {
            ['identity' => $solver] = \OmegaUp\Test\Factories\User::createUser();

            // Solve and rate admin's problems
            foreach ($adminProblems as $problemData) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $solver
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.0, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $solver,
                    $problemData['request']['problem_alias'],
                    1, /* difficulty */
                    4, /* quality */
                    [],
                    false
                );
            }

            // Solve and rate regular author's problems
            foreach ($regularProblems as $problemData) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData,
                    $solver
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.0, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $solver,
                    $problemData['request']['problem_alias'],
                    1, /* difficulty */
                    4, /* quality */
                    [],
                    false
                );
            }
        }

        // Aggregate feedback and update ranks
        \OmegaUp\Test\Utils::runAggregateFeedback();
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Get author rankings
        $authorsRank = \OmegaUp\Controllers\User::getAuthorsRank(
            1,
            100
        )['ranking'];

        // Verify regular author appears in ranking but admin doesn't
        $rankedAuthors = array_column($authorsRank, 'username');
        $this->assertContains(
            $regularAuthor->username,
            $rankedAuthors,
            'Regular author should appear in author ranking'
        );
        $this->assertNotContains(
            $adminAuthor->username,
            $rankedAuthors,
            'Site-admin should not appear in author ranking'
        );
    }
}
