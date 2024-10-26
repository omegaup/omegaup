<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Description of ContestUsersTest
 */

class ContestUsersTest extends \OmegaUp\Test\ControllerTestCase {
    public function testContestUsersValid() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create 10 users
        $n = 10;
        $users = [];
        $identities = [];

        ['user' => $users[0], 'identity' => $identities[0]] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'username' => 'test_contest_user_0',
        ]));
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identities[0]
        );

        ['user' => $users[1], 'identity' => $identities[1]] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'username' => 'test_contest_user_1',
        ]));
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identities[1]
        );
        for ($i = 2; $i < $n; $i++) {
            ['user' => $users[$i], 'identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identities[$i]
            );
        }

        // Create a n+1 user who will just join to the contest without being
        // added via API. For public contests, by entering to the contest, the user should be in
        // the list of contest's users.
        ['user' => $nonRegisteredUser, 'identity' => $nonRegisteredIdentity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $nonRegisteredIdentity
        );

        // Log in with the admin of the contest
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUsers($r);

        // Check that we have n+1 users
        $this->assertCount($n + 1, $response['users']);

        // Call search API
        $response = \OmegaUp\Controllers\Contest::apiSearchUsers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'query' => 'test_contest_user_'
            ])
        )['results'];
        $this->assertCount(2, $response);
    }

    public function testContestActivityReport() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $identity
        );

        $userLogin = self::login($identity);
        \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Call API
        $directorLogin = self::login($contestData['director']);
        $response = \OmegaUp\Controllers\Contest::apiActivityReport(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Check that we have entries in the log.
        $this->assertSame(1, count($response['events']));
        $this->assertSame(
            $identity->username,
            $response['events'][0]['username']
        );
        $this->assertSame(0, $response['events'][0]['ip']);
        $this->assertSame('open', $response['events'][0]['event']['name']);
    }

    public function testFutureContestIntro() {
        // Get a future public contest
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 60 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 120 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
                'startTime' => $startTime,
                'finishTime' => $finishTime,
            ])
        );

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        // Should show the contest intro when it is public
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );

        // Add user to our contest
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity
        );

        // Should show the contest intro when the user is registered in the contest
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );

        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame(
            $contestDetails['contest']['start_time']->time,
            $startTime->time
        );

        // Get a future private contest
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 60 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() + 120 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
                'startTime' => $startTime,
                'finishTime' => $finishTime,
                'admissionMode' => 'private',
            ])
        );

        // Shouldn't show the contest intro when it is private
        try {
            $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            )['templateProperties']['payload'];

            $this->fail(
                'User should not have access to a future contest when it is private'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        // Add user to our contest
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity
        );

        // Should show the contest intro when the user is registered in the contest
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );
    }

    public function testContestDataForTypeScript() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );
        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our contest
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity
        );

        $userLogin = self::login($identity);
        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['privacyStatement']['statementType'],
            'share_user_information' => 1,
        ]));

        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        // adminPayload object should not exist
        $this->assertArrayNotHasKey('adminPayload', $contestDetails);
    }

    public function testContestJoinWithUnregisteredUserWithoutTheFlag() {
        // Get a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        // Create two users
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $unregisteredIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add the first user to our contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $userLogin = self::login($unregisteredIdentity);
        try {
            \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testContestJoinWithUnregisteredUserWithTheFlag() {
        // Get a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        // Create two users
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $unregisteredIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add the first user to our contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $userLogin = self::login($unregisteredIdentity);

        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'start_fresh' => true,
            ])
        )['templateProperties']['payload'];

        $this->assertTrue(
            $contestDetails['shouldShowModalToLoginWithRegisteredIdentity']
        );
    }

    public function testContestParticipantsReport() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );
        $identity = [];
        $numberOfStudents = 3;
        foreach (range(0, $numberOfStudents - 1) as $studentIndex) {
            // Create users
            [
                'identity' => $identity[$studentIndex],
            ] = \OmegaUp\Test\Factories\User::createUser();

            // Add users to our private contest
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identity[$studentIndex]
            );
        }

        $userLogin = self::login($identity[0]);
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity[0],
                $contestData['contest']
            )
        );
        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame(
            $contestData['director']->username,
            $contestDetails['contest']['director']
        );
        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['privacyStatement']['statementType'],
            'share_user_information' => 1,
        ]));

        // Call API
        $directorLogin = self::login($contestData['director']);

        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token
        ]);

        $response = \OmegaUp\Controllers\Contest::apiContestants($r);

        // There are three participants in the current contest
        $this->assertSame(3, count($response['contestants']));

        // But only one participant has accepted share user information
        $this->assertSame(1, self::numberOfUsersSharingBasicInformation(
            $response['contestants']
        ));

        $userLogin = self::login($identity[1]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['privacyStatement']['statementType'],
            'share_user_information' => 0,
        ]));

        $response = \OmegaUp\Controllers\Contest::apiContestants($r);

        // The number of participants sharing their information still remains the same
        $this->assertSame(1, self::numberOfUsersSharingBasicInformation(
            $response['contestants']
        ));

        $userLogin = self::login($identity[2]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $userLogin->auth_token,
            'privacy_git_object_id' =>
                $contestDetails['privacyStatement']['gitObjectId'],
            'statement_type' =>
                $contestDetails['privacyStatement']['statementType'],
            'share_user_information' => 1,
        ]));

        $response = \OmegaUp\Controllers\Contest::apiContestants($r);

        // Now there are two participants sharing their information
        $this->assertSame(2, self::numberOfUsersSharingBasicInformation(
            $response['contestants']
        ));
    }

    public function testContestCanBeSeenByUnloggedUsers() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                null,
                $contestData['contest']
            )
        );
    }

    public function testNeedsBasicInformation() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
            'basicInformation' => 'true',
        ]));

        // Create and login a user to view the contest
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        // Contest intro can be shown by the user
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );

        // Contest needs basic information for the user
        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($contestDetails['needsBasicInformation']);
    }

    public function testBasicContestPractice() {
        // Get a contest in the past
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 120 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $startTime,
                'finishTime' => $finishTime,
                'admissionMode' => 'private',
            ])
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $userLogin = self::login($identity);
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );

        $contestDetails = \OmegaUp\Controllers\Contest::getContestPracticeDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame(
            $contestData['director']->username,
            $contestDetails['contest']['director']
        );
    }

    public function testContestPracticeForNonRegisteredUsers() {
        // Get a contest in the past
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 120 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $startTime,
                'finishTime' => $finishTime,
                'admissionMode' => 'public',
            ])
        );

        // Non-registered users can access public contests in practice mode
        [
            'identity' => $nonRegisteredIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($nonRegisteredIdentity);
        $contestDetails = \OmegaUp\Controllers\Contest::getContestPracticeDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame(
            $contestData['director']->username,
            $contestDetails['contest']['director']
        );
    }

    public function testProblemsInContestPracticeForNonRegisteredUsers() {
        // Get a contest in the past
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 120 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $startTime,
                'finishTime' => $finishTime,
                'admissionMode' => 'public',
            ])
        );

        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );
        // One more problem, but in this case, it is private
        $login = self::login($contestData['director']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ]),
            $login
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);
        $contestDetails = \OmegaUp\Controllers\Contest::getContestPracticeDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        // Users should be able to see all the problems
        foreach ($contestDetails['problems'] as $problem) {
            $problemDetails = \OmegaUp\Controllers\Problem::apiDetails(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'problem_alias' => $problem['alias'],
                    'prevent_problemset_open' => false,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );
            $this->assertSame($problemDetails['alias'], $problem['alias']);
        }

        // But they are not included in the original contest scoreboard
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'problemset_id' => $contestData['contest']->problemset_id,
            ])
        );
        $this->assertEmpty($response['ranking']);

        // Users can create runs
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity,
            inPracticeMode: true
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $userLogin = self::login($identity);
        $problemDetails = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'prevent_problemset_open' => false,
                'contest_alias' => $contestData['request']['alias'],
            ])
        );

        $this->assertCount(1, $problemDetails['runs']);
    }

    public function testPrivateContestPracticeForNonRegisteredUsers() {
        // Get a contest in the past
        $startTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 120 * 60);
        $finishTime =  new \OmegaUp\Timestamp(\OmegaUp\Time::get() - 60 * 60);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $startTime,
                'finishTime' => $finishTime,
                'admissionMode' => 'private',
            ])
        );

        // Non-registered users can't access private contests, even in practice
        // mode
        [
            'identity' => $nonRegisteredIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($nonRegisteredIdentity);
        try {
            \OmegaUp\Controllers\Contest::getContestPracticeDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );
            $this->fail(
                'User should not have access to contest in practice mode when it is private'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testContestPracticeWhenOriginalContestHasNotEnded() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $userLogin = self::login($identity);
        $this->assertTrue(
            \OmegaUp\Controllers\Contest::shouldShowIntro(
                $identity,
                $contestData['contest']
            )
        );
        try {
            \OmegaUp\Controllers\Contest::getContestPracticeDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('originalContestHasNotEnded', $e->getMessage());
        }
    }

    /**
     * A PHPUnit data provider for the test of the next registered contest for a user.
     *
     * @return list<array{0: list<\OmegaUp\Timestamp>, 1: list<\OmegaUp\Timestamp>, 2: null|int}>
     */
    public function NextRegisteredContestForUserProvider(): array {
        $currentTime = \OmegaUp\Time::get();
        $timePast1 =  new \OmegaUp\Timestamp($currentTime - 180 * 60);
        $timePast2 =  new \OmegaUp\Timestamp($currentTime - 120 * 60);
        $timePast3 =  new \OmegaUp\Timestamp($currentTime - 60 * 60);
        $timeFuture1 =  new \OmegaUp\Timestamp($currentTime + 60 * 60);
        $timeFuture2 =  new \OmegaUp\Timestamp($currentTime + 120 * 60);
        return [
            // Get the next registered contest for a user who is not registered in any contest
            [[], [], null],
            // Although both contests started at the same time, the second one is scheduled
            // to finish sooner, making it the next registered contest.
            [[$timePast1, $timePast1], [$timeFuture2, $timeFuture1], 1],
            // The first contest started first
            [[$timePast1, $timeFuture1], [$timeFuture1, $timeFuture2], 0],
            // The user doesn't have a next registered contest because the two contests already finished
            [[$timePast1, $timePast2], [$timePast3, $timePast3], null],
            // The third contest is the only current or upcoming contest
            [[$timePast1, $timePast2, $timeFuture1], [$timePast3, $timePast3, $timeFuture2], 2],
        ];
    }

    /**
     * @dataProvider NextRegisteredContestForUserProvider
     *
     * @param list<\OmegaUp\Timestamp> $startTimes
     * @param list<\OmegaUp\Timestamp> $finishTimes
     * @param null|int $nextExpectedRegisteredContestIndex
     */
    public function testNextRegisteredContestForUser(
        $startTimes,
        $finishTimes,
        $nextExpectedRegisteredContestIndex
    ) {
        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create contests and add user to them
        $n = count($startTimes);
        $contests = [];
        for ($i = 0; $i < $n; $i++) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'title' => 'Contest_' . $i,
                    'startTime' => $startTimes[$i],
                    'finishTime' => $finishTimes[$i],
                ])
            );
            \OmegaUp\Test\Factories\Contest::addUser(
                $contests[$i],
                $identity
            );
        }

        // Get the next registered contest for the user
        $nextRegisteredContestForUser = \OmegaUp\DAO\Contests::getNextRegisteredContestForUser(
            $identity
        );

        // Check that the next registered contest for the user is correct
        if (is_null($nextExpectedRegisteredContestIndex)) {
            $this->assertNull($nextRegisteredContestForUser);
        } else {
            $this->assertSame(
                $contests[$nextExpectedRegisteredContestIndex]['contest']->title,
                $nextRegisteredContestForUser['title']
            );
        }
    }

    private static function numberOfUsersSharingBasicInformation(
        array $contestants
    ): int {
        $numberOfContestants = 0;
        foreach ($contestants as $contestant) {
            if ($contestant['email']) {
                $numberOfContestants++;
            }
        }
        return $numberOfContestants;
    }
}
