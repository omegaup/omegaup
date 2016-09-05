<?php

/**
 * Description of SecurityTools
 *
 * @author Alan Gonzalez alanboy@alanboy.net
 * @author Joe Ponce joe@omegaup.com
 */

require_once 'PasswordHash.php';

class SecurityTools {
    // Base-2 logarithm of the iteration count used for password stretching
    const HASH_COST = 8;

    // Do we require the hashes to be portable to older systems (less secure)?
    const HASH_PORTABILITY = false;

    // Minimum size that we like the hash strings to be. Shorter than this is
    // considered insecure
    const MIN_HASHED_STRING_LENGTH = 20;

    /**
     * Given the plain password to check and a hash, returns true if there is
     * a match.
     *
     * @param string $passwordToCheck
     * @param string $hashedPassword
     * @return boolean
     */
    public static function compareHashedStrings($passwordToCheck, $hashedPassword) {
        // Get an instance of the pass hasher
        $hasher = new PasswordHash(self::HASH_COST, self::HASH_PORTABILITY);

        // Compare passwords
        return $hasher->CheckPassword($passwordToCheck, $hashedPassword);
    }

    public static function testStrongPassword($s_Password) {
        // Setting max passwd length to 72 to avoid DoS attacks
        Validators::isStringOfMinLength($s_Password, 'password', 8);
        Validators::isStringOfMaxLength($s_Password, 'password', 72);

        return true;
    }

    /**
     * Given a plain string, returns its hash using phpass library
     *
     * @param string $string
     * @return string
     * @throws InternalServerErrorException
     */
    public static function hashString($string) {
        $hasher = new PasswordHash(self::HASH_COST, self::HASH_PORTABILITY);
        $hash = $hasher->HashPassword($string);

        // Check that hashed password is not too short
        if (strlen($hash) < self::MIN_HASHED_STRING_LENGTH) {
            throw new InternalServerErrorException(new Exception('phpass::PasswordHash::HashPassword returned hash too short.'));
        }

        return $hash;
    }

    /**
     * Returns a random string of size $length
     *
     * @param string $length
     * @return string
     */
    public static function randomString($length) {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $str = '';
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $index = 0;

            if (function_exists('random_int')) {
                $index = random_int(0, $size - 1);
            } else {
                $index = mt_rand(0, $size - 1);
            }

            $str .= $chars[$index];
        }

        return $str;
    }
}
