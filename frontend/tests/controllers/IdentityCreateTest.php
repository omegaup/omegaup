<?php

/**
 * Tests for apiCreate and apiBulkCreate in IdentityController
 *
 * @author juan.pablo
 */

class IdentityCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for users from identity creator group
     */
    public function testIdentityHasContestOrganizerRole() {
        [
            'user' => $creator,
            'identity' => $creatorIdentity
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        [
            'user' => $mentorUser,
            'identity' => $mentorIdentity
        ] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        $isCreatorMember = \OmegaUp\Authorization::isGroupIdentityCreator(
            $creatorIdentity
        );
        // Asserting that user belongs to the  identity creator group
        $this->assertTrue($isCreatorMember);

        $isCreatorMember = \OmegaUp\Authorization::isGroupIdentityCreator(
            $mentorIdentity
        );
        // Asserting that user doesn't belong to the identity creator group
        $this->assertFalse($isCreatorMember);
    }

    /**
     * Basic test for creating a single identity
     */
    public function testCreateSingleIdentity() {
        // Identity creator group member will create the identity
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
        $schoolName = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => $schoolName,
            'group_alias' => $group['group']->alias,
        ]));

        $response = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        $this->assertEquals(1, count($response['identities']));

        // Check current school for Identity on IdentitiesSchools
        $school = \OmegaUp\DAO\Schools::findByName($schoolName);
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $response['identities'][0]['username']
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertEquals($school[0]->school_id, $identitySchool->school_id);
        $this->assertNull($identitySchool->end_time);
    }

    /**
     * Test for creating an identity with wrong group
     */
    public function testCreateIdentityWithWrongGroup() {
        // Identity creator group member will upload csv file
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );
        $wrongGroupAlias = 'wrongGroupAlias';
        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        // Call api using identity creator group member
        try {
            $response = \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => "{$wrongGroupAlias}:{$identityName}",
                'name' => $identityName,
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Identity should not be created because group alias is not correct'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Test for creating an identity without group
     */
    public function testCreateIdentityWithoutGroup() {
        // Identity creator group member will upload csv file
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
        // Call api using identity creator group member
        try {
            $response = \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $identityName,
                'name' => $identityName,
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Identity should not be created because group alias must be included in username'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Test for creating an identity with wrong username
     */
    public function testCreateIdentityWithWrongUsername() {
        // Identity creator group member will upload csv file
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );
        $wrongIdentityName = 'username:with:wrong:char';
        // Call api using identity creator group member
        try {
            $response = \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => "{$group['group']->alias}:{$wrongIdentityName}",
                'name' => $wrongIdentityName,
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Identity should not be created because of the wrong username (Use of [:] is not allowed)'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // OK.
        }
        $wrongIdentityName = 'wrongUsername';
        try {
            $response = \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $wrongIdentityName,
                'name' => $wrongIdentityName,
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Identity should not be created because of the wrong username (Username needs include group_alias)'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Basic test for uploading csv file
     */
    public function testUploadCsvFile() {
        // Identity creator group member will upload csv file
        [
            'user' => $creator,
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias,
            ),
            'group_alias' => $group['group']->alias,
        ]));
        $originalResponse = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertCount(5, $originalResponse['identities']);

        // Call api again, names should have changed
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities_updated.csv',
                $group['group']->alias,
            ),
            'group_alias' => $group['group']->alias,
        ]));
        $updatedResponse = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertCount(6, $updatedResponse['identities']);
        $this->assertNotEquals(
            $originalResponse['identities'],
            $updatedResponse['identities']
        );
    }

    public function testRemoveIdentitiesFromGroupAndAddThemAgain() {
        // Identity creator group member will upload csv file
        [
           'user' => $creator,
           'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias
            ),
            'group_alias' => $group['group']->alias,
        ]));
        $originalResponse = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertCount(5, $originalResponse['identities']);

        // Call api again, names should have changed
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities_updated.csv',
                $group['group']->alias
            ),
            'group_alias' => $group['group']->alias,
        ]));
        $updatedResponse = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertCount(6, $updatedResponse['identities']);
        $this->assertNotEquals(
            $originalResponse['identities'],
            $updatedResponse['identities']
        );

        // Now, we are going to remove all identities from the group
        foreach ($updatedResponse['identities'] as $identity) {
            \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'usernameOrEmail' => $identity['username'],
                'group_alias' => $group['group']->alias
            ]));
        }
        $removedMembers = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertEmpty($removedMembers['identities']);

        // Call api again, identities should appear in the group again
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group['group']->alias
            ),
            'group_alias' => $group['group']->alias,
        ]));
        $updatedMembers = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group['group']->alias,
            ])
        );
        $this->assertCount(5, $updatedMembers['identities']);
    }

    /**
     * Test for uploading csv file with duplicated usernames
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public function testUploadCsvFileWithDuplicatedUsernames() {
        // Identity creator group member will upload csv file
        [
            'user' => $creator,
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            // Call api using identity creator group member
            $response = \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'duplicated_identities.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('aliasInUse', $e->getMessage());
        }
    }

    /**
     * Test for uploading csv file with wrong country_id
     */
    public function testUploadCsvFileWithWrongCountryId() {
        // Identity creator group member will upload csv file
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            // Call api using identity creator group team member
            $response = \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'identities_wrong_country_id.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'parameterInvalidStateDoesNotBelongToCountry',
                $e->getMessage()
            );
        }
    }

    public function testUploadCsvFileWithEmptyCountryAndSelectedState() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            // Call api using identity creator group team member
            $response = \OmegaUp\Controllers\Identity::apiBulkCreate(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                        'identities_wrong_state_id.csv',
                        $group['group']->alias
                    ),
                    'group_alias' => $group['group']->alias,
                ])
            );
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'parameterInvalidStateNeedsToBelongToCountry',
                $e->getMessage()
            );
        }
    }

    /**
     * Basic test for login an identity
     */
    public function testLoginIdentity() {
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
        $identityPassword = \OmegaUp\Test\Utils::createRandomString();
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => $identityPassword,
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => \OmegaUp\Test\Utils::createRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $response = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        $user = \OmegaUp\DAO\Users::FindByUsername(
            "{$group['group']->alias}:{$identityName}"
        );
        $this->assertNull($user);
        $user = \OmegaUp\DAO\Users::FindByUsername($identityName);
        $this->assertNull($user);

        $identity = \OmegaUp\DAO\Identities::findByUsername(
            "{$group['group']->alias}:{$identityName}"
        );

        $this->assertEquals($identityName, $identity->name);

        // Assert the log is empty.
        $this->assertEquals(
            0,
            count(
                \OmegaUp\DAO\IdentityLoginLog::getByIdentity(
                    $identity->identity_id
                )
            )
        );

        // Call the API
        $loginResponse = \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identityPassword
        ]));

        $this->assertLogin($identity, $loginResponse['auth_token']);

        // Assert the log is not empty.
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\IdentityLoginLog::getByIdentity(
                    $identity->identity_id
                )
            )
        );

        $profileResponse = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $loginResponse['auth_token'],
            ])
        );

        $this->assertEquals(
            "{$group['group']->alias}:{$identityName}",
            $profileResponse['username']
        );
        $this->assertEquals(
            $identityName,
            $profileResponse['name']
        );
    }
}
