<?php

/**
 * Description of ContestRemoveUserTest
 *
 * @author joemmanuel
 */
class ContestRemoveUserTest extends OmegaupTestCase {
    public function testRemoveUser() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Create a user
        $user = UserFactory::createUser();

        // Add user to contest
        ContestsFactory::addUser($contestData, $user);

        $login = self::login($contestData['director']);

        // Validate 0 users
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = ContestController::apiUsers($r);
        $this->assertEquals(1, count($response['users']));

        // Remove user
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'usernameOrEmail' => $user->username,
        ]);
        ContestController::apiRemoveUser($r);

        // Validate 0 users in contest
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = ContestController::apiUsers($r);
        $this->assertEquals(0, count($response['users']));
    }
}
