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
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

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
        $r = new Request();

        // Create new PUBLIC contest
        $contestData = ContestsFactory::createContest();

        $response = ContestController::apiList($r);

        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);
    }

    /**
     *
     */
    public function testPrivateContestForInvitedUser() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(null, false /*private*/);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        $login = self::login($contestant);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);
    }

    /**
     *
     */
    public function testPrivateContestForNonInvitedUser() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(null, false /*private*/);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        $login = self::login(UserFactory::createUser());
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

        // Assert our contest is not there
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    /**
     *
     */
    public function testPrivateContestForSystemAdmin() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(null, false /*private*/);

        $login = self::login(UserFactory::createAdminUser());
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

        // Assert our contest is there
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);
    }

    /**
     *
     */
    public function testPrivateContestForContestAdmin() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(null, false /*private*/);

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addAdminUser($contestData, $contestant);

        $login = self::login($contestant);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

        // Assert our contest is there
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
        $this->assertDurationIsCorrect($response, $contestData);
    }

    /**
     *
     */
    public function testPrivateContestForContestGroupAdmin() {
        // Create new private contest
        $contestData = ContestsFactory::createContest(null, true /*private*/);

        $admin1 = UserFactory::createUser();
        $admin2 = UserFactory::createUser();

        $login = self::login($admin1);
        $response = ContestController::apiList(new Request([
            'auth_token' => $login->auth_token,
        ]));

        // Assert our contest is there
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'title',
            $contestData['request']['title']
        );

        // Add user to our private contest
        $group = GroupsFactory::createGroup($contestData['director']);
        GroupsFactory::addUserToGroup($group, $admin1);
        GroupsFactory::addUserToGroup($group, $admin2);
        ContestsFactory::addGroupAdmin($contestData, $group['group']);

        $response = ContestController::apiList(new Request([
            'auth_token' => $login->auth_token,
        ]));

        // Assert our contest is there
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'title',
            $contestData['request']['title']
        );
    }

    /**
     * Test that contests with recommended flag show first in list.
     */
    public function testRecommendedShowsOnTop() {
        $r = new Request();

        // Create 2 contests, with the not-recommended.finish_time > recommended.finish_time
        $recommendedContestData = ContestsFactory::createContest();
        $notRecommendedContestData = ContestsFactory::createContest(
            null,
            0,
            null,
            $recommendedContestData['request']['finish_time'] + 1
        );

        // Get a user for our scenario
        $contestant = UserFactory::createUser();

        // Turn recommended ON
        $login = self::login(UserFactory::createAdminUser());
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'contest_alias' => $recommendedContestData['request']['alias'],
            'value' => 1,
        ));
        ContestController::apiSetRecommended($r);
        unset($login);

        // Get list of contests
        $login = self::login($contestant);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ContestController::apiList($r);

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
}
