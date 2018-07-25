<?php

/**
 * Tests for apiCreate and apiBulkCreate in IdentityController
 *
 * @author juan.pablo
 */
require_once 'libs/FileHandler.php';

class IdentityCreateTest extends OmegaupTestCase {
    /**
     * Basic test for users with contest organizer role
     */
    public function testIdentityHasContestOrganizerRole() {
        $organizer = UserFactory::createContestOrganizerUser();
        $organizer_identity = IdentitiesDAO::getByPK($organizer->main_identity_id);
        $mentor = UserFactory::createMentorIdentity();
        $mentor_identity = IdentitiesDAO::getByPK($mentor->main_identity_id);

        $is_organizer_member = Authorization::isOrganizer($organizer_identity->identity_id);
        // Asserting that user belongs to the  contest organizer group
        $this->assertEquals(1, $is_organizer_member);

        $is_organizer_member = Authorization::isOrganizer($mentor_identity->identity_id);
        // Asserting that user doesn't belong to the  contest organizer group
        $this->assertNotEquals(1, $is_organizer_member);
    }

    /**
     * Basic test for uploading csv file
     */
    public function testUploadCsvFile() {
        // Contest organizer team member will upload csv file
        $organizer = UserFactory::createContestOrganizerUser();
        $organizerLogin = self::login($organizer);
        $group = GroupsFactory::createGroup($organizer, null, null, null, $organizerLogin);

        // Call api using contest organizer team member
        $response = IdentityController::apiBulkCreate(new Request([
            'auth_token' => $organizerLogin->auth_token,
            'identities' => self::getCsvData('identities.csv', $group['group']->alias),
            'group_alias' => $group['group']->alias,
        ]));
    }

    /**
     * Test for uploading csv file with duplicated usernames
     * @throws DuplicatedEntryInDatabaseException
     */
    public function testUploadCsvFileWithDuplicatedUsernames() {
        // Contest organizer team member will upload csv file
        $organizer = UserFactory::createContestOrganizerUser();
        $organizerLogin = self::login($organizer);
        $group = GroupsFactory::createGroup($organizer, null, null, null, $organizerLogin);

        try {
            // Call api using contest organizer team member
            $response = IdentityController::apiBulkCreate(new Request([
                'auth_token' => $organizerLogin->auth_token,
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
        // Contest organizer team member will upload csv file
        $organizer = UserFactory::createContestOrganizerUser();
        $organizerLogin = self::login($organizer);
        $group = GroupsFactory::createGroup($organizer, null, null, null, $organizerLogin);

        try {
            // Call api using contest organizer team member
            $response = IdentityController::apiBulkCreate(new Request([
                'auth_token' => $organizerLogin->auth_token,
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
        $headers = [];
        $path_file = OMEGAUP_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
        while (($data = fgetcsv($handle, 1000, ',')) != false) {
            if ($row === 0) {
                $headers = $data;
            } else {
                $identity = [];
                $identity['username'] = "{$group_alias}:{$data[0]}";
                $identity['name'] = $data[1];
                $identity['country_id'] = $data[2];
                $identity['state_id'] = $data[3];
                $identity['gender'] = $data[4];
                $identity['school_name'] = $data[5];
                $identity['password'] = Utils::CreateRandomString();
                array_push($identities, $identity);
            }
            $row++;
        }
        fclose($handle);
        return $identities;
    }
}
