<?php

/**
 * Description of ContestUsersTest
 *
 * @author joemmanuel
 */

class ContestUsersTest extends OmegaupTestCase {
    public function testContestUsersValid() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Create 10 users
        $n = 10;
        $users = array();
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

        // Prepare request
        $r = new Request();
        $r['contest_alias'] = $contestData['request']['alias'];

        // Log in with the admin of the contest
        $r['auth_token'] = $this->login($contestData['director']);

        // Call API
        $response = ContestController::apiUsers($r);

        // Check that we have n+1 users
        $this->assertEquals($n+1, count($response['users']));
    }

    public function testContestActivityReport() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        $user = UserFactory::createUser();
        ContestsFactory::openContest($contestData, $user);

        ContestController::apiDetails(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $this->login($user),
        )));

        // Call API
        $response = ContestController::apiActivityReport(new Request(array(
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $this->login($contestData['director']),
        )));

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals($user->username, $response['events'][0]['username']);
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }
}
