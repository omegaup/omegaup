<?php
/**
 * Description of UpdateContest
 *
 * @author joemmanuel
 */
class UserUpdateTest extends OmegaupTestCase {
    /**
     * Basic update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $locale = LanguagesDAO::getByName('pt');
        $states = StatesDAO::getByCountry('MX');
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
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

        // Edit all fields again with diff values
        $locale = LanguagesDAO::getByName('pseudo');
        $states = StatesDAO::getByCountry('US');
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
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
        $r = new Request([
            'username' => $user->username
        ]);
        $this->assertEquals($locale->name, IdentityController::getPreferredLanguage(
            $r
        ));
    }

    /**
     * Value for the recruitment optin flag should be non-negative
     * @expectedException InvalidParameterException
     */
    public function testNegativeStateUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            // Invalid state_id
            'state_id' => -1,
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * Update profile username with non-existence username
     */
    public function testUsernameUpdate() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $new_username = Utils::CreateRandomString();
        $r = new Request([
            'auth_token' => $login->auth_token,
            //new username
            'username' => $new_username
        ]);
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);

        $this->assertEquals($user_db->username, $new_username);
    }

    /**
     * Update profile username with existed username
     * @expectedException DuplicatedEntryInDatabaseException
     */
    public function testDuplicateUsernameUpdate() {
        $old_user = UserFactory::createUser();
        $user = UserFactory::createUser();
        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            //update username with existed username
            'username' => $old_user->username
        ]);
        UserController::apiUpdate($r);
    }

     /**
     * Request parameter name cannot be too long
     * @expectedException InvalidParameterException
     */
    public function testNameUpdateTooLong() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => 'TThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong hisIsWayTooLong ',
            'country_id' => 'MX',
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * Request parameter name cannot be empty
     * @expectedException InvalidParameterException
     */
    public function testEmptyNameUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => '',
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * @expectedException InvalidParameterException
     */
    public function testFutureBirthday() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'birth_date' => strtotime('2088-01-01'),
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/997
     * Superceded by https://github.com/omegaup/omegaup/issues/1228
     */
    public function testUpdateCountryWithNoStateData() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Omit state.
        $country_id = 'MX';
        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => $country_id,
        ]);

        try {
            UserController::apiUpdate($r);
            $this->fail('All countries now have state information, so it must be provided.');
        } catch (InvalidParameterException $e) {
            // OK!
        }
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with invalid gender option
     */
    public function testGenderWithInvalidOption() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        //generate wrong gender option
        $r = new Request([
            'auth_token' => $login->auth_token,
            'gender' => Utils::CreateRandomString(),
        ]);

        try {
            UserController::apiUpdate($r);
            $this->fail('Please select a valid gender option');
        } catch (InvalidParameterException $e) {
            // OK!
        }
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with valid gender option
     */
    public function testGenderWithValidOption() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'gender' => 'female',
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with valid default option null
     */
    public function testGenderWithNull() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'gender' => null,
        ]);

        UserController::apiUpdate($r);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with invalid gender option
     */
    public function testGenderWithEmptyString() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        //generate wrong gender option
        $r = new Request([
            'auth_token' => $login->auth_token,
            'gender' => '',
        ]);

        try {
            UserController::apiUpdate($r);
            $this->fail('Please select a valid gender option');
        } catch (InvalidParameterException $e) {
            // OK!
        }
    }

    /**
     * Tests that the user can generate a git token.
     */
    public function testGenerateGitToken() {
        $user = UserFactory::createUser();
        $this->assertNull($user->git_token);
        $login = self::login($user);
        $response = UserController::apiGenerateGitToken(new Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertNotEquals($response['token'], '');

        $dbUser = UsersDAO::FindByUsername($user->username);
        $this->assertNotNull($dbUser->git_token);
        $this->assertTrue(SecurityTools::compareHashedStrings($response['token'], $dbUser->git_token));
    }

    /**
     * Tests that users that have old hashes can migrate transparently to
     * Argon2id.
     */
    public function testOldHashTransparentMigration() {
        // Create the user and manually set its password to the well-known
        // 'omegaup' hash.
        $user = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $identity->password = '$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a';
        IdentitiesDAO::update($identity);
        $user->password = $identity->password;
        UsersDAO::update($user);
        $this->assertTrue(SecurityTools::isOldHash($identity->password));

        // After logging in, the password should have been updated.
        $identity->password = 'omegaup';
        self::login($identity);
        $identity = IdentitiesDAO::getByPK($identity->identity_id);
        $this->assertFalse(SecurityTools::isOldHash($identity->password));

        // After logging in once, the user should be able to log in again with
        // the exact same password.
        $identity->password = 'omegaup';
        self::login($identity);
    }
}
