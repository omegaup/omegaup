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

        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
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
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken($r['auth_token']);
        $this->assertEquals($r['name'], $identityDb->name);
        $this->assertEquals($r['country_id'], $identityDb->country_id);
        $this->assertEquals($r['state_id'], $identityDb->state_id);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $userDb->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $userDb->graduation_date);
        $this->assertEquals($locale->language_id, $identityDb->language_id);

        // Edit all fields again with diff values
        $locale = \OmegaUp\DAO\Languages::getByName('pseudo');
        $states = \OmegaUp\DAO\States::getByCountry('US');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'country_id' => $states[0]->country_id,
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'primary',
            'birth_date' => strtotime('2000-02-02'),
            'graduation_date' => strtotime('2026-03-03'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken($r['auth_token']);
        $this->assertEquals($r['name'], $identityDb->name);
        $this->assertEquals($r['country_id'], $identityDb->country_id);
        $this->assertEquals($r['state_id'], $identityDb->state_id);
        $this->assertEquals($r['scholar_degree'], $userDb->scholar_degree);
        $this->assertEquals(gmdate('Y-m-d', $r['birth_date']), $userDb->birth_date);
        $this->assertEquals(gmdate('Y-m-d', $r['graduation_date']), $userDb->graduation_date);
        $this->assertEquals($locale->language_id, $identityDb->language_id);

        // Double check language update with the appropiate API
        $r = new \OmegaUp\Request([
            'username' => $user->username
        ]);
        $this->assertEquals($locale->name, \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $r
        ));
    }

    /**
     * Value for the recruitment optin flag should be non-negative
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testNegativeStateUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            // Invalid state_id
            'state_id' => -1,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
    }

    /**
     * Update profile username with non-existence username
     */
    public function testUsernameUpdate() {
        $user = UserFactory::createUser();
        $login = self::login($user);
        $newUsername = Utils::CreateRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            //new username
            'username' => $newUsername
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);
        $user = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

        $this->assertEquals($identity->username, $newUsername);
    }

    /**
     * Update profile username with existed username
     * @expectedException \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public function testDuplicateUsernameUpdate() {
        $old_user = UserFactory::createUser();
        $user = UserFactory::createUser();
        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            //update username with existed username
            'username' => $old_user->username
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);
    }

     /**
     * Request parameter name cannot be too long
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testNameUpdateTooLong() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => 'TThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong ThisIsWayTooLong hisIsWayTooLong ',
            'country_id' => 'MX',
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
    }

    /**
     * Request parameter name cannot be empty
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testEmptyNameUpdate() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            // Invalid name
            'name' => '',
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testFutureBirthday() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'birth_date' => strtotime('2088-01-01'),
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'country_id' => $country_id,
        ]);

        try {
            \OmegaUp\Controllers\User::apiUpdate($r);
            $this->fail('All countries now have state information, so it must be provided.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => Utils::CreateRandomString(),
        ]);

        try {
            \OmegaUp\Controllers\User::apiUpdate($r);
            $this->fail('Please select a valid gender option');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
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

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => 'female',
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
    }

    /**
     * https://github.com/omegaup/omegaup/issues/1802
     * Test gender with valid default option null
     */
    public function testGenderWithNull() {
        // Create the user to edit
        $user = UserFactory::createUser();
        $login = self::login($user);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => null,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'gender' => '',
        ]);

        try {
            \OmegaUp\Controllers\User::apiUpdate($r);
            $this->fail('Please select a valid gender option');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // OK!
        }
    }

    /**
     * Tests that the user can generate a git token.
     */
    public function testGenerateGitToken() {
        $identity = UserFactory::createUser();
        $dbUser = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNull($dbUser->git_token);
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\User::apiGenerateGitToken(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertNotEquals($response['token'], '');

        $dbUser = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $this->assertNotNull($dbUser->git_token);
        $this->assertTrue(\OmegaUp\SecurityTools::compareHashedStrings($response['token'], $dbUser->git_token));
    }

    /**
     * Tests that users that have old hashes can migrate transparently to
     * Argon2id.
     */
    public function testOldHashTransparentMigration() {
        // Create the user and manually set its password to the well-known
        // 'omegaup' hash.
        $identity = UserFactory::createUser();
        $identity->password = '$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a';
        \OmegaUp\DAO\Identities::update($identity);
        $this->assertTrue(\OmegaUp\SecurityTools::isOldHash($identity->password));

        // After logging in, the password should have been updated.
        $identity->password = 'omegaup';
        self::login($identity);
        $identity = \OmegaUp\DAO\Identities::getByPK($identity->identity_id);
        $this->assertFalse(\OmegaUp\SecurityTools::isOldHash($identity->password));

        // After logging in once, the user should be able to log in again with
        // the exact same password.
        $identity->password = 'omegaup';
        self::login($identity);
    }
}
