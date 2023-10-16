<?php
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
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}"));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}1"));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}2"));

        ob_start();
        try {
            // Create collision
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp1.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp2.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp3.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        ob_end_clean();

        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}"));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}1"));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}2"));
    }

    /**
     * User logged via google, try log in with native mode
     */
    public function testUserLoggedViaGoogleAndThenNativeMode() {
        $time = \OmegaUp\Time::get();
        $username = "X{$time}";
        $password = \OmegaUp\Test\Utils::createRandomString();

        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "{$username}@isp.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }

        $identity = \OmegaUp\DAO\Identities::findByUsername($username);

        // Users logged via google, facebook
        $this->assertNull($identity->password);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            // Try to create new user
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => $username,
                'password' => $password,
                'email' => "{$username}@isp.com",
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('mailInUse', $e->getMessage());
        }
    }

    /**
     * User logged via google, try log in with native mode, and
     * different username
     */
    public function testUserLoggedViaGoogleAndThenNativeModeWithDifferentUsername() {
        $time = \OmegaUp\Time::get();
        $username = "Y{$time}";
        $email = "{$username}@isp.com";

        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail($email);
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }

        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $identity = \OmegaUp\DAO\Identities::FindByUserId($user->user_id);
        $email_user = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        // Asserts that user has the initial username and email
        $this->assertSame($identity->username, $username);
        $this->assertSame($email, $email_user->email);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => "Z{$username}",
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => $email,
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        try {
            // Call API
            \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('mailInUse', $e->getMessage());
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
                'parent_email' => "{$randomString}@{$randomString}.com",
                'birth_date' => $under13BirthDateTimestamp,
            ]),
            $this->assertNotNull($under13BirthDateTimestamp)
        );
        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

        $this->assertNotNull($response->parental_verification_token);
    }

    /**
     * User logged in and a parental Token is not generated
     *
     */
    public function testUserDoToGenerateParentalTokenAtTimeOfRegistration() {
        // Verify that the token is not generated.
        $over13BirthDateTimestamp = strtotime('-15 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                 'username' => $randomString,
                 'password' => $randomString,
                 'email' => "{$randomString}@{$randomString}.com",
                 'birth_date' => $over13BirthDateTimestamp,
            ]),
            $this->assertNotNull($over13BirthDateTimestamp)
        );

        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

        $this->assertNull($response->parental_verification_token);
    }

    /**
     * User under 13 creates an account with the function in the factory, where
     * the account does not register an email
     */
    public function testUserUnder13CreatesAnAccount() {
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Creates a 10 years-old user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );

        $this->assertNull($user->main_email_id);
        $this->assertNotNull($user->parental_verification_token);
        $this->assertNotNull($user->parent_email_verification_initial);
        $this->assertNotNull($user->parent_email_verification_deadline);

        // Creates a normal user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $this->assertNotNull($user->main_email_id);
        $this->assertNull($user->parental_verification_token);
        $this->assertNull($user->parent_email_verification_initial);
        $this->assertNull($user->parent_email_verification_deadline);
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
                    'email' => "{$randomString}@{$randomString}.com",
                    'parent_email' => "{$randomString}@{$randomString}.com",
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
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Create a 10 years-old user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );
        [
            'user' => $parentUser,
            'identity' => $parentIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($parentIdentity);

        $payload = \OmegaUp\Controllers\User::getVerificationParentalTokenDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'parental_verification_token' => $user->parental_verification_token,
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($payload['hasParentalVerificationToken']);

        $updatedUser = \OmegaUp\DAO\Users::getByPK($user->user_id);

        // Assert both accounts are linked by email_id
        $this->assertSame(
            $updatedUser->parent_email_id,
            $parentUser->main_email_id
        );
    }
}
