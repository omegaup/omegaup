<?php

/**
 * Testing synchronization between User and Identity
 *
 * @author juan.pablo
 */
class UserIdentitySynchronizeTest extends OmegaupTestCase {
    /**
     * Creates an omegaup user happily :)
     */
    public function testCreateUserPositive() {
        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        // Check response
        $this->assertEquals('ok', $response['status']);

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        $this->assertNotNull($user);
    }

    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
            'old_password' => $user->password,
        ]);

        // Call api
        \OmegaUp\Controllers\User::apiChangePassword($r);

        // Try to login with old password, should fail
        try {
            self::login($user);
            $this->fail('Reset password failed');
        } catch (Exception $e) {
            // We are OK
        }

        // Set new password and try again, should succeed
        $user->password = $r['password'];
        self::login($user);

        $user = \OmegaUp\DAO\Users::FindByUsername($user->username);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
    }

    /**
     * Update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => 'new_username',
            'name' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user/identity from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken($r['auth_token']);

        $this->assertEquals($r['name'], $identityDb->name);
        $this->assertEquals($r['country_id'], $identityDb->country_id);
        $this->assertEquals($r['state_id'], $identityDb->state_id);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $userDb->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $userDb->graduation_date);
        $this->assertEquals($locale->language_id, $identityDb->language_id);

        // Edit all fields again with diff values
        $locale = \OmegaUp\DAO\Languages::getByName('pseudo');
        $states = \OmegaUp\DAO\States::getByCountry('US');
        $newName = Utils::CreateRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $newName,
            'country_id' => $states[0]->country_id,
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'primary',
            'birth_date' => strtotime('2000-02-02'),
            'graduation_date' => strtotime('2026-03-03'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken($r['auth_token']);
        $this->assertEquals($r['name'], $identityDb->name);
        $this->assertEquals($r['country_id'], $identityDb->country_id);
        $this->assertEquals($r['state_id'], $identityDb->state_id);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $userDb->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $userDb->graduation_date);
        $this->assertEquals($locale->language_id, $identityDb->language_id);

        // Double check language update with the appropiate API
        $this->assertEquals(
            $locale->name,
            \OmegaUp\Controllers\Identity::getPreferredLanguage(new \OmegaUp\Request([
                'username' => $identityDb->username
            ]))
        );

        $identity = \OmegaUp\DAO\Identities::getByPK($userDb->main_identity_id);
        $this->assertEquals($identity->username, $identityDb->username);
        $this->assertEquals($identity->password, $identityDb->password);
    }

    /**
     * Update basic info test
     */
    public function testUserUpdateBasicInfo() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $newUsername = 'new_username_basic_info';
        $newPassword = Utils::CreateRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $newUsername,
            'password' => $newPassword,
        ]);

        \OmegaUp\Controllers\User::apiUpdateBasicInfo($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken($r['auth_token']);

        // Getting identity data from db
        $identity = \OmegaUp\DAO\Identities::getByPK($userDb->main_identity_id);
        $this->assertEquals($identity->username, $identityDb->username);
        $this->assertEquals($identity->password, $identityDb->password);
    }
}
