<?php

namespace OmegaUp;

/**
 * Request
 *
 * The request class holds all the parameters that are passed into an API call.
 * You can pass in an associative array to the constructor and that will be used
 * as a backing for the values.
 *
 * Request also holds all global state.
 */
class Request extends \ArrayObject {
    /**
     * The object of the user currently logged in.
     * @var null|\OmegaUp\DAO\VO\Users
     */
    public $user = null;

    /**
     * The object of the identity currently logged in.
     * @var null|\OmegaUp\DAO\VO\Identities
     */
    public $identity = null;

    /**
     * The object of the identity currently logged in.
     * @var null|\OmegaUp\DAO\VO\Identities
     */
    public $loginIdentity = null;

    /**
     * The method that will be called.
     * @var null|callable
     */
    public $method = null;

    /**
     * The name of the method that will be called.
     * @var null|string
     */
    public $methodName = null;

    /**
     * A global per-request unique(-ish) ID.
     * @var string
     */
    public static $_requestId;

    /**
     * Gets the value associated with a key. Used as $req[$key];
     *
     * @param mixed $key The key.
     * @return mixed
     */
    public function offsetGet($key): mixed {
        if (parent::offsetExists($key)) {
            return parent::offsetGet($key);
        }
        return null;
    }

    /**
     * Executes the user-provided function and returns its result.
     *
     * @return array<int, mixed>|array<string, mixed>
     */
    public function execute(): array {
        if ($this->method === null || $this->methodName === null) {
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }
        \OmegaUp\NewRelicHelper::nameTransaction("/api/{$this->methodName}");

        /** @var mixed */
        $response = call_user_func($this->method, $this);

        if ($response === false) {
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }
        if ($response === null || !is_array($response)) {
            throw new \OmegaUp\Exceptions\InternalServerErrorException(
                'generalError',
                new \Exception('API did not return an array.')
            );
        }

        /** @var array<int, mixed>|array<string, mixed> */
        return $response;
    }

    /**
     * Gets the request ID.
     *
     * @return string the global per-request unique(-ish) ID
     */
    public static function requestId(): string {
        return \OmegaUp\Request::$_requestId;
    }

    /**
     * @return bool whether a user has been logged with the main identity or not
     */
    public function isLoggedAsMainIdentity(): bool {
        return (
            $this->user !== null
            && $this->loginIdentity !== null
            && $this->user->main_identity_id === $this->loginIdentity->identity_id
        );
    }

    /**
     * Ensures that the value associated with the key is a bool.
     */
    public function ensureBool(
        string $key
    ): bool {
        /** @var mixed */
        $val = $this->offsetGet($key);
        if (is_int($val)) {
            $this[$key] = $val == 1;
        } elseif (is_bool($val)) {
            $this[$key] = $val;
        } else {
            if ($val === '0' || $val === 'false') {
                $this[$key] = false;
            } elseif (empty($val)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterEmpty',
                    $key
                );
            } else {
                $this[$key] = $val == '1' || $val == 'true';
            }
        }
        return boolval($this[$key]);
    }

    /**
     * Ensures that the value associated with the key is a bool or null.
     */
    public function ensureOptionalBool(
        string $key,
        bool $required = false
    ): ?bool {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureBool($key);
    }

    /**
     * Ensures that the value associated with the key is an int.
     */
    public function ensureInt(
        string $key,
        ?int $lowerBound = null,
        ?int $upperBound = null
    ): int {
        if (!self::offsetExists($key)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        \OmegaUp\Validators::validateNumberInRange(
            $val,
            $key,
            $lowerBound,
            $upperBound
        );
        $this[$key] = intval($val);
        return intval($val);
    }

    /**
     * Ensures that the value associated with the key is an int or null
     */
    public function ensureOptionalInt(
        string $key,
        ?int $lowerBound = null,
        ?int $upperBound = null,
        bool $required = false
    ): ?int {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureInt($key, $lowerBound, $upperBound);
    }

    /**
     * Ensures that the value associated with the key is a string.
     *
     * @param null|callable(string):bool $validator
     */
    public function ensureString(
        string $key,
        ?callable $validator = null
    ): string {
        if (!self::offsetExists($key)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        /** @var mixed */
        $mixedVal = $this->offsetGet($key);
        $val = (
            is_scalar($mixedVal) || is_object($mixedVal) ?
            strval($mixedVal) :
            ''
        );
        if ($validator !== null) {
            try {
                if (!$validator($val)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        $key
                    );
                }
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $e->parameter = $key;
                throw $e;
            }
        }
        $this[$key] = $val;
        return $val;
    }

    /**
     * Ensures that the value associated with the key is a string or null
     *
     * @param null|callable(string):bool $validator
     */
    public function ensureOptionalString(
        string $key,
        bool $required = false,
        ?callable $validator = null
    ): ?string {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureString($key, $validator);
    }

    /**
     * Ensures that the value associated with the key is a timestamp.
     */
    public function ensureTimestamp(
        string $key,
        ?int $lowerBound = null,
        ?int $upperBound = null
    ): \OmegaUp\Timestamp {
        if (!self::offsetExists($key)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        \OmegaUp\Validators::validateTimestampInRange(
            $val,
            $key,
            $lowerBound,
            $upperBound
        );
        if ($val instanceof \OmegaUp\Timestamp) {
            $timestampVal = $val;
        } else {
            $timestampVal = new \OmegaUp\Timestamp(intval($val));
        }
        $this[$key] = $timestampVal;
        return $timestampVal;
    }

    public function ensureOptionalTimestamp(
        string $key,
        ?int $lowerBound = null,
        ?int $upperBound = null,
        bool $required = false
    ): ?\OmegaUp\Timestamp {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureTimestamp($key, $lowerBound, $upperBound);
    }

    /**
     * Ensures that the value associated with the key is a float.
     */
    public function ensureFloat(
        string $key,
        ?float $lowerBound = null,
        ?float $upperBound = null
    ): float {
        if (!self::offsetExists($key)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        \OmegaUp\Validators::validateNumberInRange(
            $val,
            $key,
            $lowerBound,
            $upperBound
        );
        $this[$key] = floatval($val);
        return floatval($this[$key]);
    }

    /**
     * Ensures that the value associated with the key is a float.
     */
    public function ensureOptionalFloat(
        string $key,
        ?float $lowerBound = null,
        ?float $upperBound = null,
        bool $required = false
    ): ?float {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureFloat($key, $lowerBound, $upperBound);
    }

    /**
     * Ensures that the value associated with the key is in an enum.
     *
     * @psalm-template TValue of scalar
     * @param array<array-key, TValue> $enumValues
     * @return TValue
     */
    public function ensureEnum(
        string $key,
        array $enumValues
    ) {
        if (!self::offsetExists($key)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        foreach ($enumValues as $enumValue) {
            if ($val == $enumValue) {
                return $enumValue;
            }
        }
        throw new \OmegaUp\Exceptions\InvalidParameterException(
            'parameterNotInExpectedSet',
            $key,
            [
                'bad_elements' => (
                    is_scalar($val) || is_object($val) ?
                    strval($val) :
                    ''
                ),
                'expected_set' => implode(', ', $enumValues),
            ]
        );
    }

    /**
     * Ensures that the value associated with the key is in an enum.
     *
     * @psalm-template TValue of scalar
     * @param array<int, TValue> $enumValues
     * @return TValue|null
     */
    public function ensureOptionalEnum(
        string $key,
        array $enumValues,
        bool $required = false
    ) {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return null;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $key
            );
        }
        return $this->ensureEnum($key, $enumValues);
    }

    /**
     * Ensures that an identity is logged in.
     *
     * @psalm-assert !null $this->identity
     * @psalm-assert !null $this->identity->identity_id
     * @psalm-assert !null $this->identity->username
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     * @return void
     */
    public function ensureIdentity(): void {
        if ($this->user !== null || $this->identity !== null) {
            return;
        }
        $this->user = null;
        $this->identity = null;
        $this->loginIdentity = null;
        $session = \OmegaUp\Controllers\Session::getCurrentSession($this);
        if ($session['identity'] === null) {
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }
        $this->identity = $session['identity'];
        $this->loginIdentity = $session['loginIdentity'];
        if ($session['user'] !== null) {
            $this->user = $session['user'];
        }
    }

    /**
     * Ensures that an identity is logged in, and it is the main identity of
     * its associated user.
     *
     * @psalm-assert !null $this->identity
     * @psalm-assert !null $this->identity->identity_id
     * @psalm-assert !null $this->identity->user_id
     * @psalm-assert !null $this->identity->username
     * @psalm-assert !null $this->user
     * @psalm-assert !null $this->user->main_identity_id
     * @psalm-assert !null $this->user->user_id
     * @psalm-assert !null $this->user->username
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     * @return void
     */
    public function ensureMainUserIdentity(): void {
        $this->ensureIdentity();
        if (
            $this->user === null
            || $this->user->main_identity_id != $this->identity->identity_id
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }

    /**
     * Ensures that an identity is logged in is Over 13 years of age.
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     * @psalm-assert !null $this->identity
     * @psalm-assert !null $this->identity->identity_id
     * @psalm-assert !null $this->identity->username
     */
    public function ensureIdentityIsOver13(): void {
        $this->ensureIdentity();
        if ($this->user === null) {
            return;
        }
        if (\OmegaUp\Authorization::isUnderThirteenUser($this->user)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'U13CannotPerform'
            );
        }
    }

    /**
     * Ensures that an identity is logged in is Over 13 years of age, and it is the main identity of
     * its associated user.
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     * @psalm-assert !null $this->identity
     * @psalm-assert !null $this->identity->identity_id
     * @psalm-assert !null $this->identity->user_id
     * @psalm-assert !null $this->identity->username
     * @psalm-assert !null $this->user
     * @psalm-assert !null $this->user->main_identity_id
     * @psalm-assert !null $this->user->user_id
     * @psalm-assert !null $this->user->username
     */
    public function ensureMainUserIdentityIsOver13(): void {
        $this->ensureMainUserIdentity();
        if (\OmegaUp\Authorization::isUnderThirteenUser($this->user)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'U13CannotPerform'
            );
        }
    }

    /**
     * Returns an array of strings from a request parameter
     * containing a single string with comma-separated values.
     *
     * @param list<string> $default
     * @return list<string>
     */
    public function getStringList(
        string $param,
        array $default = [],
        bool $required = false
    ): array {
        if ($this[$param] === null) {
            if ($required) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterEmpty',
                    $param
                );
            }
            return $default;
        }

        /** @var mixed */
        $value = $this[$param];
        if (is_array($value)) {
            /** @var list<string> */
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $strings = explode(
            ',',
            (
                is_scalar($value) || is_object($value) ?
                strval($value) :
                ''
            )
        );

        /** @var list<string> */
        return array_unique($strings);
    }

    /**
     * Returns a real array from the Request values. This is useful to build
     * Params objects.
     *
     * @return array<string, string>
     */
    public function toStringArray(): array {
        $result = [];
        /**
         * @var mixed $key
         * @var mixed $value
         */
        foreach ($this as $key => $value) {
            $result[
                is_scalar($key) || is_object($key) ?
                strval($key) :
                ''
            ] = (
                is_scalar($value) || is_object($value) ?
                strval($value) :
                ''
            );
        }
        return $result;
    }

    /**
     * Returns the content of $_SERVER[$name] as a string (or null).
     */
    public static function getServerVar(string $name): ?string {
        if (!isset($_SERVER[$name]) || !is_string($_SERVER[$name])) {
            return null;
        }
        return $_SERVER[$name];
    }

    /**
     * Returns the content of $_REQUEST[$name] as a string (or null).
     */
    public static function getRequestVar(string $name): ?string {
        if (!isset($_REQUEST[$name]) || !is_string($_REQUEST[$name])) {
            return null;
        }
        return $_REQUEST[$name];
    }
}

\OmegaUp\Request::$_requestId = str_replace('.', '', uniqid('', true));
