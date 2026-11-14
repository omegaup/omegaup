<?php
/**
 * Tests for apiCreate and apiBulkCreate in IdentityController
 */

class IdentityCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for users from identity creator group
     */
    public function testIdentityHasContestOrganizerRole() {
        [
            'identity' => $creatorIdentity
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        [
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

        $this->assertSame(1, count($response['identities']));

        // Check current school for Identity on IdentitiesSchools
        $school = \OmegaUp\DAO\Schools::findByName($schoolName);
        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $response['identities'][0]['username']
        );
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
            $identity->current_identity_school_id
        );
        $this->assertSame($school[0]->school_id, $identitySchool->school_id);
        $this->assertNull($identitySchool->end_time);
    }

    /**
     * Test for creating an identity with wrong group
     */
    public function testCreateIdentityWithWrongGroup() {
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
        $wrongGroupAlias = 'wrongGroupAlias';
        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        // Call api using identity creator group member
        try {
            \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
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
        $identityName = substr(\OmegaUp\Test\Utils::createRandomString(), - 10);
        // Call api using identity creator group member
        try {
            \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
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
        $wrongIdentityName = 'username:with:wrong:char';
        // Call api using identity creator group member
        try {
            \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
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
            \OmegaUp\Controllers\Identity::apiCreate(new \OmegaUp\Request([
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
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'duplicated_identities.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInArrayException $e) {
            $localizedText = \OmegaUp\Translations::getInstance()->get(
                'groupMemberUsernameInUse'
            );
            $errorMessage = \OmegaUp\ApiUtils::formatString(
                $localizedText,
                ['usernames' => join('<br />', $e->duplicatedItemsInArray)]
            );
            $this->assertStringContainsString(
                $e->getErrorMessage(),
                $errorMessage
            );
            $this->assertSame('groupMemberUsernameInUse', $e->getMessage());
        }
    }

    /**
     * Test for uploading csv file with a long school name
     */
    public function testUploadCsvFileWithLongSchoolName() {
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
            // Call api using identity creator group member
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'identities_long_school_name.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterStringTooLong', $e->getMessage());
            $this->assertSame('school_name', $e->parameter);
        }
    }

    /**
     * Test for uploading csv file with wrong country_id
     */
    public function testUploadCsvFileWithWrongCountryId() {
        // Identity creator group member will upload csv file
        ['identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
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
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'identities_wrong_country_id.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should not have allowed bulk user creation');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
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
            \OmegaUp\Controllers\Identity::apiBulkCreate(
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
            $this->assertSame(
                'parameterInvalidStateNeedsToBelongToCountry',
                $e->getMessage()
            );
        }
    }

    public function testUploadCsvFileWithEmptyStateAndSelectedCountry() {
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

        // Call api using identity creator group team member
        \OmegaUp\Controllers\Identity::apiBulkCreate(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'identities_no_states.csv',
                    $group['group']->alias
                ),
                'group_alias' => $group['group']->alias,
            ])
        );

        [
            'identities' => $identities,
        ] = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        $membersMapping = [
            [
                'name' => 'Identity One',
                'country_id' => 'MX',
                'state_id' => 'AGU',
            ],
            [
                'name' => 'Identity Two',
                'country_id' => 'MX',
                'state_id' => '',
            ],
            [
                'name' => 'Identity Three',
                'country_id' => 'MX',
                'state_id' => 'BCS',
            ],
            [
                'name' => 'Identity Four',
                'country_id' => 'MX',
                'state_id' => '',
            ],
            [
                'name' => 'Identity Five',
                'country_id' => 'US',
                'state_id' => '',
            ],
        ];

        foreach ($membersMapping as $key => $member) {
            $this->assertSame($member['name'], $identities[$key]['name']);
            $this->assertSame(
                $member['country_id'],
                $identities[$key]['country_id']
            );
            $this->assertSame(
                $member['state_id'],
                $identities[$key]['state_id'] ?? ''
            );
        }
    }

    /**
     * Basic test for login an identity
     */
    public function testLoginIdentity() {
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

        $user = \OmegaUp\DAO\Users::FindByUsername(
            "{$group['group']->alias}:{$identityName}"
        );
        $this->assertNull($user);
        $user = \OmegaUp\DAO\Users::FindByUsername($identityName);
        $this->assertNull($user);

        $identity = \OmegaUp\DAO\Identities::findByUsername(
            "{$group['group']->alias}:{$identityName}"
        );

        $this->assertSame($identityName, $identity->name);

        // Assert the log is empty.
        $this->assertSame(
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
        $this->assertSame(
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

        $this->assertSame(
            "{$group['group']->alias}:{$identityName}",
            $profileResponse['username']
        );
        $this->assertSame(
            $identityName,
            $profileResponse['name']
        );
    }

    /**
     * Test for uploading empty identities array in apiBulkCreate
     */
    public function testBulkCreateWithEmptyIdentities(): void {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            owner: $creatorIdentity,
            login: $creatorLogin,
        );

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => json_encode([]),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Should have thrown an InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('identities', $e->parameter);
        }
    }

    /**
     * A PHPUnit data provider for malformed identity entries in apiBulkCreate.
     *
     * @return array<string, array{0: mixed}>
     */
    public function malformedIdentityEntriesProvider(): array {
        return [
            'scalar string' => [['invalid_scalar_entry']],
            'scalar integer' => [[123]],
            'scalar null' => [[null]],
            'empty array' => [[[]]],
            'null username' => [[['username' => null]]],
            'integer username' => [[['username' => 123]]],
            'empty string username' => [[['username' => '']]],
            'whitespace username' => [[['username' => '   ']]],
        ];
    }

    /**
     * Test for uploading malformed identity entries in apiBulkCreate
     *
     * @param mixed $payload
     *
     * @dataProvider malformedIdentityEntriesProvider
     */
    public function testBulkCreateWithMalformedIdentityEntries($payload): void {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            owner: $creatorIdentity,
            login: $creatorLogin,
        );

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreate(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'identities' => json_encode($payload),
                    'group_alias' => $group['group']->alias,
                ])
            );
            $this->fail('Should have failed for malformed identity entry');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('identities', $e->parameter);
        }
    }

    /**
     * A PHPUnit data provider for invalid or missing required/typed fields in apiBulkCreate.
     *
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: string}>
     */
    public function invalidOrMissingRequiredFieldsProvider(): array {
        return [
            'missing password' => [
                [
                    'name' => 'Valid User',
                    'gender' => 'male',
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterEmpty',
                'password',
            ],
            'non-string password' => [
                [
                    'name' => 'Valid User',
                    'password' => 12345678,
                    'gender' => 'male',
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterEmpty',
                'password',
            ],
            'missing gender' => [
                [
                    'name' => 'Valid User',
                    'password' => 'validPass1234',
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterEmpty',
                'gender',
            ],
            'non-string gender' => [
                [
                    'name' => 'Valid User',
                    'password' => 'validPass1234',
                    'gender' => 1,
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterEmpty',
                'gender',
            ],
            'non-string name' => [
                [
                    'name' => ['invalid_array'],
                    'password' => 'validPass1234',
                    'gender' => 'male',
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterInvalid',
                'name',
            ],
            'non-string country_id' => [
                [
                    'name' => 'Valid User',
                    'password' => 'validPass1234',
                    'gender' => 'male',
                    'country_id' => ['MX'],
                    'state_id' => 'QUE',
                    'school_name' => 'Valid School',
                ],
                'parameterInvalid',
                'country_id',
            ],
            'non-string state_id' => [
                [
                    'name' => 'Valid User',
                    'password' => 'validPass1234',
                    'gender' => 'male',
                    'country_id' => 'MX',
                    'state_id' => ['QUE'],
                    'school_name' => 'Valid School',
                ],
                'parameterInvalid',
                'state_id',
            ],
            'non-string school_name' => [
                [
                    'name' => 'Valid User',
                    'password' => 'validPass1234',
                    'gender' => 'male',
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                    'school_name' => ['School'],
                ],
                'parameterInvalid',
                'school_name',
            ],
        ];
    }

    /**
     * Test for required and typed fields in apiBulkCreate
     *
     * @param array<string, mixed> $identity
     *
     * @dataProvider invalidOrMissingRequiredFieldsProvider
     */
    public function testBulkCreateWithInvalidOrMissingRequiredFields(
        array $identity,
        string $expectedMessage,
        string $expectedParameter
    ): void {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            owner: $creatorIdentity,
            login: $creatorLogin,
        );

        $identity['username'] = "{$group['group']->alias}:validuser";

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => json_encode([$identity]),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                "Should have failed with {$expectedMessage} for {$expectedParameter}"
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($expectedMessage, $e->getMessage());
            $this->assertSame($expectedParameter, $e->parameter);
        }
    }

    /**
     * Test verifying rollback on failure midway through bulk creation
     */
    public function testBulkCreateRollbackOnFailure(): void {
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            owner: $creatorIdentity,
            login: $creatorLogin,
        );

        $reflection = new \ReflectionClass(\OmegaUp\MySQLConnection::class);
        $countProp = $reflection->getProperty('_transactionCount');
        $countProp->setAccessible(true);
        $okProp = $reflection->getProperty('_transactionOk');
        $okProp->setAccessible(true);

        $conn = \OmegaUp\MySQLConnection::getInstance();
        $initialTxCount = $countProp->getValue($conn);
        $this->assertTrue($okProp->getValue($conn));

        $validUsername = "{$group['group']->alias}:validuser1";
        // Second identity has invalid group alias to trigger exception midway
        $invalidUsername = 'wronggroup:user2';

        $identities = [
            [
                'username' => $validUsername,
                'name' => 'Valid User 1',
                'password' => 'validPassword123',
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => 'Valid School',
            ],
            [
                'username' => $invalidUsername,
                'name' => 'Invalid User 2',
                'password' => 'validPassword123',
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'female',
                'school_name' => 'Valid School',
            ],
        ];

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => json_encode($identities),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail(
                'Should have failed due to invalid group alias'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('group_alias', $e->parameter);
        }

        // Verify that transaction count was restored (no transaction leak)
        $this->assertSame(
            $initialTxCount,
            $countProp->getValue($conn),
            'Transaction count should return to initial level (no leak)'
        );

        // Verify that transaction was marked as failed by transRollback()
        $this->assertFalse(
            $okProp->getValue($conn),
            'Transaction should be marked as failed by transRollback()'
        );
    }
}
