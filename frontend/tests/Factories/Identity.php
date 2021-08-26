<?php

namespace OmegaUp\Test\Factories;

class Identity {
    public static function getCsvData(
        string $file,
        string $group_alias,
        string $password = '',
        bool $forTeams = false
    ): string {
        /** @var list<array{username: string, name: string, country_id: string, state_id: string, gender: string, school_name: string, password: string, usernames: string}> */
        $identities = [];
        $path_file = OMEGAUP_TEST_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
        $_headers = fgetcsv($handle, 1000, ',');
        while (
            ($data = fgetcsv(
                $handle,
                1000,
                ','
            )) !== false &&
            !is_null($data)
        ) {
            $username = $forTeams ? "teams:{$group_alias}:{$data[0]}" : "{$group_alias}:{$data[0]}";
            array_push($identities, [
                'username' => $username,
                'name' => strval($data[1]),
                'country_id' => strval($data[2]),
                'state_id' => strval($data[3]),
                'gender' => strval($data[4]),
                'school_name' => strval($data[5]),
                'password' => $password == '' ? \OmegaUp\Test\Utils::createRandomString() : $password,
                'usernames' => isset($data[6]) ? strval($data[6]) : null,
            ]);
        }
        fclose($handle);

        return json_encode($identities);
    }

    /**
     * @return list<string>
     */
    public static function getUsernamesInCsvFile(string $file): array {
        $usernames = [];
        $path_file = OMEGAUP_TEST_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'identities'
            );
        }
        $_headers = fgetcsv($handle, 1000, ',');
        while (
            ($data = fgetcsv(
                $handle,
                1000,
                ','
            )) !== false &&
            !is_null($data)
        ) {
            array_push($usernames, $data[0]);
        }
        fclose($handle);

        return $usernames;
    }

    public static function createIdentitiesFromAGroup(
        \OmegaUp\DAO\VO\Groups $group,
        \OmegaUp\Test\ScopedLoginToken $adminLogin,
        string $password
    ): array {
        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                strval($group->alias),
                $password
            ),
            'group_alias' => $group->alias,
        ]));

        // Getting the identities members list
        $response = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group_alias' => $group->alias,
        ]));

        [$unassociatedIdentity, $associatedIdentity] = $response['identities'];
        $unassociatedIdentity = \OmegaUp\DAO\Identities::FindByUsername(
            $unassociatedIdentity['username']
        );
        if (is_null($unassociatedIdentity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $associatedIdentity = \OmegaUp\DAO\Identities::FindByUsername(
            $associatedIdentity['username']
        );
        if (is_null($associatedIdentity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $unassociatedIdentity->password = $password;
        $associatedIdentity->password = $password;
        return [$unassociatedIdentity, $associatedIdentity];
    }
}
