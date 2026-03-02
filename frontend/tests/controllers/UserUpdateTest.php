<?php
/**
 * Description of UpdateContest
 */
class UserUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $r['auth_token']
        )['loginIdentity'];
        $graduationDate = null;
        if (!is_null($identityDb['current_identity_school_id'])) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityDb['current_identity_school_id']
            );
            if (!is_null($identitySchool)) {
                $graduationDate = $identitySchool->graduation_date;
            }
        }

        $this->assertSame($r['name'], $identityDb['name']);
        $this->assertSame($r['country_id'], $identityDb['country_id']);
        $this->assertSame($r['state_id'], $identityDb['state_id']);
        $this->assertSame($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertSame(
            gmdate(
                'Y-m-d',
                $r['birth_date']
            ),
            $userDb->birth_date
        );
        // Graduation date without school is not saved on database.
        $this->assertNull($graduationDate);
        $this->assertSame($locale->language_id, $identityDb['language_id']);

        // Edit all fields again with diff values
        $locale = \OmegaUp\DAO\Languages::getByName('pseudo');
        $states = \OmegaUp\DAO\States::getByCountry('US');
        $token = $login->auth_token;
        $r = new \OmegaUp\Request([
            'auth_token' => $token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => $states[0]->country_id,
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'primary',
            'birth_date' => strtotime('2000-02-02'),
            'graduation_date' => strtotime('2026-03-03'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($token);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $token
        )['loginIdentity'];
        $graduationDate = null;
        if (!is_null($identityDb['current_identity_school_id'])) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityDb['current_identity_school_id']
            );
            if (!is_null($identitySchool)) {
                $graduationDate = $identitySchool->graduation_date;
            }
        }

        $this->assertSame($r['name'], $identityDb['name']);
        $this->assertSame($r['country_id'], $identityDb['country_id']);
        $this->assertSame($r['state_id'], $identityDb['state_id']);
        $this->assertSame($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertSame(
            gmdate(
                'Y-m-d',
                $r['birth_date']
            ),
            $userDb->birth_date
        );
        // Graduation date without school is not saved on database.
        $this->assertNull($graduationDate);
        $this->assertSame($locale->language_id, $identityDb['language_id']);

        // Double check language update with the appropriate API
        $identity = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $token
        )['loginIdentity'];
        unset($identity['acting_identity_id']);
        unset($identity['classname']);
        $this->assertSame(
            $locale->name,
            \OmegaUp\Controllers\Identity::getPreferredLanguage(
                new \OmegaUp\DAO\VO\Identities($identity)
            )
        );
    }

    public function testFillAllProfileFields() {
        // Create the user to edit
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $token = $login->auth_token;
        $r = new \OmegaUp\Request([
            'auth_token' => $token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'gender' => 'female',
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1998-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'preferred_language' => 'c',
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        $user = \OmegaUp\DAO\Users::getByPK($user->user_id);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

        $profileProgress = \OmegaUp\Controllers\User::getProfileProgress(
            $user
        );
        $this->assertSame(100.0, $profileProgress);
    }

    /**
     * Testing the modifications on IdentitiesSchools
     * on each user information update
     */
    public function testUpdateUserSchool() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // On user creation, no IdentitySchool is created
        $this->assertNull($identity->current_identity_school_id);

        // Now update user, adding a new school without graduation_date
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($identitySchool->school_id, $school->school_id);
        $this->assertNull($identitySchool->graduation_date);
        $this->assertNull($identitySchool->end_time);

        // Now call API again but to assign graduation_date
        $graduationDate = '2019-05-11';
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'graduation_date' => $graduationDate,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($identitySchool->school_id, $school->school_id);
        $this->assertSame($identitySchool->graduation_date, $graduationDate);
        $this->assertNull($identitySchool->end_time);

        // Now assign a new School to User
        $newSchool = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $newSchool->school_id,
        ]));

        // Previous IdentitySchool should have end_time filled.
        $previousIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identitySchool->identity_school_id
        );
        $this->assertNotNull($previousIdentitySchool->end_time);

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($identitySchool->school_id, $newSchool->school_id);
        $this->assertSame($identitySchool->graduation_date, $graduationDate);
        $this->assertNull($identitySchool->end_time);

        // Update the school one more time, set the first school again
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school->school_id,
        ]));

        // Previous IdentitySchool should have end_time filled.
        $previousIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identitySchool->identity_school_id
        );
        $this->assertNotNull($previousIdentitySchool->end_time);

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($identitySchool->school_id, $school->school_id);
        $this->assertNull($identitySchool->end_time);
    }

    /**
     * Testing the modifications on IdentitiesSchools
     * on each user information update
     */
    public function testUpdateUserWithNewSchool() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // On user creation, no IdentitySchool is created
        $this->assertNull($identity->current_identity_school_id);

        $schoolName = 'New school';

        // Update user, adding a new school with value 0 in school_id
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => 0,
            'school_name' => $schoolName,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );

        $school = \OmegaUp\DAO\Schools::getByPK($identitySchool->school_id);

        $this->assertSame($school->name, $schoolName);

        // Update user, adding a new school with no value in school_id
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_name' => $schoolName,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $identity->username
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );

        $school = \OmegaUp\DAO\Schools::getByPK($identitySchool->school_id);

        $this->assertSame($school->name, $schoolName);

        // Update user, adding a new school with null value in school_id should
        // throw an exception
        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'school_id' => null,
                'school_name' => $schoolName,
            ]));
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }

    /**
     * Update profile username with non-existence username
     */
    public function testUsernameUpdate() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $newUsername = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $newUsername
        ]));
        $user = \OmegaUp\DAO\AuthTokens::getUserByToken($login->auth_token);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

        $this->assertSame($identity->username, $newUsername);
    }

    /**
     * Update profile username with existed username
     */
    public function testDuplicateUsernameUpdate() {
        ['identity' => $oldIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                //update username with existed username
                'username' => $oldIdentity->username
            ]));
            $this->fail('Should not have been able to use duplicate username');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('usernameInUse', $e->getMessage());
        }
    }

     /**
     * Request parameter name cannot be too long
     */
    public function testNameUpdateTooLong() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                // Invalid name
                'name' => 'TThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong hisIsWayTooLong ',
            ]));
            $this->fail('Update should have failed due to name too long');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterStringTooLong', $e->getMessage());
            $this->assertSame('name', $e->parameter);
        }
    }

    /**
     * Request parameter name cannot be empty
     */
    public function testEmptyNameUpdate() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => '',
            ]));
            $this->fail('Update should have failed due to empty name');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterStringTooShort', $e->getMessage());
            $this->assertSame('name', $e->parameter);
        }
    }

    public function testFutureBirthday() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'birth_date' => strtotime('2088-01-01'),
            ]));
            $this->fail('Update should have failed due to future birthday');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('birthdayInTheFuture', $e->getMessage());
            $this->assertSame('birth_date', $e->parameter);
        }
    }

    /**
     * https://github.com/omegaup/omegaup/issues/997
     * Superseded by https://github.com/omegaup/omegaup/issues/1228
     */
    public function testUpdateCountryWithNoStateData() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'country_id' => 'MX',
            ]));
            $this->fail(
                'All countries now have state information, so it must be provided.'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
            $this->assertSame('state_id', $e->parameter);
        }
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with invalid gender option
     */
    public function testGenderWithInvalidOption() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'gender' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Please select a valid gender option');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotInExpectedSet', $e->getMessage());
            $this->assertSame('gender', $e->parameter);
        }
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with valid gender option
     */
    public function testGenderWithValidOption() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => 'female',
        ]));
        $this->assertSame('ok', $response['status']);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with valid default option null
     */
    public function testGenderWithNull() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => null,
        ]));
        $this->assertSame('ok', $response['status']);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with invalid gender option
     */
    public function testGenderWithEmptyString() {
        // Create the user to edit
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'gender' => '',
            ]));
            $this->fail('Please select a valid gender option');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotInExpectedSet', $e->getMessage());
            $this->assertSame('gender', $e->parameter);
        }
    }

    /**
     * Tests that the user can generate a git token.
     */
    public function testGenerateGitToken() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $this->assertNull($user->git_token);
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiGenerateGitToken(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertNotEquals($response['token'], '');

        $dbUser = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNotNull($dbUser->git_token);
        $this->assertTrue(
            \OmegaUp\SecurityTools::compareHashedStrings(
                $response['token'],
                $dbUser->git_token
            )
        );
    }

    /**
     * Tests that users that have old hashes can migrate transparently to
     * Argon2id.
     */
    public function testOldHashTransparentMigration() {
        // Create the user and manually set its password to the well-known
        // 'omegaup' hash.
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $identity->password = '$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a';
        \OmegaUp\DAO\Identities::update($identity);
        $user->password = $identity->password;
        \OmegaUp\DAO\Users::update($user);
        $this->assertTrue(
            \OmegaUp\SecurityTools::isOldHash(
                $identity->password
            )
        );

        // After logging in, the password should have been updated.
        $identity->password = 'omegaup';
        self::login($identity);
        $identity = \OmegaUp\DAO\Identities::getByPK($identity->identity_id);
        $this->assertFalse(
            \OmegaUp\SecurityTools::isOldHash(
                $identity->password
            )
        );

        // After logging in once, the user should be able to log in again with
        // the exact same password.
        $identity->password = 'omegaup';
        self::login($identity);
    }

    /**
     * Test update objectives
     */
    public function testUpdateObjectives() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => true,
            'has_scholar_objective' => false,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $this->assertSame(
            $r['has_competitive_objective'],
            $userDb->has_competitive_objective
        );
        $this->assertSame(
            $r['has_learning_objective'],
            $userDb->has_learning_objective
        );
        $this->assertSame(
            $r['has_scholar_objective'],
            $userDb->has_scholar_objective
        );
        $this->assertSame(
            $r['has_teaching_objective'],
            $userDb->has_teaching_objective
        );

        // Edit objectives again with diff values
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => false,
            'has_scholar_objective' => true,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from apiProfile
        $r2 = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username
        ]);
        $response = \OmegaUp\Controllers\User::apiProfile($r2);

        $this->assertSame(
            $r['has_competitive_objective'],
            $response['has_competitive_objective']
        );
        $this->assertSame(
            $r['has_learning_objective'],
            $response['has_learning_objective']
        );
        $this->assertSame(
            $r['has_scholar_objective'],
            $response['has_scholar_objective']
        );
        $this->assertSame(
            $r['has_teaching_objective'],
            $response['has_teaching_objective']
        );
    }

    /**
     * Test objectives cannot be null
     */
    public function testObjectivesWithNull() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'has_competitive_objective' => true,
                'has_learning_objective' => true,
                'has_scholar_objective' => true,
                'has_teaching_objective' => null,
            ]));
            $this->fail('Update should have failed due to empty objectives');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
            $this->assertSame('has_teaching_objective', $e->parameter);
        }
    }

    public function testGetSelfEmailDetailsForTypeScript() {
        $userInformation = [
            'username' => 'original_username',
            'name' => 'Original Name',
            'email' => 'original@email.com',
        ];
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams($userInformation)
        );

        $login = self::login($identity);

        $payload = \OmegaUp\Controllers\User::getEmailEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        $this->assertSame($payload['email'], $userInformation['email']);
        $this->assertSame(
            $payload['profile']['username'],
            $userInformation['username']
        );
        $this->assertSame(
            $payload['profile']['name'],
            $userInformation['name']
        );
    }

    public function testUpdateUserMainEmailAsNonSysAdmin() {
        $userToGetInformation = [
            'username' => 'username_to_get_info',
            'name' => 'Name To Get Info',
            'email' => 'email_to_get_info@email.com',
        ];
        [
            'identity' => $identityToGetInformation,
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams($userToGetInformation)
        );

        // Create user who attempts get the information of previously registered
        // user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::getEmailEditDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $identityToGetInformation->username,
                ])
            );
            $this->fail(
                'Non-admin user should not be able to get user details'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testUpdateUserMainEmailAsSysAdmin() {
        //createAdminUser
        $userToGetInformation = [
            'username' => 'username_to_get_info',
            'name' => 'Name To Get Info',
            'email' => 'email_to_get_info@email.com',
        ];
        [
            'identity' => $identityToGetInformation,
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams($userToGetInformation)
        );

        // Create user with sysadmin privileges
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($identity);

        $payload = \OmegaUp\Controllers\User::getEmailEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identityToGetInformation->username,
            ])
        )['templateProperties']['payload'];

        $this->assertSame($payload['email'], $userToGetInformation['email']);
        $this->assertSame(
            $payload['profile']['username'],
            $identityToGetInformation->username
        );
        $this->assertSame(
            $payload['profile']['name'],
            $identityToGetInformation->name
        );
    }
}
