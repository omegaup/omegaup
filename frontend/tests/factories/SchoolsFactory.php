<?php

/**
 * SchoolsFactory
 *
 * This class is a helper for creating schools as needed in other places
 *
 * @author joemmanuel
 */
class SchoolsFactory {
    /**
     * Create a random school
     * @param  string $name
     * @return array{creator: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string, school_id: int}, school: \OmegaUp\DAO\VO\Schools}
     */
    public static function createSchool($name = null) {
        if (is_null($name)) {
            $name = Utils::CreateRandomString();
        }

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = OmegaupTestCase::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
        ]);

        // Call api
        $response = \OmegaUp\Controllers\School::apiCreate($r);
        [$school] = \OmegaUp\DAO\Schools::findByName(strval($r['name']));

        return [
            'creator' => $identity,
            'request' => $r,
            'response' => $response,
            'school' => $school,
        ];
    }

    /**
     * Add user to school
     * @param array{creator: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, response: array{status: string, school_id: int}, school: \OmegaUp\DAO\VO\Schools} $schoolData
     * @param \OmegaUp\DAO\VO\Identities $user
     */
    public static function addUserToSchool(
        $schoolData,
        $user
    ) {
        $login = \OmegaupTestCase::login($user);
        $response = \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolData['school']->school_id
        ]));
    }
}
