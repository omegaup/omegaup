<?php
/**
 * Description of UserPrivacyPolicyTest
 *
 * @author juan.pablo
 */
class UserPrivacyPolicyTest extends OmegaupTestCase {
    /**
     * User reviews Privacy Consent Log
     */
    public function testUserReviewsPrivacyConsentLog() {
        // Create privacy policy
        $privacy_poilcy = UserFactory::createPrivacyStatement();

        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');
    }

    /**
     * User accepts for first time privacy policy
     */
    public function testUserAcceptsPrivacyPolicy() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy = UserFactory::createPrivacyStatement();
        $latest_privacy_policy = UserController::getPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
        ]));

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
            'privacy_git_object_id' => $latest_privacy_policy['git_object_id'],
            'statement_type' => $latest_privacy_policy['statement_type'],
        ]));

        $this->assertEquals($response['status'], 'ok');

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertTrue($response['hasAccepted'], 'User should have already accepted privacy policy');
    }

    /**
     * User tries to accept policy previously accepted
     */
    public function testUserTriesToAcceptPolicyPreviouslyAccepted() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy_version_1 = UserFactory::createPrivacyStatement();
        $latest_privacy_policy = UserController::getPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
        ]));

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
            'privacy_git_object_id' => $latest_privacy_policy['git_object_id'],
            'statement_type' => $latest_privacy_policy['statement_type'],
        ]));

        $this->assertEquals($response['status'], 'ok');

        try {
            UserController::apiAcceptPrivacyPolicy(new Request([
                'auth_token' => $login->auth_token,
                'privacy_git_object_id' => $latest_privacy_policy['git_object_id'],
                'statement_type' => $latest_privacy_policy['statement_type'],
            ]));
            $this->fail('Should have thrown a DuplicatedEntryInDatabaseException');
        } catch (DuplicatedEntryInDatabaseException $e) {
            // OK.
        }
    }

    /**
     * User accepts previous policy but not latest one
     */
    public function testAcceptsPreviousPolicyButNotLatestOne() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy_version_1 = UserFactory::createPrivacyStatement();
        $latest_privacy_policy = UserController::getPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
        ]));

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token,
            'privacystatement_id' => $latest_privacy_policy,
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token,
            'privacy_git_object_id' => $latest_privacy_policy['git_object_id'],
            'statement_type' => $latest_privacy_policy['statement_type'],
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Create other privacy policy
        $privacy_poilcy_version_2 = UserFactory::createPrivacyStatement();

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');
    }
}
