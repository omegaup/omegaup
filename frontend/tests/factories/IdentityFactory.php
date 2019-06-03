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
     * @param $file
     * @return $csv_data
     */
    public static function getCsvData($file, $group_alias, $password = '') {
        $row = 0;
        $identities = [];
        $path_file = OMEGAUP_RESOURCES_ROOT . $file;
        if (($handle = fopen($path_file, 'r')) == false) {
            throw new InvalidParameterException('parameterInvalid', 'identities');
        }
        $headers = fgetcsv($handle, 1000, ',');
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            array_push($identities, [
                'username' => "{$group_alias}:{$data[0]}",
                'name' => $data[1],
                'country_id' => $data[2],
                'state_id' => $data[3],
                'gender' => $data[4],
                'school_name' => $data[5],
                'password' => $password == '' ? Utils::CreateRandomString() : $password,
            ]);
        }
        fclose($handle);
        return $identities;
    }
}
