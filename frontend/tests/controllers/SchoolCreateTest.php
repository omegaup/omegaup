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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Schools::findByName(
                    $r['name']
                )
            )
        );
    }

    /**
     *
     */
    public function testCreateSchoolDuplicatedName() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString()
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Schools::findByName(
                    $r['name']
                )
            )
        );

        // Call api again
        $response = \OmegaUp\Controllers\School::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Schools::findByName(
                    $r['name']
                )
            )
        );
    }
}
