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
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        // Check response
        $this->assertEquals('ok', $response['status']);

        // Verify DB
        $user = UsersDAO::FindByUsername($r['username']);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $this->assertNotNull($user);
        $this->assertEquals($identity->password, $user->password);
    }

    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
            'old_password' => $user->password,
        ]);

        // Call api
        UserController::apiChangePassword($r);

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

        $user = UsersDAO::FindByUsername($user->username);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $this->assertEquals($identity->password, $user->password);
    }

    /**
     * Update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $locale = LanguagesDAO::getByName('pt');
        $states = StatesDAO::getByCountry('MX');
        $r = new Request([
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

        UserController::apiUpdate($r);

        // Check user/identity from db
        $userDb = AuthTokensDAO::getUserByToken($r['auth_token']);
        $identityDb = AuthTokensDAO::getIdentityByToken($r['auth_token']);

        $this->assertEquals($r['name'], $identityDb->name);
        $this->assertEquals($r['country_id'], $identityDb->country_id);
        $this->assertEquals($r['state_id'], $identityDb->state_id);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $userDb->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $userDb->graduation_date);
        $this->assertEquals($locale->language_id, $identityDb->language_id);

        // Edit all fields again with diff values
        $locale = LanguagesDAO::getByName('pseudo');
        $states = StatesDAO::getByCountry('US');
        $newName = Utils::CreateRandomString();
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => $newName,
            'country_id' => $states[0]->country_id,
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'primary',
            'birth_date' => strtotime('2000-02-02'),
            'graduation_date' => strtotime('2026-03-03'),
            'locale' => $locale->name,
        ]);

        UserController::apiUpdate($r);

        // Check user from db
        $userDb = AuthTokensDAO::getUserByToken($r['auth_token']);
        $identityDb = AuthTokensDAO::getIdentityByToken($r['auth_token']);
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
            IdentityController::getPreferredLanguage(new Request([
                'username' => $identityDb->username
            ]))
        );

        $identity = IdentitiesDAO::getByPK($userDb->main_identity_id);
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
        $r = new Request([
            'auth_token' => $login->auth_token,
            'username' => $newUsername,
            'password' => $newPassword,
        ]);

        UserController::apiUpdateBasicInfo($r);

        // Check user from db
        $userDb = AuthTokensDAO::getUserByToken($r['auth_token']);
        $identityDb = AuthTokensDAO::getIdentityByToken($r['auth_token']);

        // Getting identity data from db
        $identity = IdentitiesDAO::getByPK($userDb->main_identity_id);
        $this->assertEquals($identity->username, $identityDb->username);
        $this->assertEquals($identity->password, $identityDb->password);
    }
}
