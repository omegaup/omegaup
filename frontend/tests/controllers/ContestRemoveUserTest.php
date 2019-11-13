<?php

/**
 * Description of ContestRemoveUserTest
 *
 * @author joemmanuel
 */
class ContestRemoveUserTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRemoveUser() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

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
