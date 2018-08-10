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
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($r['name'], $user_db->name);
        $this->assertEquals($r['country_id'], $user_db->country_id);
        $this->assertEquals($r['state_id'], $user_db->state_id);
        $this->assertEquals($r['scholar_degree'], $user_db->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $user_db->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $user_db->graduation_date);
        $this->assertEquals($locale->language_id, $user_db->language_id);

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
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($r['name'], $user_db->name);
        $this->assertEquals($r['country_id'], $user_db->country_id);
        $this->assertEquals($r['state_id'], $user_db->state_id);
        $this->assertEquals($r['scholar_degree'], $user_db->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $user_db->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $user_db->graduation_date);
        $this->assertEquals($locale->language_id, $user_db->language_id);

        // Double check language update with the appropiate API
        $r = new Request([
            'username' => $user->username
        ]);
        $this->assertEquals($locale->name, UserController::getPreferredLanguage($r));
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
}
