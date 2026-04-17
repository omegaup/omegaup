<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Testing synchronization between User and Identity
 */
class UserIdentitySynchronizeTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Creates an omegaup user happily :)
     */
    public function testCreateUserPositive() {
        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => \OmegaUp\Test\Utils::createRandomString(),
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey,
            'birth_date' => 946684800, // 01-01-2000
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        // Check response
        $this->assertSame($r['username'], $response['username']);

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        $this->assertNotNull($user);
    }

    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'old_password' => $identity->password,
        ]);

        // Call api
        \OmegaUp\Controllers\User::apiChangePassword($r);

        // Try to login with old password, should fail
        try {
            self::login($identity);
            $this->fail('Reset password failed');
        } catch (Exception $e) {
            // We are OK
        }

        // Set new password and try again, should succeed
        $identity->password = $r['password'];
        self::login($identity);

        $user = \OmegaUp\DAO\Users::FindByUsername($identity->username);
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
    }

    /**
     * Update test
     */
    public function testUserUpdate() {
        // Create the user to edit
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $locale = \OmegaUp\DAO\Languages::getByName('pt');
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => 'new_username',
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
            'scholar_degree' => 'master',
            'birth_date' => strtotime('1988-01-01'),
            'graduation_date' => strtotime('2016-02-02'),
            'locale' => $locale->name,
        ]);

        \OmegaUp\Controllers\User::apiUpdate($r);

        // Check user/identity from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $r['auth_token']
        )['loginIdentity'];
        unset($identityDb['classname']);
        unset($identityDb['acting_identity_id']);
        $identityDb = new \OmegaUp\DAO\VO\Identities($identityDb);
        $graduationDate = null;
        if (!is_null($identityDb->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityDb->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $graduationDate = $identitySchool->graduation_date;
            }
        }

        $this->assertSame($r['name'], $identityDb->name);
        $this->assertSame($r['country_id'], $identityDb->country_id);
        $this->assertSame($r['state_id'], $identityDb->state_id);
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
        $this->assertSame($locale->language_id, $identityDb->language_id);

        // Edit all fields again with diff values
        $locale = \OmegaUp\DAO\Languages::getByName('pseudo');
        $states = \OmegaUp\DAO\States::getByCountry('US');
        $newName = \OmegaUp\Test\Utils::createRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $newName,
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
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $r['auth_token']
        )['loginIdentity'];
        unset($identityDb['classname']);
        unset($identityDb['acting_identity_id']);
        $identityDb = new \OmegaUp\DAO\VO\Identities($identityDb);
        $graduationDate = null;
        if (!is_null($identityDb->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityDb->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $graduationDate = $identitySchool->graduation_date;
            }
        }

        $this->assertSame($r['name'], $identityDb->name);
        $this->assertSame($r['country_id'], $identityDb->country_id);
        $this->assertSame($r['state_id'], $identityDb->state_id);
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
        $this->assertSame($locale->language_id, $identityDb->language_id);

        // Double check language update with the appropriate API
        $this->assertSame(
            $locale->name,
            \OmegaUp\Controllers\Identity::getPreferredLanguage($identityDb)
        );

        $identity = \OmegaUp\DAO\Identities::getByPK($userDb->main_identity_id);
        $this->assertSame($identity->username, $identityDb->username);
        $this->assertSame($identity->password, $identityDb->password);
    }

    /**
     * Update basic info test
     */
    public function testUserUpdateBasicInfo() {
        // Create the user to edit
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $newUsername = 'new_username_basic_info';
        $newPassword = \OmegaUp\Test\Utils::createRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $newUsername,
            'password' => $newPassword,
        ]);

        \OmegaUp\Controllers\User::apiUpdateBasicInfo($r);

        // Check user from db
        $userDb = \OmegaUp\DAO\AuthTokens::getUserByToken($r['auth_token']);
        $identityDb = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $r['auth_token']
        )['loginIdentity'];

        // Getting identity data from db
        $identity = \OmegaUp\DAO\Identities::getByPK($userDb->main_identity_id);
        $this->assertSame($identity->username, $identityDb['username']);
        $this->assertSame($identity->password, $identityDb['password']);
    }
}
