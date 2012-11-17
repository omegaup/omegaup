<?php

/**
 * Description of SecurityTools
 *
 * @author Alan Gonzalez alanboy@alanboy.net
 */

require_once 'PasswordHash.php';

class SecurityTools {

    public static function encryptString($unencrypted) {
        return $unencrypted;
    }

    public static function compareEncryptedStrings($encrypted_a, $encrypted_b) {
        return strcmp( $encrypted_a, $encrypted_b ) == 0;
    }


    public static function testStrongPassword($s_Password) {    
        if(strlen($s_Password) < 4) {
            return false;
        }

        return true;
    }
	
	public static function hashString($string) {
		$hasher = new PasswordHash(8, false);
		$hash = $hasher->HashPassword($string);
		
		if (strlen($hash) < 20) {
			Logger::error("Hasher returned hash too short.");
			throw new InternalServerError();
		}
		
		return $hash;
	}
}

