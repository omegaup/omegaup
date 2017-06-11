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
            'recruitment_optin' => 1,
            // Invalid state_id
            'state_id' => -1,
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
     * Exercising valid values for the recruitment flag while updating an user
     */
    public function testRecruitmentOptionUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
        ]);

        // Set recruitment_optin to true
        $r['recruitment_optin'] = 1;
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->recruitment_optin, $r['recruitment_optin']);

        // Set recruitment_optin to false
        $r['recruitment_optin'] = 0;
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->recruitment_optin, $r['recruitment_optin']);
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
     */
    public function testUpdateCountryWithNoStateData() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Choose a country for which we dont have state
        // data, like Nicaragua
        $country_id = 'NI';
        $r = new Request([
            'auth_token' => $login->auth_token,
            'country_id' => $country_id,
        ]);

        UserController::apiUpdate($r);

        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->country_id, $country_id);
    }
}
