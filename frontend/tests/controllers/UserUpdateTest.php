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

        $r = new Request();

        // Login
        $r['auth_token'] = $this->login($user);

        // Change values
        $r['name'] = Utils::CreateRandomString();
        $r['country_id'] = 'MX';

        $states = StatesDAO::search(array('country_id' => $r['country_id']));
        $r['state_id'] = $states[0]->state_id;

        $r['scholar_degree'] = 'Maestría';
        $r['birth_date'] = strtotime('1988-01-01');
        $r['graduation_date'] = strtotime('2016-02-02');
        $r['recruitment_optin'] = 1;

        // Call api
        $response = UserController::apiUpdate($r);

        // Check user from db
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->getName(), $r['name']);
        $this->assertEquals($user_db->getCountryId(), $r['country_id']);
        $this->assertEquals($user_db->getStateId(), $r['state_id']);
        $this->assertEquals($user_db->getScholarDegree(), $r['scholar_degree']);
        $this->assertEquals($user_db->getBirthDate(), gmdate('Y-m-d', $r['birth_date']));
        $this->assertEquals($user_db->getGraduationDate(), gmdate('Y-m-d', $r['graduation_date']));
        $this->assertEquals($user_db->getRecruitmentOptin(), $r['recruitment_optin']);
    }

    /**
     * @expectedException InvalidDatabaseOperationException
     */
    public function testBadUserUpdate() {
        $user = UserFactory::createUser();

        $r = new Request();
        $r['auth_token'] = $this->login($user);
        $r['name'] = Utils::CreateRandomString();

        // Invalid state_id
        $r['state_id'] = -1;

        UserController::apiUpdate($r);
    }

    /**
     * Name cannot be empty
     * @expectedException InvalidParameterException
     */
    public function testInvalidStateUpdate() {
        $user = UserFactory::createUser();

        $r = new Request();
        $r['auth_token'] = $this->login($user);

        // Invalid name
        $r['name'] = '';

        UserController::apiUpdate($r);
    }

    /**
     * Value for the recruitment optin flag should be non-negative
     * @expectedException InvalidParameterException
     */
    public function testInvalidStateUpdate() {
        $user = UserFactory::createUser();

        $r = new Request();
        $r['auth_token'] = $this->login($user);
        $r['name'] = Utils::CreateRandomString();

        // Invalid recruitment_optin
        $r['recruitment_optin'] = -1;

        UserController::apiUpdate($r);
    }

    /**
     * Null values for the recruitment optin flag are allowed
     */
    public function testRecruitmentOptinUpdate() {
        $user = UserFactory::createUser();

        $r = new Request();
        $r['auth_token'] = $this->login($user);
        $r['name'] = Utils::CreateRandomString();

        // Null recruitment_optin
        $r['recruitment_optin'] = NULL;
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->getRecruitmentOptin(), $r['recruitment_optin']);

        // Null recruitment_optin
        $r['recruitment_optin'] = 1;
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->getRecruitmentOptin(), $r['recruitment_optin']);

        // Null recruitment_optin
        $r['recruitment_optin'] = 0;
        UserController::apiUpdate($r);
        $user_db = AuthTokensDAO::getUserByToken($r['auth_token']);
        $this->assertEquals($user_db->getRecruitmentOptin(), $r['recruitment_optin']);
    }
}
