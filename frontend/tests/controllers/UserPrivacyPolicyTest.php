<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Description of UserPrivacyPolicyTest
 */
class UserPrivacyPolicyTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * User reviews Privacy Consent Log
     */
    public function testUserReviewsPrivacyConsentLog() {
        // Create privacy policy
        \OmegaUp\Test\Factories\User::createPrivacyStatement();

        // Create the user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiLastPrivacyPolicyAccepted(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ])
        );

        $this->assertFalse(
            $response['hasAccepted'],
            'User should not have already accepted privacy policy'
        );
    }

    /**
     * User accepts for first time privacy policy
     */
    public function testUserAcceptsPrivacyPolicy() {
        // Create the user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create privacy policy
        \OmegaUp\Test\Factories\User::createPrivacyStatement();
        $latestPrivacyPolicy = \OmegaUp\Controllers\User::getPrivacyPolicyDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $response = \OmegaUp\Controllers\User::apiAcceptPrivacyPolicy(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'privacy_git_object_id' => $latestPrivacyPolicy['git_object_id'],
                'statement_type' => $latestPrivacyPolicy['statement_type'],
            ])
        );

        $this->assertEquals($response['status'], 'ok');

        $response = \OmegaUp\Controllers\User::apiLastPrivacyPolicyAccepted(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ])
        );

        $this->assertTrue(
            $response['hasAccepted'],
            'User should have already accepted privacy policy'
        );
    }

    /**
     * User tries to accept policy previously accepted
     */
    public function testUserTriesToAcceptPolicyPreviouslyAccepted() {
        // Create the user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create privacy policy
        \OmegaUp\Test\Factories\User::createPrivacyStatement();
        $latestPrivacyPolicy = \OmegaUp\Controllers\User::getPrivacyPolicyDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $response = \OmegaUp\Controllers\User::apiAcceptPrivacyPolicy(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'privacy_git_object_id' => $latestPrivacyPolicy['git_object_id'],
                'statement_type' => $latestPrivacyPolicy['statement_type'],
            ])
        );

        $this->assertEquals($response['status'], 'ok');

        try {
            \OmegaUp\Controllers\User::apiAcceptPrivacyPolicy(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'privacy_git_object_id' => $latestPrivacyPolicy['git_object_id'],
                    'statement_type' => $latestPrivacyPolicy['statement_type'],
                ])
            );
            $this->fail(
                'Should have thrown a DuplicatedEntryInDatabaseException'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            // OK.
        }
    }

    /**
     * User accepts previous policy but not latest one
     */
    public function testAcceptsPreviousPolicyButNotLatestOne() {
        // Create the user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create privacy policy
        \OmegaUp\Test\Factories\User::createPrivacyStatement();
        $latestPrivacyPolicy = \OmegaUp\Controllers\User::getPrivacyPolicyDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $response = \OmegaUp\Controllers\User::apiLastPrivacyPolicyAccepted(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'privacystatement_id' => $latestPrivacyPolicy,
            ])
        );

        $this->assertFalse(
            $response['hasAccepted'],
            'User should not have already accepted privacy policy'
        );

        $response = \OmegaUp\Controllers\User::apiAcceptPrivacyPolicy(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'privacy_git_object_id' => $latestPrivacyPolicy['git_object_id'],
                'statement_type' => $latestPrivacyPolicy['statement_type'],
            ])
        );

        $this->assertEquals($response['status'], 'ok');

        // Create other privacy policy
        \OmegaUp\Test\Factories\User::createPrivacyStatement();

        $response = \OmegaUp\Controllers\User::apiLastPrivacyPolicyAccepted(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ])
        );

        $this->assertFalse(
            $response['hasAccepted'],
            'User should not have already accepted privacy policy'
        );
    }
}
