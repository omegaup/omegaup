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

        $user = UserFactory::createUser();
        $login = OmegaupTestCase::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
        ]);

        // Call api
        $response = SchoolController::apiCreate($r);

        return [
            'creator' => $user,
            'request' => $r,
            'response' => $response,
            'school' => SchoolsDAO::findByName($r['name'])[0]
        ];
    }

    /**
     * Add user to school
     * @param array $schoolData
     * @param UsersDAO $user
     */
    public static function addUserToSchool($schoolData, $user) {
        $login = OmegaupTestCase::login($user);
        $response = UserController::apiUpdate(new Request([
            'auth_token' => $login->auth_token,
            'school_id' => $schoolData['school']->school_id
        ]));
    }
}
