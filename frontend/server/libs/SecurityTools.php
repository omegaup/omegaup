<?php

/**
 * Description of SecurityTools
 *
 * @author Alan Gonzalez alanboy@alanboy.net
 * @author Joe Ponce joe@omegaup.com
 */

require_once 'third_party/PasswordHash.php';

class SecurityTools {
    // Base-2 logarithm of the iteration count used for password stretching
    const HASH_COST = 8;

    // Do we require the hashes to be portable to older systems (less secure)?
    const HASH_PORTABILITY = false;

    // Minimum size that we like the hash strings to be. Shorter than this is
    // considered insecure
    const MIN_HASHED_STRING_LENGTH = 20;

    /**
     * The secret key that is used to communicate with omegaup-gitserver.
     */
    private static $_gitserverSecretKey;

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
        $max = strlen($chars) - 1;
        $rng = function_exists('random_int') ? 'random_int' : 'mt_rand';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[$rng(0, $max)];
        }

        return $str;
    }

    public static function getGitserverAuthorizationHeader(string $problem, string $username) {
        require_once 'libs/third_party/sodium_compat/autoload-fast.php';

        require_once 'libs/third_party/constant_time_encoding/src/EncoderInterface.php';
        require_once 'libs/third_party/constant_time_encoding/src/Base64.php';
        require_once 'libs/third_party/constant_time_encoding/src/Base64UrlSafe.php';
        require_once 'libs/third_party/constant_time_encoding/src/Binary.php';

        require_once 'libs/third_party/paseto/src/KeyInterface.php';
        require_once 'libs/third_party/paseto/src/SendingKey.php';
        require_once 'libs/third_party/paseto/src/ReceivingKey.php';
        require_once 'libs/third_party/paseto/src/Keys/AsymmetricSecretKey.php';
        require_once 'libs/third_party/paseto/src/Keys/AsymmetricPublicKey.php';
        require_once 'libs/third_party/paseto/src/ProtocolCollection.php';
        require_once 'libs/third_party/paseto/src/ProtocolInterface.php';
        require_once 'libs/third_party/paseto/src/Protocol/Version1.php';
        require_once 'libs/third_party/paseto/src/Protocol/Version2.php';
        require_once 'libs/third_party/paseto/src/Traits/RegisteredClaims.php';
        require_once 'libs/third_party/paseto/src/JsonToken.php';
        require_once 'libs/third_party/paseto/src/Purpose.php';
        require_once 'libs/third_party/paseto/src/Builder.php';
        require_once 'libs/third_party/paseto/src/Util.php';
        require_once 'libs/third_party/paseto/src/Parsing/Header.php';
        require_once 'libs/third_party/paseto/src/Parsing/PasetoMessage.php';
        require_once 'libs/third_party/paseto/src/Exception/PasetoException.php';

        if (self::$_gitserverSecretKey == null) {
            self::$_gitserverSecretKey = new \ParagonIE\Paseto\Keys\AsymmetricSecretKey(
                base64_decode(OMEGAUP_GITSERVER_SECRET_KEY)
            );
        }
        $token = (new \ParagonIE\Paseto\Builder())
            ->setKey(self::$_gitserverSecretKey)
            ->setVersion(new \ParagonIE\Paseto\Protocol\Version2())
            ->setPurpose(\ParagonIE\Paseto\Purpose::public())
            ->setExpiration(
                (new DateTime('now'))->add(new DateInterval('PT5M'))
            )
            ->setIssuer('omegaUp frontend')
            ->setSubject($username)
            ->setClaims([
                'problem' => $problem,
            ]);
        return "Authorization: Bearer {$token->toString()}";
    }
}
