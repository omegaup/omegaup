<?php

/**
 * Testing synchronization betwen User and Identity
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
     * Reset password via admin
     */
    public function testResetPasswordViaAdmin() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        // Create the admin who will change the password
        $admin = UserFactory::createAdminUser();

        $adminLogin = self::login($admin);
        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
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

        // Sanity check, admin should be able to login fine
        self::login($admin);

        $user = UsersDAO::FindByUsername($user->username);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
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
     * Basic update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $locale = LanguagesDAO::search(['name' => 'pt']);
        $states = StatesDAO::search(['country_id' => 'MX']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'Maestría',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'locale' => $locale[0]->name,
            'recruitment_optin' => 1,
        ]);

        UserController::apiUpdate($r);

        // Check user from db
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($r['name'], $user_db->name);
        $this->assertEquals($r['country_id'], $user_db->country_id);
        $this->assertEquals($r['state_id'], $user_db->state_id);
        $this->assertEquals($r['scholar_degree'], $user_db->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $user_db->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $user_db->graduation_date);
        $this->assertEquals($r['recruitment_optin'], $user_db->recruitment_optin);
        $this->assertEquals($locale[0]->language_id, $user_db->language_id);

        // Edit all fields again with diff values
        $locale = LanguagesDAO::search(['name' => 'pseudo']);
        $states = StatesDAO::search(['country_id' => 'US']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'country_id' => $states[0]->country_id,
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'Primaria',
            'birth_date' => strtotime('2000-02-02'),
            'graduation_date' => strtotime('2026-03-03'),
            'locale' => $locale[0]->name,
            'recruitment_optin' => 0,
        ]);

        UserController::apiUpdate($r);

        // Check user from db
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($r['name'], $user_db->name);
        $this->assertEquals($r['country_id'], $user_db->country_id);
        $this->assertEquals($r['state_id'], $user_db->state_id);
        $this->assertEquals($r['scholar_degree'], $user_db->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $user_db->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $user_db->graduation_date);
        $this->assertEquals($r['recruitment_optin'], $user_db->recruitment_optin);
        $this->assertEquals($locale[0]->language_id, $user_db->language_id);

        // Double check language update with the appropiate API
        $r = new Request([
            'username' => $user->username
        ]);
        $this->assertEquals($locale[0]->name, UserController::getPreferredLanguage($r));

        $identity = IdentitiesDAO::getByPK($user_db->main_identity_id);
        $this->assertEquals($identity->password, $user_db->password);
    }
}
