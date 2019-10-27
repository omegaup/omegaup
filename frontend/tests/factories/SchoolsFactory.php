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
     * @return array
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

        return [
            'creator' => $identity,
            'request' => $r,
            'response' => $response,
            'school' => \OmegaUp\DAO\Schools::findByName($r['name'])[0]
        ];
    }

    /**
     * Add user to school
     * @param array $schoolData
     * @param \OmegaUp\DAO\Users $user
     */
    public static function addUserToSchool($schoolData, $user) {
        $login = OmegaupTestCase::login($user);
        $response = \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolData['school']->school_id
        ]));
    }
}
