<?php
/**
 * Description of ListContests
 */

class ContestListTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Check request and response durations match.
     *
     * @param array $response
     * @param array $contestData
     */
    private function assertDurationIsCorrect($response, $contestData) {
        $contestFromResponse = null;

        foreach ($response['results'] as $entry) {
            if ($entry['title'] === $contestData['request']['title']) {
                $contestFromResponse = $entry;
                break;
            }
        }

        $this->assertNotNull($contestFromResponse);

        $durationFromResponse = (
            $contestFromResponse['finish_time']->time -
            $contestFromResponse['start_time']->time
        );
        $durationFromRequest = (
            $contestData['request']['finish_time'] -
            $contestData['request']['start_time']
        );

        $this->assertSame($durationFromRequest, $durationFromResponse);
    }

    /**
     * Basic test. Check that most recent contest is at the top of the list
     */
    public function testLatestPublicContest() {
        // Create new PUBLIC contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Log as a random contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 50
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);

        // Assert our contest is there.
        $this->assertArrayContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    /**
     * Basic test. Check that most recent contest is at the top of the list
     */
    public function testLatestPublicContestNotLoggedIn() {
        // Create new PUBLIC contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'page_size' => 50,
        ]));
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'page_size' => 50,
            'query' => 'thiscontestdoesnotexist',
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    public function testPrivateContestForInvitedUser() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Get a user for our scenario
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'page_size' => 50,
            ])
        );
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);

        $response = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'query' => 'thiscontestdoesnotexist',
            ])
        );
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    public function testPrivateContestForInvitedGroup() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'private',
            ])
        );

        // Get a user for our scenario
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        {
            $login = self::login($contestData['director']);
            $groupData = \OmegaUp\Test\Factories\Groups::createGroup(
                login: $login,
            );
            \OmegaUp\Test\Factories\Groups::addUserToGroup(
                $groupData,
                $identity,
                $login
            );
            \OmegaUp\Controllers\Contest::apiAddGroup(
                new \OmegaUp\Request([
                    'contest_alias' => strval($contestData['request']['alias']),
                    'group' => $groupData['group']->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
        }

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'page_size' => 50,
            ])
        );
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);

        $response = \OmegaUp\Controllers\Contest::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'query' => 'thiscontestdoesnotexist',
            ])
        );
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    public function testPrivateContestForNonInvitedUser() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    public function testPrivateContestForSystemAdmin() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 100,
        ]));
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 100,
            'query' => 'thiscontestdoesnotexist',
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    /**
     *
     */
    public function testPrivateContestForContestAdmin() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $identity);

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 50,
        ]));
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'query' => 'thiscontestdoesnotexist',
            'page_size' => 50,
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    /**
     * An added admin group should see those contests as well
     */
    public function testPrivateContestForContestGroupAdmin() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $title = $contestData['request']['title'];

        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity1);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 50,
        ]);

        // Assert our contest is not there.
        $response = \OmegaUp\Controllers\Contest::apiList($r);
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $title
        );
        $response = \OmegaUp\Controllers\Contest::apiAdminList($r);
        $this->assertArrayNotContainsInKey(
            $response['contests'],
            'title',
            $title
        );

        // Add user to our private contest
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $contestData['director']
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $identity1);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $identity2);
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestData,
            $group['group']
        );

        // Assert our contest is there
        $response = \OmegaUp\Controllers\Contest::apiList($r);

        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $title
        );
        $response = \OmegaUp\Controllers\Contest::apiAdminList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['contests'],
            'title',
            $title
        );
    }

    /**
     * Authors with admin groups should only see each contest once.
     */
    public function testAuthorOnlySeesContestsOnce() {
        // Create new private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $author = $contestData['director'];
        $title = $contestData['request']['title'];

        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        $group = \OmegaUp\Test\Factories\Groups::createGroup($author);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $identity1);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $identity2);
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestData,
            $group['group']
        );

        $login = self::login($author);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 50,
        ]);

        // Assert our contest is there, but just once.
        $response = \OmegaUp\Controllers\Contest::apiList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $title
        );
        $response = \OmegaUp\Controllers\Contest::apiAdminList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['contests'],
            'title',
            $title
        );
    }

    /**
     * Test that contests with recommended flag show first in list.
     */
    public function testRecommendedShowsOnTop() {
        // Create 2 contests, with the not-recommended.finish_time > recommended.finish_time
        $recommendedContestData = \OmegaUp\Test\Factories\Contest::createContest();
        $notRecommendedContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams(
            [
                'finishTime' => $recommendedContestData['request']['finish_time'] + 1,
            ]
        ));

        // Get a user for our scenario
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Turn recommended ON
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($identity);
        \OmegaUp\Controllers\Contest::apiSetRecommended(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $recommendedContestData['request']['alias'],
            'value' => 1,
        ]));
        unset($login);

        // Get list of contests
        $login = self::login($contestantIdentity);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Check that recommended contest is earlier in list than not-recommended
        $recommendedPosition = 0;
        $notRecommendedPosition = 0;

        foreach ($response['results'] as $contest) {
            if ($contest['title'] == $recommendedContestData['request']['title']) {
                break;
            }

            $recommendedPosition++;
        }

        foreach ($response['results'] as $contest) {
            if ($contest['title'] == $notRecommendedContestData['request']['title']) {
                break;
            }

            $notRecommendedPosition++;
        }

        $this->assertTrue($recommendedPosition < $notRecommendedPosition);
    }

    public function testExtenseContestsList() {
        // Create a contest
        \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user for our scenario
        [
            'identity' => $contestantIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Get list of contests
        $login = self::login($contestantIdentity);
        try {
            \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'page_size' => 1000,
            ]));
            $this->fail('Should have failed because of the page size limit');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNumberTooLarge', $e->getMessage());
        }
    }

    /**
     * Test to set recommended value in two contests.
     */
    public function testRecommendedContestsList() {
        // Create 2 contests not-recommended
        $recommendedContest = [];
        $recommendedContest[0] = \OmegaUp\Test\Factories\Contest::createContest();
        $recommendedContest[1] = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user for our scenario
        [
            'identity' => $contestantIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Get list of contests
        $login = self::login($contestantIdentity);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 100,
        ]));

        // Assert that two contests are not recommended
        for ($i = 0; $i < 2; $i++) {
            $contest = $recommendedContest[$i];
            $contest = $this->findByPredicate(
                $response['results'],
                fn ($value) => $value['alias'] == $contest['contest']->alias
            );

            $this->assertFalse($contest['recommended']);
        }

        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($identity);
        for ($i = 0; $i < 2; $i++) {
            \OmegaUp\Controllers\Contest::apiSetRecommended(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $recommendedContest[$i]['request']['alias'],
                'value' => 1,
            ]));
        }
        unset($login);

        // Get list of contests
        $login = self::login($contestantIdentity);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 100,
        ]));
        unset($login);

        // Assert that two contests are already recommended
        for ($i = 0; $i < 2; $i++) {
            $contest = $recommendedContest[$i];
            $contest = $this->findByPredicate(
                $response['results'],
                fn ($value) => $value['alias'] == $contest['contest']->alias,
            );

            $this->assertTrue($contest['recommended']);
        }
    }

    /**
     * Basic test. Check that only the first contest is on the list
     */
    public function testShowOnlyCurrentContests() {
        $r = new \OmegaUp\Request();

        // Create 2 contests, the second one will occur in to the future.
        $currentContestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $futureContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams(
            [
                'admissionMode' => 'private',
                'finishTime' => ($currentContestData['request']['start_time'] + (60 * 60 * 49)),
                'startTime' => ($currentContestData['request']['start_time'] + (60 * 60 * 48)),
            ]
        ));

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contests
        \OmegaUp\Test\Factories\Contest::addUser(
            $currentContestData,
            $identity
        );
        \OmegaUp\Test\Factories\Contest::addUser($futureContestData, $identity);

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'active' => \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE,
        ]);

        $response = \OmegaUp\Controllers\Contest::apiList($r);

        $this->assertArrayContainsInKey(
            $response['results'],
            'contest_id',
            $currentContestData['contest']->contest_id
        );
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'contest_id',
            $futureContestData['contest']->contest_id
        );
    }

    public function testPrivateContestListForInvitedUser() {
        // Create three new private contests, and one public contest
        $contestData = [];
        $contestData[] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );
        for ($i = 1; $i < 4; $i++) {
            $contestData[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'private',
                ])
            );
        }

        // Get a user for our scenario
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to two private contest
        $numberOfPrivateContests = 2;
        for ($i = 0; $i < $numberOfPrivateContests; $i++) {
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData[$i],
                $identity
            );
        }

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiListParticipating(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertSame(
            $numberOfPrivateContests,
            count($response['contests'])
        );
    }

    public function testPrivateContestListForInvitedGroup() {
        // Create three new private contests, and one public contest
        $contestData = [];
        $contestData[] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );
        for ($i = 1; $i < 4; $i++) {
            $contestData[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'private',
                ])
            );
        }

        // Get a user for our scenario
        [
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);

        // Add user to two private contest
        $numberOfPrivateContests = 2;
        for ($i = 0; $i < $numberOfPrivateContests; $i++) {
            $login = self::login($contestData[$i]['director']);
            \OmegaUp\Controllers\Contest::apiAddGroup(
                new \OmegaUp\Request([
                    'contest_alias' => strval(
                        $contestData[$i]['request']['alias']
                    ),
                    'group' => $groupData['group']->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
        }

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiListParticipating(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertSame(
            $numberOfPrivateContests,
            count($response['contests'])
        );
    }

    /**
     * Check that most recent updated contests are sorted on the top of the list
     */
    public function testLatestUpdatedPublicContests() {
        // Create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create three PUBLIC contests
        $contests = [];
        $contests[0] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'lastUpdated' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
            ])
        );

        $contests[1] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'lastUpdated' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() + 1
                ),
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contests[1]
        );

        $contests[2] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'lastUpdated' => new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get() + 2
                ),
            ])
        );
        $originalOrderContest = [
            $contests[2]['contest']->contest_id,
            $contests[1]['contest']->contest_id,
            $contests[0]['contest']->contest_id
        ];

        // Log as a random contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $loginContestant = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $loginContestant->auth_token,
            'page_size' => 100,
            'admission_mode' => 'public'
        ]));

        // Assert our contest is there.
        $apiListOrder = [];
        foreach ($response['results'] as $contest) {
            if (in_array($contest['contest_id'], $originalOrderContest)) {
                $apiListOrder[] = $contest['contest_id'];
            }
        }
        $this->assertSame($apiListOrder, $originalOrderContest);

        $login = self::login($contests[1]['director']);

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 5);
        // set contests[1] to private
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contests[1]['request']['alias'],
            'admission_mode' => 'private',
        ]));

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 10);
        // set contests[1] to public
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contests[1]['request']['alias'],
            'admission_mode' => 'public',
        ]));

        // New order must be [1, 2, 0]
        $modifiedOrderContest = [
            $contests[1]['contest']->contest_id,
            $contests[2]['contest']->contest_id,
            $contests[0]['contest']->contest_id
        ];

        $loginNewContestant = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $loginNewContestant->auth_token,
            'page_size' => 100,
            'admission_mode' => 'public'
        ]));

        // Assert our contest is there.
        $apiListOrder = [];
        foreach ($response['results'] as $contest) {
            if (in_array($contest['contest_id'], $originalOrderContest)) {
                $apiListOrder[] = $contest['contest_id'];
            }
        }

        $this->assertSame($apiListOrder, $modifiedOrderContest);
    }

    /**
     * Basic test. Check that only the first contest is on the list depending on selected tab
     */
    public function testShowAllContests() {
        $r = new \OmegaUp\Request();

        // Create 3 contests, the second one will occur in to the future and the third one will occur in to the past.
        $currentContestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'public']
            )
        );
        $futureContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams(
            [
                'admissionMode' => 'public',
                'finishTime' => ($currentContestData['request']['start_time'] + (60 * 60 * 49)),
                'startTime' => ($currentContestData['request']['start_time'] + (60 * 60 * 48)),
            ]
        ));
        $pastContestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams(
            [
                'admissionMode' => 'public',
                'finishTime' => ($currentContestData['request']['start_time'] - (60 * 60 * 48)),
                'startTime' => ($currentContestData['request']['start_time'] - (60 * 60 * 49)),
            ]
        ));

        // Check current contests
        $r = new \OmegaUp\Request([
            'page' => 1,
            'page_size' => 1,
            'tab_name' => 'current',
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);
        $this->assertArrayContainsInKey(
            $response['results'],
            'contest_id',
            $currentContestData['contest']->contest_id
        );

        // Check future contests
        $r = new \OmegaUp\Request([
            'page' => 1,
            'page_size' => 1,
            'tab_name' => 'future',
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);
        $this->assertArrayContainsInKey(
            $response['results'],
            'contest_id',
            $futureContestData['contest']->contest_id
        );

        // Check past contests
        $r = new \OmegaUp\Request([
            'page' => 1,
            'page_size' => 1,
            'tab_name' => 'past',
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);
        $this->assertArrayContainsInKey(
            $response['results'],
            'contest_id',
            $pastContestData['contest']->contest_id
        );
    }

    /**
     * A PHPUnit data provider for all the sorting option available in contest
     * list API.
     *
     * @return list<array{0: string, 1:list<int>, 2: bool}>
     */
    public function sortOrderProvider(): array {
        return [
            ['title', [4, 3, 2, 1], false],
            ['ends', [2, 3, 1, 4], false],
            ['duration', [3, 2, 4, 1], true],
            ['organizer', [4, 1, 2, 3], false],
            ['signedup', [1, 3, 4, 2], true],
        ];
    }

    /**
     * @param string $sortOrder
     * @param list<int> $expectedOrder
     *
     * @dataProvider sortOrderProvider
     */
    public function testContestListOrder(
        $sortOrder,
        $expectedOrder,
        $expectedParticipating
    ) {
        $now = \OmegaUp\Time::get();

        // Create 4 contests_mapping with the following configuration:
        // 1. Public contest_mapping that starts in 4 hours and finishes in 9 hours with alias contest_2 and title Contest 2 the admin username is user_2
        // 2. Public contest_mapping that starts in 2 hour and finishes in 8 hours with alias contest_3 and title Contest 3 the admin username is user_1
        // 3. Public contest_mapping that starts in 1 hour and finishes in 4 hours with alias contest_4 and title Contest 4 the admin username is user_4
        // 4. Public contest_mapping that starts in 3 hours and finishes in 5 hours with alias contest_1 and title Contest 1 the admin username is user_3
        $contestsMapping = [
            [
                'alias' => 'contest_2',
                'title' => 'Contest 2',
                'start_time' => $now + (4 * 60 * 60), // 4 hours from now
                'finish_time' => $now + (9 * 60 * 60), // 9 hours from now
                'admin_username' => 'user_2',
            ],
            [
                'alias' => 'contest_3',
                'title' => 'Contest 3',
                'start_time' => $now + (2 * 60 * 60), // 2 hours from now
                'finish_time' => $now + (8 * 60 * 60), // 8 hours from now
                'admin_username' => 'user_1',
            ],
            [
                'alias' => 'contest_4',
                'title' => 'Contest 4',
                'start_time' => $now + (1 * 60 * 60), // 1 hours from now
                'finish_time' => $now + (4 * 60 * 60), // 4 hours from now
                'admin_username' => 'user_4',
            ],
            [
                'alias' => 'contest_1',
                'title' => 'Contest 1',
                'start_time' => $now + (3 * 60 * 60), // 3 hours from now
                'finish_time' => $now + (5 * 60 * 60), // 5 hours from now
                'admin_username' => 'user_3',
            ],
        ];

        $contests = [];
        foreach ($contestsMapping as $contest) {
            // Get an admin for our scenario
            [
                'user' => $userAdmin,
                'identity' => $admin,
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => $contest['admin_username'],
                ])
            );

            // Create contest params
            $params = new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
                'alias' => $contest['alias'],
                'title' => $contest['title'],
                'startTime' => $contest['start_time'],
                'finishTime' => $contest['finish_time'],
                'contestDirector' => $admin,
                'contestDirectorUser' => $userAdmin,
            ]);

            // Create 4 contests
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                $params
            );
        }

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to the contest 3 and 1 (the second and the last one in the list)
        \OmegaUp\Test\Factories\Contest::addUser($contests[1], $identity);
        \OmegaUp\Test\Factories\Contest::addUser($contests[3], $identity);

        // Log in user
        $login = self::login($identity);

        // Get list of contests for user
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'sort_order' => $sortOrder,
            'page' => 1,
            'page_size' => 10,
            'tab_name' => 'future',
        ]))['results'];

        foreach ($expectedOrder as $index => $expectedContest) {
            $this->assertSame(
                "contest_{$expectedContest}",
                $response[$index]['alias']
            );
        }

        $this->assertSame(
            $expectedParticipating,
            $response[0]['participating']
        );
    }
}
