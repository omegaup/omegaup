<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class SchoolCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Create school happy path
     */
    public function testCreateSchool() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);

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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString()
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);

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
