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
        $password = \OmegaUp\Test\Utils::createRandomPassword();
        $email = "{$username}@{$username}.com";
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey,
                'birth_date' => 946684800, // 01-01-2000
            ])
        );

        // Check response
        $this->assertSame($username, $response['username']);

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

        $user = \OmegaUp\DAO\Emails::existsByEmail($email);
        $this->assertFalse($user);
    }

    public function testDeletedUserTriesToUseRegisteredEmail() {
        $username = \OmegaUp\Test\Utils::createRandomString();
        $email = "{$username}@{$username}.com";
        // Creates a user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['email' => $email]
            )
        );

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
        \OmegaUp\Controllers\User::apiDeleteConfirm(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'token' => $response['token'],
            ])
        );

        // Restore the email for testing purposes
        [$userEmail] = \OmegaUp\DAO\Emails::getByUserId($identity->user_id);
        $this->assertStringContainsString('deleted', $userEmail->email);

        $userEmail->email = $email;

        \OmegaUp\DAO\Emails::update($userEmail);

        $newUsername = \OmegaUp\Test\Utils::createRandomString();
        $newPassword = \OmegaUp\Test\Utils::createRandomPassword();
        // Call API mailFromUserLikelyRemoved
        try {
            \OmegaUp\Controllers\User::apiCreate(
                new \OmegaUp\Request([
                    'username' => $newUsername,
                    'password' => $newPassword,
                    'email' => $email,
                    'permission_key' => \OmegaUp\Controllers\User::$permissionKey,
                    'birth_date' => 946684800, // 01-01-2000
                ])
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('mailFromUserLikelyRemoved', $e->getMessage());
        }
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
                $identities[$id]->username
            );
            $this->assertNotNull($user);

            // Call api using admin
            $login = self::login($identities[$id]);

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
                $identities[$id]->username
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
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }
}
