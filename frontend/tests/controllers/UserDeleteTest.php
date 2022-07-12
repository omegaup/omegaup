<?php
/**
 * UserDeleteTest
 */
class UserDeleteTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Deletes an omegaup user as admin
     */
    public function testDeleteUserAsAdmin() {
        $username = \OmegaUp\Test\Utils::createRandomString();
        $email = "{$username}@{$username}.com";
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                'username' => $username,
                'password' => $username,
                'email' => $email,
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey,
            ])
        );

        // Check response
        $this->assertEquals($username, $response['username']);

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $this->assertNotNull($user);

        // Admin will delete $user
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Call api using admin
        $login = self::login($admin);

        // Call API to request a token
        $response = \OmegaUp\Controllers\User::apiDeleteRequest(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
            ])
        );

        $this->assertNotNull($response['token']);

        // Call API
        $response = \OmegaUp\Controllers\User::apiDeleteConfirm(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'token' => $response['token'],
            ])
        );

        // Verify DB user no longer exists
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $this->assertNull($user);
    }

    /**
     * User themselves deletes their account
     */
    public function testDeleteUser() {
        // Creates a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNotNull($user);

        // Call api using admin
        $login = self::login($identity);

        // Call API to request a token
        $response = \OmegaUp\Controllers\User::apiDeleteRequest(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertNotNull($response['token']);

        // Call API
        $response = \OmegaUp\Controllers\User::apiDeleteConfirm(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'token' => $response['token'],
            ])
        );

        // Verify DB user no longer exists
        $user = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNull($user);
    }

    /**
     * Three User themselves deletes their account
     */
    public function testDeletethreeUser() {
        $identities = [];
        $numberOfContestants = 3;
        foreach (range(0, $numberOfContestants - 1) as $id) {
            [
            'identity' => $identities[$id],
            ] = \OmegaUp\Test\Factories\User::createUser();

          // Verify DB
            $user = \OmegaUp\DAO\Users::FindByUsername(
                $identity[$id]->username
            );
            $this->assertNotNull($user);

          // Call api using admin
            $login = self::login($identity[$id]);

          // Call API to request a token
            $response = \OmegaUp\Controllers\User::apiDeleteRequest(
                new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                ])
            );

            $this->assertNotNull($response['token']);

          // Call API
            $response = \OmegaUp\Controllers\User::apiDeleteConfirm(
                new \OmegaUp\Request([
                  'auth_token' => $login->auth_token,
                  'token' => $response['token'],
                ])
            );

         // Verify DB user no longer exists
            $user = \OmegaUp\DAO\Users::FindByUsername(
                $identity[$id]->username
            );
            $this->assertNull($user);
        }
    }
    /**
     * Normal user tries to delete another's user account
     */
    public function testDeleteAccountFromAnotherUser() {
        // Creates a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $normalUser] = \OmegaUp\Test\Factories\User::createUser();

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNotNull($user);

        // Call api using admin
        $login = self::login($normalUser);

        try {
            // Call API to request a token
            \OmegaUp\Controllers\User::apiDeleteRequest(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $identity->username,
                ])
            );
            $this->fail(
                'Should have failed because no one could delete an account but the owner or admin'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
