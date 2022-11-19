<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Testing new user special cases
 */
class UserRegistrationTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     *  Scenario:
     *      user A creates a new native account :
     *          username=A email=A@example.com
     *
     *      user B logs in with fb/google:
     *          email=A@gmail.com
     */
    public function testUserNameCollision() {
        $salt = \OmegaUp\Time::get();

        // Test users should not exist
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername('A' . $salt));
        $this->assertNull(
            \OmegaUp\DAO\Users::FindByUsername(
                'A' . $salt . '1'
            )
        );
        $this->assertNull(
            \OmegaUp\DAO\Users::FindByUsername(
                'A' . $salt . '2'
            )
        );

        // Create collision
        \OmegaUp\Controllers\Session::LoginViaGoogle('A' . $salt . '@isp1.com');
        \OmegaUp\Controllers\Session::LoginViaGoogle('A' . $salt . '@isp2.com');
        \OmegaUp\Controllers\Session::LoginViaGoogle('A' . $salt . '@isp3.com');

        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername('A' . $salt));
        $this->assertNotNull(
            \OmegaUp\DAO\Users::FindByUsername(
                'A' . $salt . '1'
            )
        );
        $this->assertNotNull(
            \OmegaUp\DAO\Users::FindByUsername(
                'A' . $salt . '2'
            )
        );
    }

    /**
     * User logged via google, try log in with native mode
     */
    public function testUserLoggedViaGoogleAndThenNativeMode() {
        $username = 'X' . \OmegaUp\Time::get();
        $password = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\Controllers\Session::LoginViaGoogle($username . '@isp.com');
        $identity = \OmegaUp\DAO\Identities::findByUsername($username);

        // Users logged via google, facebook
        $this->assertNull($identity->password);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => $username,
            'password' => $password,
            'email' => $username . '@isp.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        try {
            // Try to create new user
            $response = \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('mailInUse', $e->getMessage());
        }
    }

    /**
     * User logged via google, try log in with native mode, and
     * different username
     */
    public function testUserLoggedViaGoogleAndThenNativeModeWithDifferentUsername() {
        $username = 'Y' . \OmegaUp\Time::get();
        $email = $username . '@isp.com';

        \OmegaUp\Controllers\Session::LoginViaGoogle($email);
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $identity = \OmegaUp\DAO\Identities::FindByUserId($user->user_id);
        $email_user = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        // Asserts that user has the initial username and email
        $this->assertEquals($identity->username, $username);
        $this->assertEquals($email, $email_user->email);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => 'Z' . $username,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => $email,
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        try {
            // Call API
            $response = \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('mailInUse', $e->getMessage());
        }
    }

     /**
     * user13 logged in and a parental Token is generated
     *
     */
    public function testUser13ToGenerateParentalTokenAtTimeOfRegistration() {
        // Verify that the token is generated.
        $under13BirthDateTimestamp = strtotime('-10 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                'username' => $randomString,
                'password' => $randomString,
                'parent_email' => $randomString . '@' . $randomString . '.com',
                'birth_date' => $under13BirthDateTimestamp,
            ]),
            $this->assertNotNull($under13BirthDateTimestamp)
        );
        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

        $this->assertNotNull($response->parental_verification_token);
    }

    /**
     * user logged in and a parental Token is not generated
     *
     */
    public function testUserDoToGenerateParentalTokenAtTimeOfRegistration() {
         //Verify that the token is not generated.
         $over13BirthDateTimestamp = strtotime('-15 years');
         $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                 'username' => $randomString,
                 'password' => $randomString,
                 'email' => $randomString . '@' . $randomString . '.com',
                 'birth_date' => $over13BirthDateTimestamp,
            ]),
            $this->assertNotNull($over13BirthDateTimestamp)
        );

         $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

         $this->assertNull($response->parental_verification_token);
    }

    /**
     *  User registration fails due to both email address were provided
     *
     */
    public function testUserParentalTokenNotGeneratedDueInvalidParameters() {
        $over13BirthDateTimestamp = strtotime('-15 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        try {
            \OmegaUp\Controllers\User::apiCreate(
                new \OmegaUp\Request([
                    'username' => $randomString,
                    'password' => $randomString,
                    'email' => $randomString . '@' . $randomString . '.com',
                    'parent_email' => $randomString . '@' . $randomString . '.com',
                    'birth_date' => $over13BirthDateTimestamp,
                ])
            );
            $this->fail(
                'User should have not been able to be created because it is not valid provide both email and parent_email'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * User 13 and its account link to the parent account
     * on verification of parental token
     */
    public function testUse13LinkedToParentAccountWhenTokenVerificationDone() {
        //Created User13
        $under13BirthDateTimestamp = strtotime('-12 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
            'username' => $randomString,
            'password' => $randomString,
            'birth_date' => $under13BirthDateTimestamp,
            ])
        );
        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);
        \OmegaUp\Controllers\User::getVerificationParentalTokenDetailsForTypeScript(
            $response->parental_verification_token
        );
        //verify token belongs to child accounts ---?

        //assert the message.
        $this->assertEquals(
            "Parent's account is linked to their child's account"
        );
    }
}
