<?php

namespace OmegaUp;

/**
 * A class that abstracts away support for APC user cache for PHP7 / Travis.
 */
abstract class CacheAdapter {
    /** @var CacheAdapter|null */
    private static $_instance = null;

    public static function getInstance(): CacheAdapter {
        if (is_null(CacheAdapter::$_instance)) {
            if (function_exists('apcu_clear_cache')) {
                CacheAdapter::$_instance = new APCCacheAdapter();
            } else {
                CacheAdapter::$_instance = new InProcessCacheAdapter();
            }
        }
        return CacheAdapter::$_instance;
    }

    /**
     * @param string $key
     * @param mixed $defaultVar
     * @param int $ttl
     * @return mixed
     */
    abstract public function entry(string $key, $defaultVar, int $ttl = 0);

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return bool
     */
    abstract public function add(string $key, $var, int $ttl = 0): bool;
    abstract public function cas(string $key, int $old, int $new): bool;
    abstract public function clear(): void;
    abstract public function delete(string $key): bool;

    /**
     * @param string $key
     * @return mixed
     */
    abstract public function fetch(string $key);

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     */
    abstract public function store(string $key, $var, int $ttl = 0): bool;
}

/**
 * Implementation of CacheAdapter that uses the real APC functions.
 */
class APCCacheAdapter extends CacheAdapter {
    /**
     * @param string $key
     * @param mixed $defaultVar
     * @param int $ttl
     * @return mixed
     */
    public function entry(string $key, $defaultVar, int $ttl = 0) {
        return apcu_entry(
            $key,
            /** @return mixed */
            function (string $key) use ($defaultVar) {
                return $defaultVar;
            },
            $ttl
        );
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return bool
     */
    public function add(string $key, $var, int $ttl = 0): bool {
        return apcu_add($key, $var, $ttl);
    }

    public function cas(string $key, int $old, int $new): bool {
        return apcu_cas($key, $old, $new);
    }

    public function clear(): void {
        apcu_clear_cache();
    }

    public function delete(string $key): bool {
        return apcu_delete($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        return apcu_fetch($key, $success);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     */
    public function store(string $key, $var, int $ttl = 0): bool {
        return apcu_store($key, $var, $ttl);
    }
}

/**
 * Implementation of CacheAdapter that uses an array to back the cache. Does
 * not survive across test function invocations.
 */
class InProcessCacheAdapter extends CacheAdapter {
    /** @var array<string, mixed> */
    private $cache = [];

    /**
     * @param string $key
     * @param mixed $defaultVar
     * @param int $ttl
     * @return mixed
     */
    public function entry(string $key, $defaultVar, int $ttl = 0) {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $defaultVar;
        }
        return $this->cache[$key];
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return bool
     */
    public function add(string $key, $var, int $ttl = 0): bool {
        if (array_key_exists($key, $this->cache)) {
            return false;
        }
        $this->cache[$key] = $var;
        return true;
    }

    public function cas(string $key, int $old, int $new): bool {
        if (
            !array_key_exists($key, $this->cache) ||
            $this->cache[$key] !== $old
        ) {
            return false;
        }
        $this->cache[$key] = $new;
        return true;
    }

    public function clear(): void {
        $this->cache = [];
    }

    public function delete(string $key): bool {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }
        unset($this->cache[$key]);
        return true;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function fetch(string $key) {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }
        return $this->cache[$key];
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     */
    public function store(string $key, $var, int $ttl = 0): bool {
        $this->cache[$key] = $var;
        return true;
    }
}

/**
 * Maneja el acceso al cache (usando apc user cache)
 *
 */
class Cache {
    const SESSION_PREFIX = 'session-';
    const CONTESTANT_SCOREBOARD_PREFIX = 'scoreboard-';
    const ADMIN_SCOREBOARD_PREFIX = 'scoreboard-admin-';
    const CONTESTANT_SCOREBOARD_EVENTS_PREFIX = 'scoreboard-events-';
    const ADMIN_SCOREBOARD_EVENTS_PREFIX = 'scoreboard-events-admin-';
    const CONTEST_INFO = 'contest-info-';
    const PROBLEM_SETTINGS_DISTRIB = 'problem-settings-distrib-json-';
    const PROBLEM_STATEMENT = 'statement-';
    const PROBLEM_SOLUTION = 'solution-';
    const PROBLEM_SOLUTION_EXISTS = 'solution-exists-';
    const PROBLEM_STATS = 'problem-stats-';
    const RUN_ADMIN_DETAILS = 'run-admin-details-';
    const RUN_COUNTS = 'run-counts-';
    const USER_PROFILE = 'profile-';
    const PROBLEMS_SOLVED_RANK = 'problems-solved-rank-';
    const CONTESTS_LIST_PUBLIC = 'contest-list-public';
    const CONTESTS_LIST_SYSTEM_ADMIN = 'contest-list-sys-admin';
    const CONTESTS_LIST_USER_ID = 'contest-list-user-id';
    const SCHOOL_RANK = 'school-rank';

    /** @var \Logger */
    private $log;

    /** @var string */
    protected $key;

    /**
     * Inicializa el cache para el key dado
     * @param string $key el id del cache
     */
    public function __construct(string $prefix, string $id = '') {
        $this->log = \Logger::getLogger('cache');

        if (!self::isEnabled()) {
            $this->log->debug('Cache disabled');
            return;
        }

        $cache_ver = self::getVersion($prefix);
        $this->key = "{$cache_ver}{$prefix}{$id}";
        $this->log->debug("Cache enabled for {$this->key}");
    }

    /**
     * set
     *
     * Si el cache está prendido, guarda value en key con el timeout dado
     *
     * @param mixed $value
     * @param int $timeout (seconds)
     * @return boolean
     */
    public function set($value, int $timeout = APC_USER_CACHE_TIMEOUT): bool {
        if (!self::isEnabled()) {
            return false;
        }
        if (
            CacheAdapter::getInstance()->store(
                $this->key,
                $value,
                $timeout
            ) !== true
        ) {
            $this->log->debug("Cache store failed for key: {$this->key}");
            return false;
        }
        $this->log->debug("Cache stored successful for key: {$this->key}");
        return true;
    }

    /**
     * delete
     *
     * Si el cache esta prendido, invalida el key del cache
     *
     * @return boolean
     */
    public function delete(): bool {
        if (!self::isEnabled()) {
            return false;
        }
        if (CacheAdapter::getInstance()->delete($this->key) !== true) {
            $this->log->warn(
                "Failed to invalidate cache for key: {$this->key}"
            );
            return false;
        }
        return true;
    }

    /**
     * get
     *
     * Si el cache está prendido y la clave está en el cache, regresa el valor. Si no está, regresa null
     *
     * @return null|mixed
     */
    public function get() {
        if (!self::isEnabled()) {
            return null;
        }
        /** @var false|mixed */
        $result = CacheAdapter::getInstance()->fetch($this->key);
        if ($result === false) {
            $this->log->info("Cache miss for key: {$this->key}");
            return null;
        }
        $this->log->debug("Cache hit for key: {$this->key}");
        return $result;
    }

    /**
     * If the specified $id exists in cache, gets its associated value from the
     * cache.  Otherwise, executes $setFunc() to generate the associated
     * value, stores it, and returns it.
     *
     * @param string $prefix
     * @param string $id
     * @param callable():mixed $setFunc
     * @param int $timeout (seconds)
     * @param ?bool &$cacheUsed Whether the $id had a pre-computed value in the cache.
     * @return mixed the value returned from the cache or $setFunc().
     */
    public static function getFromCacheOrSet(
        string $prefix,
        string $id,
        callable $setFunc,
        int $timeout = 0,
        ?bool &$cacheUsed = null
    ) {
        $cache = new \OmegaUp\Cache($prefix, $id);
        /** @var null|mixed */
        $returnValue = $cache->get();

        // If there wasn't a value in the cache for the key ($prefix, $id)
        if (!is_null($returnValue)) {
            if (!is_null($cacheUsed)) {
                $cacheUsed = true;
            }
            return $returnValue;
        }

        // Get the value from the function provided
        /** @var mixed */
        $returnValue = call_user_func($setFunc);
        $cache->set($returnValue, $timeout);

        if (!is_null($cacheUsed)) {
            $cacheUsed = false;
        }
        return $returnValue;
    }

    /**
     * Delete the entry defined by $prefix-$id
     *
     * @param string $prefix
     * @param string $id
     */
    public static function deleteFromCache($prefix, $id = ''): void {
        $cache = new \OmegaUp\Cache($prefix, $id);
        $cache->delete();
    }

    /**
     * Gets the current cache version for all entries with prefix $prefix.
     *
     * @param string $prefix
     */
    private static function getVersion(string $prefix): int {
        $key = "v{$prefix}";
        return intval(CacheAdapter::getInstance()->entry($key, 0));
    }

    /**
     * Invalidate all entries that begin with $prefix.
     *
     * It does so by changing the current version used for these entries, so
     * old entries will never be fetched or updated again.
     *
     * @param string $prefix
     */
    public static function invalidateAllKeys(string $prefix): void {
        if (!self::isEnabled()) {
            return;
        }
        $key = "v{$prefix}";
        // Must do this in a loop to avoid race condition when two threads try
        // to invalidate the cache simultaneously.
        do {
            // Ensure the version key exists.
            $version = self::getVersion($prefix);
        } while (
            !CacheAdapter::getInstance()->cas(
                $key,
                $version,
                $version + 1
            )
        );
    }

    private static function isEnabled(): bool {
        return defined('APC_USER_CACHE_ENABLED') &&
            APC_USER_CACHE_ENABLED === true;
    }

    /**
     * Invalidates all entries in the user cache.
     *
     * Only use this for testing purposes.
     */
    public static function clearCacheForTesting(): void {
        CacheAdapter::getInstance()->clear();
    }
}
