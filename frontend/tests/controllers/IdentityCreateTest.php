<?php

/**
 * Tests for apiCreate and apiBulkCreate in IdentityController
 *
 * @author juan.pablo
 */
require_once 'libs/FileHandler.php';

class IdentityCreateTest extends OmegaupTestCase {
    /**
     * Basic test for users from identity creator group
     */
    public function testIdentityHasContestOrganizerRole() {
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorIdentity = IdentitiesDAO::getByPK($creator->main_identity_id);
        $mentor = UserFactory::createMentorIdentity();
        $mentorIdentity = IdentitiesDAO::getByPK($mentor->main_identity_id);

        $isCreatorMember = Authorization::isGroupIdentityCreator($creatorIdentity->identity_id);
        // Asserting that user belongs to the  identity creator group
        $this->assertTrue($isCreatorMember);

        $isCreatorMember = Authorization::isGroupIdentityCreator($mentorIdentity->identity_id);
        // Asserting that user doesn't belong to the identity creator group
        $this->assertFalse($isCreatorMember);
    }

    /**
     * Basic test for creating a single identity
     */
    public function testCreateSingleIdentity() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        $identityName = substr(Utils::CreateRandomString(), - 10);
        // Call api using identity creator group member
        IdentityController::apiCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'username' => "{$group['group']->alias}:{$identityName}",
            'name' => $identityName,
            'password' => Utils::CreateRandomString(),
            'country_id' => 'MX',
            'state_id' => 'QUE',
            'gender' => 'male',
            'school_name' => Utils::CreateRandomString(),
            'group_alias' => $group['group']->alias,
        ]));

        $response = GroupController::apiMembers(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        $this->assertEquals(1, count($response['identities']));
    }

    /**
     * Test for creating an identity with wrong group
     */
    public function testCreateIdentityWithWrongGroup() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);
        $wrongGroupAlias = 'wrongGroupAlias';
        $identityName = substr(Utils::CreateRandomString(), - 10);
        // Call api using identity creator group member
        try {
            $response = IdentityController::apiCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => "{$wrongGroupAlias}:{$identityName}",
                'name' => $identityName,
                'password' => Utils::CreateRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => Utils::CreateRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Identity should not be created because group alias is not correct');
        } catch (InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Test for creating an identity without group
     */
    public function testCreateIdentityWithoutGroup() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);
        $identityName = substr(Utils::CreateRandomString(), - 10);
        // Call api using identity creator group member
        try {
            $response = IdentityController::apiCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $identityName,
                'name' => $identityName,
                'password' => Utils::CreateRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => Utils::CreateRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Identity should not be created because group alias must be included in username');
        } catch (InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Test for creating an identity with wrong username
     */
    public function testCreateIdentityWithWrongUsername() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);
        $wrongIdentityName = 'username:with:wrong:char';
        // Call api using identity creator group member
        try {
            $response = IdentityController::apiCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => "{$group['group']->alias}:{$wrongIdentityName}",
                'name' => $wrongIdentityName,
                'password' => Utils::CreateRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => Utils::CreateRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Identity should not be created because of the wrong username (Use of [:] is not allowed)');
        } catch (InvalidParameterException $e) {
            // OK.
        }
        $wrongIdentityName = 'wrongUsername';
        try {
            $response = IdentityController::apiCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'username' => $wrongIdentityName,
                'name' => $wrongIdentityName,
                'password' => Utils::CreateRandomString(),
                'country_id' => 'MX',
                'state_id' => 'QUE',
                'gender' => 'male',
                'school_name' => Utils::CreateRandomString(),
                'group_alias' => $group['group']->alias,
            ]));
            $this->fail('Identity should not be created because of the wrong username (Username needs include group_alias)');
        } catch (InvalidParameterException $e) {
            // OK.
        }
    }

    /**
     * Basic test for uploading csv file
     */
    public function testUploadCsvFile() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        // Call api using identity creator group member
        $response = IdentityController::apiBulkCreate(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => self::getCsvData('identities.csv', $group['group']->alias),
            'group_alias' => $group['group']->alias,
        ]));

        $response = GroupController::apiMembers(new Request([
            'auth_token' => $creatorLogin->auth_token,
            'group_alias' => $group['group']->alias,
        ]));

        $this->assertEquals(5, count($response['identities']));
    }

    /**
     * Test for uploading csv file with duplicated usernames
     * @throws DuplicatedEntryInDatabaseException
     */
    public function testUploadCsvFileWithDuplicatedUsernames() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        try {
            // Call api using identity creator group member
            $response = IdentityController::apiBulkCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => self::getCsvData('duplicated_identities.csv', $group['group']->alias),
                'group_alias' => $group['group']->alias,
            ]));
        } catch (DuplicatedEntryInDatabaseException $e) {
            // OK.
        }
    }

    /**
     * Test for uploading csv file with wrong country_id
     * @throws InvalidDatabaseOperationException
     */
    public function testUploadCsvFileWithWrongCountryId() {
        // Identity creator group member will upload csv file
        $creator = UserFactory::createGroupIdentityCreator();
        $creatorLogin = self::login($creator);
        $group = GroupsFactory::createGroup($creator, null, null, null, $creatorLogin);

        try {
            // Call api using identity creator group team member
            $response = IdentityController::apiBulkCreate(new Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => self::getCsvData('identities_wrong_country_id.csv', $group['group']->alias),
                'group_alias' => $group['group']->alias,
            ]));
        } catch (InvalidDatabaseOperationException $e) {
            // OK.
        }
    }

    /**
     * @param $file
     * @return $csv_data
     */
    private static function getCsvData($file, $group_alias) {
        $row = 0;
        $identities = [];
        $path_file = OMEGAUP_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
        $headers = fgetcsv($handle, 1000, ',');
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            array_push($identities, [
                'username' => "{$group_alias}:{$data[0]}",
                'name' => $data[1],
                'country_id' => $data[2],
                'state_id' => $data[3],
                'gender' => $data[4],
                'school_name' => $data[5],
                'password' => Utils::CreateRandomString(),
            ]);
        }
        fclose($handle);
        return $identities;
    }
}
