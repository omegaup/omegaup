<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for apiUpdate and apiChangePassword in IdentityController
 */

class IdentityUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for updating a single identity
     */
    public function testUpdateSingleIdentity() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);

        $this->assertSame($username, $identity->username);
        $this->assertSame($identityName, $identity->name);
        $this->assertSame('MX', $identity->country_id);
        $this->assertSame('QUE', $identity->state_id);
        $this->assertSame('male', $identity->gender);

        $newIdentityName = substr(
            \OmegaUp\Test\Utils::createRandomString(),
            - 10
        );
        \OmegaUp\Controllers\Identity::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $newIdentityName,
            'country_id' => 'US',
            'state_id' => 'CA',
            'gender' => 'female',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
            'original_username' => $identity->username,
        ]));

        $newIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $username
        );

        $this->assertNotEquals($newIdentity->name, $identity->name);
        $this->assertNotEquals($newIdentity->country_id, $identity->country_id);
        $this->assertNotEquals($newIdentity->state_id, $identity->state_id);
        $this->assertNotEquals($newIdentity->gender, $identity->gender);
        $this->assertNotEquals(
            $newIdentity->current_identity_school_id,
            $identity->current_identity_school_id
        );
    }

    /**
     * Test for updating IdentitySchool on IdentityUpdate
     */
    public function testIdentitySchoolUpdate() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $schoolName = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => $schoolName,
            'group_alias' => $group['group']->alias,
        ]));

        $school = \OmegaUp\DAO\Schools::findByName($schoolName);

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($school[0]->school_id, $identitySchool->school_id);
        $this->assertNull($identitySchool->end_time);

        // Update the Identity, but preserve the same school
        $newIdentityName = substr(
            \OmegaUp\Test\Utils::createRandomString(),
            - 10
        );
        \OmegaUp\Controllers\Identity::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $newIdentityName,
            'country_id' => 'US',
            'state_id' => 'CA',
            'gender' => 'female',
            'school_name' => $schoolName,
            'group_alias' => $group['group']->alias,
            'original_username' => $identity->username,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($school[0]->school_id, $identitySchool->school_id);
        $this->assertNull($identitySchool->end_time);

        // Now update the school for Identity
        $newSchoolName = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Identity::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $newIdentityName,
            'country_id' => 'US',
            'state_id' => 'CA',
            'gender' => 'female',
            'school_name' => $newSchoolName,
            'group_alias' => $group['group']->alias,
            'original_username' => $identity->username,
        ]));

        // Verify that the end time is not null from previous IdentitySchool record
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $previousIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identitySchool->identity_school_id
        );
        $this->assertNotNull($previousIdentitySchool->end_time);

        $newSchool = \OmegaUp\DAO\Schools::findByName($newSchoolName);

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame(
            $newSchool[0]->school_id,
            $identitySchool->school_id
        );
        $this->assertNull($identitySchool->end_time);
    }

    /**
     * Test for updating a no-main identity's username
     */
    public function testUpdateNoMainIdentityUsername() {
        // Identity creator group member will create an identity
        ['user' => $user, 'identity' => $creator] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::CreateRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $password = \OmegaUp\Test\Utils::CreateRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $password,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $password;
        $login = self::login($identity);
        $newUsername = 'newname';

        try {
            \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                //new username
                'username' => $newUsername
            ]));
            $this->fail('User should not be able to change username');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Test for changing identity password
     */
    public function testChangePasswordIdentity() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $originalPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $originalPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;
        $identityLogin = self::login($identity);

        // Changing password
        $newPassword = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Identity::apiChangePassword(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'password' => $newPassword,
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $newPassword;

        $identityLogin = self::login($identity);
    }

    /**
     * Test for changing no-main identity password
     */
    public function testChangePasswordNoMainIdentity() {
        // Identity creator group member will create an identity
        ['user' => $user, 'identity' => $creator] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::CreateRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $originalPassword = \OmegaUp\Test\Utils::CreateRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $originalPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;
        $identityLogin = self::login($identity);
        $newPassword = 'anypassword';

        try {
            \OmegaUp\Controllers\User::apiUpdateBasicInfo(new \OmegaUp\Request([
                'auth_token' => $identityLogin->auth_token,
                'username' => $username,
                'password' => $newPassword,
            ]));
            $this->fail('User should not be able to change password');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Test for changing identity password and trying logging with old password
     */
    public function testChangePasswordIdentityAndLoginWithOldPassword() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $originalPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $originalPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;
        $identityLogin = self::login($identity);

        // Changing password
        $newPassword = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Identity::apiChangePassword(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'password' => $newPassword,
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;

        try {
            $identityLogin = self::login($identity);
            $this->fail('Identity can not login with old password');
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertSame($e->getMessage(), 'usernameOrPassIsWrong');
        }
    }

    /**
     * Test for changing identity password from another group
     */
    public function testChangePasswordIdentityFromOtherGroup() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        ['user' => $creator2, 'identity' => $creatorIdentity2] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin2 = self::login($creatorIdentity2);

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $originalPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $originalPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;
        $identityLogin = self::login($creatorIdentity);

        try {
            // Trying to change the password, it must fail
            $newPassword = \OmegaUp\Test\Utils::createRandomString();
            \OmegaUp\Controllers\Identity::apiChangePassword(new \OmegaUp\Request([
                'auth_token' => $creatorLogin2->auth_token,
                'username' => $username,
                'password' => $newPassword,
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Creators are not authorized to change passwords from other groups they do not belong'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Test for trying change identity password with invalid user
     */
    public function testChangePasswordIdentityWithInvalidUser() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        $username = "{$group['group']->alias}:{$identityName}";
        $originalPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => $username,
            'name' => $identityName,
            'password' => $originalPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity($username);
        $identity->password = $originalPassword;
        $identityLogin = self::login($creatorIdentity);

        // Normal user will try change the password of an identity
        ['user' => $normalUser, 'identity' => $normalIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($normalIdentity);

        try {
            // Changing password
            $newPassword = \OmegaUp\Test\Utils::createRandomString();
            \OmegaUp\Controllers\Identity::apiChangePassword(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'username' => $username,
                'password' => $newPassword,
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('User is not allowed to change password');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }
}
