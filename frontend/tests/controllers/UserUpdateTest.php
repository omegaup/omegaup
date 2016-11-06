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

        $states = StatesDAO::search(array('country_id' => 'MX'));
        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'Maestría',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'recruitment_optin' => 1,
        ));

        // Call api
        $response = UserController::apiUpdate($r);

        // Check user from db
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->name, $r['name']);
        $this->assertEquals($user_db->country_id, $r['country_id']);
        $this->assertEquals($user_db->state_id, $r['state_id']);
        $this->assertEquals($user_db->scholar_degree, $r['scholar_degree']);
        $this->assertEquals($user_db->birth_date, gmdate('Y-m-d', $r['birth_date']));
        $this->assertEquals($user_db->graduation_date, gmdate('Y-m-d', $r['graduation_date']));
        $this->assertEquals($user_db->recruitment_optin, $r['recruitment_optin']);
    }

    /**
     * Value for the recruitment optin flag should be non-negative
     * @expectedException InvalidDatabaseOperationException
     */
    public function testNegativeStateUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'recruitment_optin' => 1,
            // Invalid state_id
            'state_id' => -1,
        ));

        UserController::apiUpdate($r);
    }

     /**
     * Request parameter name cannot be too long
     * @expectedException InvalidParameterException
     */
    public function testNameUpdateTooLong() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => 'TThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong hisIsWayTooLong ',
            'country_id' => 'MX',
        ));

        UserController::apiUpdate($r);
    }

    /**
     * Request parameter name cannot be empty
     * @expectedException InvalidParameterException
     */
    public function testEmptyNameUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => '',
        ));

        UserController::apiUpdate($r);
    }

    /**
     * Request parameter recruitment_optin cannot be null
     * @expectedException InvalidParameterException
     */
    public function testNullRecruitmentOptinUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            // Null recruitment_optin
            'recruitment_optin' => null,
        ));

        UserController::apiUpdate($r);
    }

    /**
     * Exercising valid values for the recruitment flag while updating an user
     */
    public function testRecruitmentOptinUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
        ));

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
}
