<?php

/**
 * Description of ContestUsersTest
 *
 * @author joemmanuel
 */

class ContestUsersTest extends OmegaupTestCase {
    public function testContestUsersValid() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Create 10 users
        $n = 10;
        $users = [];
        for ($i = 0; $i < $n; $i++) {
            // Create a user
            $users[$i] = UserFactory::createUser();

            // Add it to the contest
            ContestsFactory::addUser($contestData, $users[$i]);
        }

        // Create a n+1 user who will just join to the contest withot being
        // added via API. For public contests, by entering to the contest, the user should be in
        // the list of contest's users.
        $nonRegisteredUser = UserFactory::createUser();
        ContestsFactory::openContest($contestData, $nonRegisteredUser);

        // Log in with the admin of the contest
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call API
        $response = ContestController::apiUsers($r);

        // Check that we have n+1 users
        $this->assertEquals($n+1, count($response['users']));
    }

    public function testContestActivityReport() {
        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        $user = UserFactory::createUser();
        ContestsFactory::openContest($contestData, $user);

        $userLogin = self::login($user);
        ContestController::apiDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Call API
        $directorLogin = self::login($contestData['director']);
        $response = ContestController::apiActivityReport(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals($user->username, $response['events'][0]['username']);
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }
}
