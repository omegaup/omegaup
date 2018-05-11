<?php
/**
 * Description of UserPrivacyPoliciesTest
 *
 * @author juan.pablo
 */
class UserPrivacyPoliciesTest extends OmegaupTestCase {
    /**
     * User reviews Audit Log
     */
    public function testUserReviewsAuditLog() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token,
            'git_object_id' => Utils::CreateRandomString()
        ]));

        $this->assertFalse($response['hasAccepted'], 'User already has accepted privacy policies');
    }

    /**
     * User accepts for first time privacy policies
     */
    public function testUserAcceptsPolicies() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        $response = UserController::apiAcceptPrivacyPolicies(new Request([
            'auth_token' => $login->auth_token,
            'git_object_id' => Utils::CreateRandomString()
        ]));

        $this->assertEquals($response['status'], 'ok');
    }

    /**
     * User tries to accept policies previously accepted
     */
    public function testUserTriesToAcceptPoliciesPreviouslyAccepted() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        $git_object_id_version_1 = Utils::CreateRandomString();
        $git_object_id_version_2 = Utils::CreateRandomString();

        $response = UserController::apiAcceptPrivacyPolicies(new Request([
            'auth_token' => $login->auth_token,
            'git_object_id' => $git_object_id_version_1
        ]));

        $this->assertEquals($response['status'], 'ok');

        $this->setExpectedException('DuplicatedEntryInDatabaseException');

        $response = UserController::apiAcceptPrivacyPolicies(new Request([
            'auth_token' => $login->auth_token,
            'git_object_id' => $git_object_id_version_1
        ]));

        $response = UserController::apiAcceptPrivacyPolicies(new Request([
            'auth_token' => $login->auth_token,
            'git_object_id' => $git_object_id_version_2
        ]));

        $this->assertEquals($response['status'], 'ok');
    }
}
