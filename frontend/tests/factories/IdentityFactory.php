<?php

/**
 * IdentityFactory
 *
 * This class is a helper for identity actions
 *
 * @author juan.pablo
 */

class IdentityFactory {
    /**
     * @return array{username: string, name: string, country_id: string, state_id: string, gender: string, school_name: string, password: string}[]
     */
    public static function getCsvData(string $file, string $group_alias, string $password = '') : array {
        $row = 0;
        /** @var array{username: string, name: string, country_id: string, state_id: string, gender: string, school_name: string, password: string}[] */
        $identities = [];
        $path_file = OMEGAUP_TEST_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'identities');
        }
        $headers = fgetcsv($handle, 1000, ',');
        while (($data = fgetcsv($handle, 1000, ',')) !== false && !is_null($data)) {
            array_push($identities, [
                'username' => "{$group_alias}:{$data[0]}",
                'name' => strval($data[1]),
                'country_id' => strval($data[2]),
                'state_id' => strval($data[3]),
                'gender' => strval($data[4]),
                'school_name' => strval($data[5]),
                'password' => $password == '' ? Utils::CreateRandomString() : $password,
            ]);
        }
        fclose($handle);
        return $identities;
    }

    public static function createIdentitiesFromAGroup(
        \OmegaUp\DAO\VO\Groups $group,
        ScopedLoginToken $adminLogin,
        string $password
    ) : array {
        // Call api using identity creator group member
        IdentityController::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'identities' => IdentityFactory::getCsvData(
                'identities.csv',
                strval($group->alias),
                $password
            ),
            'group_alias' => $group->alias,
        ]));

        // Getting the identities members list
        $response = GroupController::apiMembers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group_alias' => $group->alias,
        ]));

        [$unassociatedIdentity, $associatedIdentity] = $response['identities'];
        $unassociatedIdentity = \OmegaUp\DAO\Identities::FindByUsername(
            $unassociatedIdentity['username']
        );
        $associatedIdentity = \OmegaUp\DAO\Identities::FindByUsername(
            $associatedIdentity['username']
        );

        $unassociatedIdentity->password = $password;
        $associatedIdentity->password = $password;
        return [$unassociatedIdentity, $associatedIdentity];
    }
}
