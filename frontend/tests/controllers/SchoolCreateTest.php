<?php

/**
 *
 * @author joemmanuel
 */

class SchoolCreateTest extends OmegaupTestCase {
    /**
     * Create school happy path
     */
    public function testCreateSchool() {
        $user = UserFactory::createUser();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString()
        ));

        // Call api
        $response = SchoolController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(SchoolsDAO::findByName($r['name'])));
    }

    /**
     *
     */
    public function testCreateSchoolDuplicatedName() {
        $user = UserFactory::createUser();

        $r = new Request(array(
            'auth_token' => self::login($user),
            'name' => Utils::CreateRandomString()
        ));

        // Call api
        $response = SchoolController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(SchoolsDAO::findByName($r['name'])));

        // Call api again
        $response = SchoolController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(SchoolsDAO::findByName($r['name'])));
    }
}
