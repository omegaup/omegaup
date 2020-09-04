<?php

namespace OmegaUp\Test\Factories;

class Schools {
    /**
     * Create a random school
     * @param  string $name
     * @return array{creator: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{school_id: int}, school: \OmegaUp\DAO\VO\Schools}
     */
    public static function createSchool($name = null) {
        if (is_null($name)) {
            $name = \OmegaUp\Test\Utils::createRandomString();
        }

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);
        [$school] = \OmegaUp\DAO\Schools::findByName($r->ensureString('name'));

        return [
            'creator' => $identity,
            'request' => $r,
            'response' => $response,
            'school' => $school,
        ];
    }

    /**
     * Add user to school
     * @param array{creator: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{school_id: int}, school: \OmegaUp\DAO\VO\Schools} $schoolData
     * @param \OmegaUp\DAO\VO\Identities $user
     */
    public static function addUserToSchool(
        $schoolData,
        $user
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login($user);
        $response = \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolData['school']->school_id
        ]));
    }
}
