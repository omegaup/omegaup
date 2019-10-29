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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        // Add user to contest
        ContestsFactory::addUser($contestData, $identity);

        $login = self::login($contestData['director']);

        // Validate 0 users
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = \OmegaUp\Controllers\Contest::apiUsers($r);
        $this->assertEquals(1, count($response['users']));

        // Remove user
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'usernameOrEmail' => $identity->username,
        ]);
        \OmegaUp\Controllers\Contest::apiRemoveUser($r);

        // Validate 0 users in contest
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = \OmegaUp\Controllers\Contest::apiUsers($r);
        $this->assertEquals(0, count($response['users']));
    }
}
