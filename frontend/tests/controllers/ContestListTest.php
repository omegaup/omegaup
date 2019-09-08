<?php

/**
 * Description of ListContests
 *
 * @author joemmanuel
 */

class ContestListTest extends OmegaupTestCase {
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

        $durationFromResponse = $contestFromResponse['finish_time'] - $contestFromResponse['start_time'];
        $durationFromRequest = $contestData['request']['finish_time'] - $contestData['request']['start_time'];

        $this->assertEquals($durationFromRequest, $durationFromResponse);
    }

    /**
     * Basic test. Check that most recent contest is at the top of the list
     */
    public function testLatestPublicContest() {
        // Create new PUBLIC contest
        $contestData = ContestsFactory::createContest();

        // Log as a random contestant
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'page_size' => 50
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);

        // Assert our contest is there.
        foreach ($response['results'] as $contest) {
            if ($contest['title'] == $contestData['request']['title']) {
                return;
            }
        }
        $this->assertFalse(true, 'Array does not contain created contest');
    }

    /**
     * Basic test. Check that most recent contest is at the top of the list
     */
    public function testLatestPublicContestNotLoggedIn() {
        // Create new PUBLIC contest
        $contestData = ContestsFactory::createContest();

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
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        $login = self::login($contestant);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
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
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    public function testPrivateContestForNonInvitedUser() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        $login = self::login(UserFactory::createUser());

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
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        $login = self::login(UserFactory::createAdminUser());

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
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addAdminUser($contestData, $contestant);

        $login = self::login($contestant);

        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
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
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));
        $title = $contestData['request']['title'];

        $admin1 = UserFactory::createUser();
        $admin2 = UserFactory::createUser();

        $login = self::login($admin1);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
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
        $group = GroupsFactory::createGroup($contestData['director']);
        GroupsFactory::addUserToGroup($group, $admin1);
        GroupsFactory::addUserToGroup($group, $admin2);
        ContestsFactory::addGroupAdmin($contestData, $group['group']);

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
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));
        $author = $contestData['director'];
        $title = $contestData['request']['title'];

        $admin1 = UserFactory::createUser();
        $admin2 = UserFactory::createUser();

        // Add user to our private contest
        $group = GroupsFactory::createGroup($author);
        GroupsFactory::addUserToGroup($group, $admin1);
        GroupsFactory::addUserToGroup($group, $admin2);
        ContestsFactory::addGroupAdmin($contestData, $group['group']);

        $login = self::login($author);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
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
        $r = new \OmegaUp\Request();

        // Create 2 contests, with the not-recommended.finish_time > recommended.finish_time
        $recommendedContestData = ContestsFactory::createContest();
        $notRecommendedContestData = ContestsFactory::createContest(new ContestParams(
            [
                'finish_time' => $recommendedContestData['request']['finish_time'] + 1,
            ]
        ));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Turn recommended ON
        $login = self::login(UserFactory::createAdminUser());
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $recommendedContestData['request']['alias'],
            'value' => 1,
        ]);
        \OmegaUp\Controllers\Contest::apiSetRecommended($r);
        unset($login);

        // Get list of contests
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiList($r);

        // Check that recommended contest is earlier in list han not-recommended
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

    /**
     * Test to set recommended value in two contests.
     */
    public function testRecommendedSContestsList() {
        // Create 2 contests not-recommended
        $recommendedContest[0] = ContestsFactory::createContest();
        $recommendedContest[1] = ContestsFactory::createContest();

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Get list of contests
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Assert that two contests are not recommended
        for ($i = 0; $i < 2; $i++) {
            $contest = $recommendedContest[$i];
            $contest = $this->findByPredicate($response['results'], function ($value) use ($contest) {
                return $value['alias'] == $contest['contest']->alias;
            });

            $this->assertEquals(0, $contest['recommended']);
        }

        // Turn recommended ON
        // phpcbf does not like a block just for scoping purposes and
        // messes up the alignment pretty badly.
        if (true) {
            $login = self::login(UserFactory::createAdminUser());
            for ($i = 0; $i < 2; $i++) {
                \OmegaUp\Controllers\Contest::apiSetRecommended(new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $recommendedContest[$i]['request']['alias'],
                    'value' => 1,
                ]));
            }
        }

        // Get list of contests
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Assert that two contests are already recommended
        for ($i = 0; $i < 2; $i++) {
            $contest = $recommendedContest[$i];
            $contest = $this->findByPredicate($response['results'], function ($value) use ($contest) {
                return $value['alias'] == $contest['contest']->alias;
            });

            $this->assertEquals(1, $contest['recommended']);
        }
    }

    /**
     * Basic test. Check that only the first contest is on the list
     */
    public function testShowOnlyCurrentContests() {
        $r = new \OmegaUp\Request();

        // Create 2 contests, the second one will occur in to the future.
        $currentContestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));
        $futureContestData = ContestsFactory::createContest(new ContestParams(
            [
                'admission_mode' => 'private',
                'finish_time' => ($currentContestData['request']['start_time'] + (60 * 60 * 49)),
                'start_time' => ($currentContestData['request']['start_time'] + (60 * 60 * 48)),
            ]
        ));

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contests
        ContestsFactory::addUser($currentContestData, $contestant);
        ContestsFactory::addUser($futureContestData, $contestant);

        $login = self::login($contestant);
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
        for ($i = 0; $i < 4; $i++) {
            $isPublic = ($i === 0) ? 'public' : 'private';
            $contestData[$i] = ContestsFactory::createContest(new ContestParams(['admission_mode' => $isPublic]));
        }

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to two private contest
        $numberOfPrivateContests = 2;
        for ($i = 0; $i < $numberOfPrivateContests; $i++) {
            ContestsFactory::addUser($contestData[$i], $contestant);
        }

        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiListParticipating($r);

        $this->assertEquals($numberOfPrivateContests, count($response['contests']));
    }

    /**
     * Check that most recent updated contests are sorted on the top of the list
     */
    public function testLatestUpdatedPublicContests() {
        // Create a problem
        $problemData = ProblemsFactory::createProblem();

        // Create three PUBLIC contests
        $contests[0] = ContestsFactory::createContest(
            new ContestParams([
                'last_updated' => \OmegaUp\Time::get()
            ])
        );

        $contests[1] = ContestsFactory::createContest(
            new ContestParams([
                'last_updated' => \OmegaUp\Time::get() + 1
            ])
        );
        ContestsFactory::addProblemToContest($problemData, $contests[1]);

        $contests[2] = ContestsFactory::createContest(
            new ContestParams([
                'last_updated' => \OmegaUp\Time::get() + 2
            ])
        );
        $originalOrderContest = [
            $contests[2]['contest']->contest_id,
            $contests[1]['contest']->contest_id,
            $contests[0]['contest']->contest_id
        ];

        // Log as a random contestant
        $contestant = UserFactory::createUser();

        $loginContestant = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $loginContestant->auth_token,
            'page_size' => 50,
            'admission_mode' => 'public'
        ]));

        // Assert our contest is there.
        $apiListOrder = [];
        foreach ($response['results'] as $contest) {
            if (in_array($contest['contest_id'], $originalOrderContest)) {
                $apiListOrder[] = $contest['contest_id'];
            }
        }
        $this->assertEquals($apiListOrder, $originalOrderContest);

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

        $loginNewContestant = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiList(new \OmegaUp\Request([
            'auth_token' => $loginNewContestant->auth_token,
            'page_size' => 50,
            'admission_mode' => 'public'
        ]));

        // Assert our contest is there.
        $apiListOrder = [];
        foreach ($response['results'] as $contest) {
            if (in_array($contest['contest_id'], $originalOrderContest)) {
                $apiListOrder[] = $contest['contest_id'];
            }
        }

        $this->assertEquals($apiListOrder, $modifiedOrderContest);
    }
}
