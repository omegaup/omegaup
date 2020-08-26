<?php

namespace OmegaUp;

/**
 * Password and token functions.
 */
class SecurityTools {
    /**
     * The expected prefix of an Argon2id crypt hash.
     */
    private const ARGON2ID_CRYPT_HASH_PREFIX = '$argon2id$';

    /**
     * The memory cost for the Argon2id crypt hash, in kibibytes.
     */
    private const ARGON2ID_MEMORY_COST = 1024;

    /**
     * Options that allow for compatibility between PHP's password_hash() and
     * libsodium's sodium_crypto_pwhash_str().
     */
    private const PASSWORD_HASH_OPTIONS = [
        'threads' => 1,
        'memory_cost' => self::ARGON2ID_MEMORY_COST,
        'time_cost' => SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
    ];

    /**
     * The secret key that is used to communicate with omegaup-gitserver.
     * @var null|\ParagonIE\Paseto\Keys\AsymmetricSecretKey
     */
    private static $_gitserverSecretKey = null;

    /**
     * @var null|\ParagonIE\Paseto\Keys\SymmetricKey
     */
    private static $_courseCloneSecretKey = null;

    /**
     * Given the plain password to check and a hash, returns true if there is
     * a match.
     *
     * @param string $passwordToCheck
     * @param string $hashedPassword
     * @return boolean
     */
    public static function compareHashedStrings(
        string $passwordToCheck,
        string $hashedPassword
    ): bool {
        if (
            !defined('PASSWORD_ARGON2ID') &&
            strpos($hashedPassword, self::ARGON2ID_CRYPT_HASH_PREFIX) === 0
        ) {
            return sodium_crypto_pwhash_str_verify(
                $hashedPassword,
                $passwordToCheck
            );
        }
        return password_verify($passwordToCheck, $hashedPassword);
    }

    public static function testStrongPassword(?string $password): void {
        // Setting max passwd length to 72 to avoid DoS attacks
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $password,
            'password',
            8,
            72
        );
    }

    /**
     * Given a plain string, returns its hash using the Argon2id algorithm.
     *
     * @param string $string
     * @return string
     */
    public static function hashString(string $string): string {
        if (!defined('PASSWORD_ARGON2ID')) {
            $hashedString = sodium_crypto_pwhash_str(
                $string,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
                self::ARGON2ID_MEMORY_COST * 1024
            );
        } else {
            /** @psalm-suppress InvalidScalarArgument This misfires in PHP 7.4 */
            $hashedString = password_hash(
                $string,
                PASSWORD_ARGON2ID,
                self::PASSWORD_HASH_OPTIONS
            );
        }
        if ($hashedString === false || is_null($hashedString)) {
            throw new \OmegaUp\Exceptions\InternalServerErrorException(
                'generalError',
                new \Exception('Hash function returned false')
            );
        }
        return $hashedString;
    }

    /**
     * Given a hashed password, returns whether it was hashed using the old
     * Blowfish algorithm.
     *
     * @param string $hashedPassword The hashed password
     * @return bool Whether it is produced using the old Blowfish algorithm.
     */
    public static function isOldHash(string $hashedPassword): bool {
        if (!defined('PASSWORD_ARGON2ID')) {
            if (
                strpos(
                    $hashedPassword,
                    self::ARGON2ID_CRYPT_HASH_PREFIX
                ) !== 0
            ) {
                return true;
            }
            return sodium_crypto_pwhash_str_needs_rehash(
                $hashedPassword,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
                self::ARGON2ID_MEMORY_COST * 1024
            );
        }
        /** @psalm-suppress InvalidScalarArgument This misfires in PHP 7.4 */
        return password_needs_rehash(
            $hashedPassword,
            PASSWORD_ARGON2ID,
            self::PASSWORD_HASH_OPTIONS
        );
    }

    /**
     * Returns a random string of size $length
     *
     * @param int $length
     * @return string
     */
    public static function randomString(int $length): string {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $str = '';
        $max = strlen($chars) - 1;
        /** @var callable(int, int):int */
        $rng = function_exists('random_int') ? 'random_int' : 'mt_rand';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[$rng(0, $max)];
        }

        return $str;
    }

    /**
     * Returns a random hexadecimal string.
     *
     * @param int $length The length of the string.
     * @return string The string.
     */
    public static function randomHexString(int $length): string {
        return bin2hex(random_bytes(intval($length / 2)));
    }

    /**
     * Returns the Bearer HTTP authorization header to use against the gitserver.
     *
     * @param string $problem  The problem alias.
     * @param string $username The username that is going to be authenticated.
     * @return string The Bearer HTTP authorization header.
     */
    public static function getGitserverAuthorizationHeader(
        string $problem,
        string $username
    ): string {
        if (OMEGAUP_GITSERVER_SECRET_TOKEN != '') {
            return 'Authorization: OmegaUpSharedSecret ' . OMEGAUP_GITSERVER_SECRET_TOKEN . ' ' . $username;
        }

        return 'Authorization: Bearer ' . self::getAuthorizationToken(
            /*$claims*/            ['problem' => $problem],
            /*$subject*/$username,
            /*$expiration*/'PT5M',
            /*$tokenType=*/'gitserver'
        );
    }

    /**
     * Gets a Bearer authorization token for a particular subject that is valid
     * for a single claim in a given time.
     *
     * @param array<string, string> $claims
     * @param string                $subject
     * @param string                $expiration
     * @param string                $tokenType
     * @return string The Bearer authorization token.
     */
    public static function getAuthorizationToken(
        $claims,
        $subject,
        $expiration,
        $tokenType
    ): string {
        // Given that we already have an autoload configured, we cannot use
        // sodium_compat's (fast) autoloader. Instead, simulate what it does
        // here, with the full path of the standard autoload file.
        require_once 'libs/third_party/sodium_compat/autoload.php';
        \ParagonIE_Sodium_Compat::$fastMult = true;

        require_once 'libs/third_party/constant_time_encoding/src/EncoderInterface.php';
        require_once 'libs/third_party/constant_time_encoding/src/Base64.php';
        require_once 'libs/third_party/constant_time_encoding/src/Base64UrlSafe.php';
        require_once 'libs/third_party/constant_time_encoding/src/Binary.php';

        require_once 'libs/third_party/paseto/src/KeyInterface.php';
        require_once 'libs/third_party/paseto/src/SendingKey.php';
        require_once 'libs/third_party/paseto/src/ReceivingKey.php';
        require_once 'libs/third_party/paseto/src/Keys/SymmetricKey.php';
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
        require_once 'libs/third_party/paseto/src/Exception/InvalidKeyException.php';

        if ($tokenType === 'gitserver') {
            if (is_null(self::$_gitserverSecretKey)) {
                self::$_gitserverSecretKey = new \ParagonIE\Paseto\Keys\AsymmetricSecretKey(
                    base64_decode(OMEGAUP_GITSERVER_SECRET_KEY)
                );
            }
            $secretKey = self::$_gitserverSecretKey;
            $purpose = \ParagonIE\Paseto\Purpose::public();
        } else {
            if (is_null(self::$_courseCloneSecretKey)) {
                self::$_courseCloneSecretKey = new \ParagonIE\Paseto\Keys\SymmetricKey(
                    base64_decode(OMEGAUP_COURSE_CLONE_SECRET_KEY)
                );
            }
            $secretKey = self::$_courseCloneSecretKey;
            $purpose = \ParagonIE\Paseto\Purpose::local();
        }

        $token = (new \ParagonIE\Paseto\Builder())
            ->setKey($secretKey)
            ->setVersion(new \ParagonIE\Paseto\Protocol\Version2())
            ->setPurpose($purpose)
            ->setExpiration(
                (new \DateTime(
                    'now'
                ))->add(
                    new \DateInterval(
                        $expiration
                    )
                )
            )
            ->setIssuer('omegaUp frontend')
            ->setSubject($subject)
            ->setClaims($claims);
        return $token->toString();
    }
}
