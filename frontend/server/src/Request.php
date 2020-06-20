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
    public function offsetGet($key) {
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
        if (is_null($this->method)) {
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }

        /** @var mixed */
        $response = call_user_func($this->method, $this);

        if ($response === false) {
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }
        if (is_null($response) || !is_array($response)) {
            $apiException = new \OmegaUp\Exceptions\InternalServerErrorException(
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
        return self::ensureBool($key);
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
        return self::ensureInt($key, $lowerBound, $upperBound);
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
        if (!self::offsetExists($key) || is_null(self::offsetGet($key))) {
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
        ?float $upperBound = null,
        bool $required = true
    ): void {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return;
            }
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
    }

    /**
     * Ensures that an identity is logged in.
     *
     * @throws \OmegaUp\Exceptions\UnauthorizedException
     * @psalm-assert !null $this->identity
     * @psalm-assert !null $this->identity->identity_id
     * @psalm-assert !null $this->identity->username
     */
    public function ensureIdentity(): void {
        if (!is_null($this->user) || !is_null($this->identity)) {
            return;
        }
        $this->user = null;
        $this->identity = null;
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            $this
        );
        if (is_null($session['identity'])) {
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }
        $this->identity = $session['identity'];
        if (!is_null($session['user'])) {
            $this->user = $session['user'];
        }
    }

    /**
     * Ensures that an identity is logged in, and it is the main identity of
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
    public function ensureMainUserIdentity(): void {
        if (!is_null($this->user) && !is_null($this->identity)) {
            return;
        }
        $this->ensureIdentity();
        if (
            is_null($this->user)
            || $this->user->main_identity_id != $this->identity->identity_id
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
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
        if (is_null($this[$param])) {
            if ($required) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterEmpty',
                    $param
                );
            }
            return $default;
        }

        if (is_array($this[$param])) {
            /** @var list<string> */
            return $this[$param];
        }

        if (empty($this[$param])) {
            return [];
        }

        $strings = explode(',', strval($this[$param]));

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
        /** @var mixed $value */
        foreach ($this as $key => $value) {
            $result[strval($key)] = strval($value);
        }
        return $result;
    }
}

\OmegaUp\Request::$_requestId = str_replace('.', '', uniqid('', true));
