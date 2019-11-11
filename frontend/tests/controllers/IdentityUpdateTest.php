<?php

/**
 * Tests for apiUpdate and apiChangePassword in IdentityController
 *
 * @author juan.pablo
 */

class IdentityUpdateTest extends OmegaupTestCase {
    /**
     * Basic test for updating a single identity
     */
    public function testUpdateSingleIdentity() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = GroupsFactory::createGroup(
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

        $this->assertEquals($username, $identity->username);
        $this->assertEquals($identityName, $identity->name);
        $this->assertEquals('MX', $identity->country_id);
        $this->assertEquals('QUE', $identity->state_id);
        $this->assertEquals('male', $identity->gender);

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
        $this->assertNotEquals($newIdentity->school_id, $identity->school_id);
    }

    /**
     * Test for changing identity password
     */
    public function testChangePasswordIdentity() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = GroupsFactory::createGroup(
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
     * Test for changing identity password and trying logging with old password
     */
    public function testChangePasswordIdentityAndLoginWithOldPassword() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = GroupsFactory::createGroup(
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
            $this->assertEquals($e->getMessage(), 'usernameOrPassIsWrong');
        }
    }

    /**
     * Test for changing identity password from another group
     */
    public function testChangePasswordIdentityFromOtherGroup() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = GroupsFactory::createGroup(
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
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }

    /**
     * Test for trying change identity password with invalid user
     */
    public function testChangePasswordIdentityWithInvalidUser() {
        // Identity creator group member will create an identity
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = GroupsFactory::createGroup(
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

        // Normal user will try change the passowrd of an identity
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
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }
}
