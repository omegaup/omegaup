<?php
/**
 * Request
 *
 * The request class holds all the parameters that are passed into an API call.
 * You can pass in an associative array to the constructor and that will be used
 * as a backing for the values.
 *
 * Request also holds all global state.
 *
 * You can use the push function to create a child Request object that will have
 * copy-on-write semantics.
 */
class Request extends ArrayObject {
    /**
     * The parent of this Request. This is set whenever the push function is called.
     */
    private $parent = null;

    /**
     * The object of the user currently logged in.
     */
    public $user = null;

    /**
     * The method that will be called.
     */
    public $method = null;

    /**
     * A global per-request unique(-ish) ID.
     */
    public static $_requestId = null;

    /**
     * Whether $key exists. Used as isset($req[$key]);
     *
     * @param string $key The key.
     */
    public function offsetExists($key) {
        return parent::offsetExists($key) || ($this->parent != null && isset($this->parent[$key]));
    }

    /**
     * Gets the value associated with a key. Used as $req[$key];
     *
     * @param string $key The key.
     */
    public function offsetGet($key) {
        if (isset($this[$key]) && parent::offsetGet($key) !== 'null') {
            return parent::offsetGet($key);
        }
        if ($this->parent != null) {
            return $this->parent->offsetGet($key);
        }
        return null;
    }

    /**
     * Creates a new Request with the same members as the current Request, with copy-on-write semantics.
     *
     * @param array $contents The (optional) array with the values.
     */
    public function push($contents = null) {
        $req = new Request($contents);
        $req->parent = $this;
        $req->user = $this->user;
        return $req;
    }

    /**
     * Executes the user-provided function and returns its result.
     */
    public function execute() {
        $response = call_user_func($this->method, $this);

        if ($response === false) {
            throw new NotFoundException('apiNotFound');
        }

        return $response;
    }

    /**
     * Gets the request ID.
     *
     * @return the global per-request unique(-ish) ID
     */
    public static function requestId() {
        return Request::$_requestId;
    }

    /**
     * Ensures that the value associated with the key is a bool.
     */
    public function ensureBool(
        string $key,
        bool $required = true
    ) {
        $val = self::offsetGet($key);
        if (is_int($val)) {
            $this[$key] = $val == 1;
        } elseif (is_bool($val)) {
            $this[$key] = $val;
        } else {
            if (empty($val)) {
                if (!$required) {
                    return;
                }
                throw new InvalidParameterException('parameterEmpty', $key);
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
    ) {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return;
            }
            throw new InvalidParameterException('parameterEmpty', $key);
        }
        $val = self::offsetGet($key);
        Validators::validateNumberInRange($val, $key, $lowerBound, $upperBound);
        $this[$key] = (int)$val;
    }

    /**
     * Ensures that the value associated with the key is a float.
     */
    public function ensureFloat(
        string $key,
        ?float $lowerBound = null,
        ?float $upperBound = null,
        bool $required = true
    ) {
        if (!self::offsetExists($key)) {
            if (!$required) {
                return;
            }
            throw new InvalidParameterException('parameterEmpty', $key);
        }
        $val = self::offsetGet($key);
        Validators::validateNumberInRange($val, $key, $lowerBound, $upperBound);
        $this[$key] = (float)$val;
    }
}

Request::$_requestId = str_replace('.', '', uniqid('', true));
