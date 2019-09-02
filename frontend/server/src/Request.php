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
    public function execute() : array {
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
    public static function requestId() : string {
        return \OmegaUp\Request::$_requestId;
    }

    /**
     * Ensures that the value associated with the key is a bool.
     */
    public function ensureBool(
        string $key,
        bool $required = true
    ) : void {
        /** @var mixed */
        $val = $this->offsetGet($key);
        if (is_int($val)) {
            $this[$key] = $val == 1;
        } elseif (is_bool($val)) {
            $this[$key] = $val;
        } else {
            if (empty($val)) {
                if (!$required) {
                    return;
                }
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', $key);
            }
            $this[$key] = $val == '1' || $val == 'true';
        }
    }

    /**
     * Ensures that the value associated with the key is an int.
     */
    public function ensureInt(
        string $key,
        ?int $lowerBound = null,
        ?int $upperBound = null,
        bool $required = true
    ) : void {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', $key);
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        \OmegaUp\Validators::validateNumberInRange($val, $key, $lowerBound, $upperBound);
        $this[$key] = intval($val);
    }

    /**
     * Ensures that the value associated with the key is a float.
     */
    public function ensureFloat(
        string $key,
        ?float $lowerBound = null,
        ?float $upperBound = null,
        bool $required = true
    ) : void {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return;
            }
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', $key);
        }
        /** @var mixed */
        $val = $this->offsetGet($key);
        \OmegaUp\Validators::validateNumberInRange($val, $key, $lowerBound, $upperBound);
        $this[$key] = floatval($val);
    }
}

\OmegaUp\Request::$_requestId = str_replace('.', '', uniqid('', true));
