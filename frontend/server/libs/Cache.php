<?php

/**
 * A class that abstracts away support for APC user cache for PHP7 / Travis.
 */
abstract class CacheAdapter {
    private static $sInstance = null;

    public static function getInstance() {
        if (CacheAdapter::$sInstance == null) {
            if (function_exists('apcu_clear_cache')) {
                CacheAdapter::$sInstance = new APCCacheAdapter();
            } else {
                CacheAdapter::$sInstance = new InProcessCacheAdapter();
            }
        }
        return CacheAdapter::$sInstance;
    }

    abstract public function entry($key, $default_var, $ttl = 0);
    abstract public function add($key, $var, $ttl = 0);
    abstract public function cas($key, $old, $new);
    abstract public function clear();
    abstract public function delete($key);
    abstract public function fetch($key);
    abstract public function store($key, $var, $ttl = 0);
}

/**
 * Implementation of CacheAdapter that uses the real APC functions.
 */
class APCCacheAdapter extends CacheAdapter {
    public function entry($key, $default_var, $ttl = 0) {
        return apcu_entry($key, function ($key) use ($default_var) {
            return $default_var;
        }, $ttl);
    }

    public function add($key, $var, $ttl = 0) {
        return apcu_add($key, $var, $ttl);
    }

    public function cas($key, $old, $new) {
        return apcu_cas($key, $old, $new);
    }

    public function clear() {
        apcu_clear_cache();
    }

    public function delete($key) {
        return apcu_delete($key);
    }

    public function fetch($key) {
        return apcu_fetch($key, $success);
    }

    public function store($key, $var, $ttl = 0) {
        return apcu_store($key, $var, $ttl);
    }
}

/**
 * Implementation of CacheAdapter that uses an array to back the cache. Does
 * not survive across test function invocations.
 */
class InProcessCacheAdapter extends CacheAdapter {
    private $cache = [];

    public function entry($key, $default_var, $ttl = 0) {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $default_var;
        }
        return $this->cache[$key];
    }

    public function add($key, $var, $ttl = 0) {
        if (array_key_exists($key, $this->cache)) {
            return false;
        }
        $this->cache[$key] = $var;
        return true;
    }

    public function cas($key, $old, $new) {
        if (!array_key_exists($key, $this->cache) || $this->cache[$key] !== $old) {
            return false;
        }
        $this->cache[$key] = $new;
        return true;
    }

    public function clear() {
        $this->cache = [];
    }

    public function delete($key) {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }
        unset($this->cache[$key]);
        return true;
    }

    public function fetch($key) {
        if (!array_key_exists($key, $this->cache)) {
            return false;
        }
        return $this->cache[$key];
    }

    public function store($key, $var, $ttl = 0) {
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
    const PROBLEM_STATEMENT = 'statement-';
    const PROBLEM_SAMPLE = 'sample-';
    const CONTEST_INFO = 'contest-info-';
    const PROBLEM_STATS = 'problem-stats-';
    const RUN_ADMIN_DETAILS = 'run-admin-details-';
    const RUN_COUNTS = 'run-counts-';
    const USER_PROFILE = 'profile-';
    const PROBLEMS_SOLVED_RANK = 'problems-solved-rank-';
    const CONTESTS_LIST_PUBLIC = 'contest-list-public';
    const CONTESTS_LIST_SYSTEM_ADMIN = 'contest-list-sys-admin';
    const CONTESTS_LIST_USER_ID = 'contest-list-user-id';
    const SCHOOL_RANK = 'school-rank';

    private $enabled;
    private $log;
    protected $key;

    public static $cacheResults = true;

    /**
     * Inicializa el cache para el key dado
     * @param string $key el id del cache
     */
    public function __construct($prefix, $id = '') {
        $this->enabled = self::cacheEnabled();
        $this->log = Logger::getLogger('cache');

        if ($this->enabled) {
            $cache_ver = self::getVersion($prefix);
            $this->key = $cache_ver.$prefix.$id;
            $this->log->debug('Cache enabled for ' . $this->key);
        } else {
            $this->log->debug('Cache disabled');
        }
    }

    /**
     * set
     *
     * Si el cache está prendido, guarda value en key con el timeout dado
     *
     * @param string $value
     * @param int $timeout (seconds)
     * @return boolean
     */
    public function set($value, $timeout = APC_USER_CACHE_TIMEOUT) {
        if ($this->enabled === true) {
            if (CacheAdapter::getInstance()->store($this->key, $value, $timeout) === true) {
                $this->log->debug('Cache stored successful for key: ' . $this->key);
                return true;
            } else {
                $this->log->debug('Cache store failed for key: ' . $this->key);
            }
        }
        return false;
    }

    /**
     * delete
     *
     * Si el cache esta prendido, invalida el key del cache
     *
     * @return boolean
     */
    public function delete() {
        if ($this->enabled === true) {
            if (CacheAdapter::getInstance()->delete($this->key) === true) {
                return true;
            } else {
                $this->log->warn('Failed to invalidate cache for key: ' . $this->key);
            }
        }

        return false;
    }

    /**
     * get
     *
     * Si el cache está prendido y la clave está en el cache, regresa el valor. Si no está, regresa null
     *
     * @return mixed
     */
    public function get() {
        if ($this->enabled === true) {
            if (($result = CacheAdapter::getInstance()->fetch($this->key)) !== false) {
                $this->log->debug('Cache hit for key: ' . $this->key);
                return $result;
            } else {
                $this->log->info('Cache miss for key: ' . $this->key);
            }
        }

        return null;
    }

    /**
     *
     * If value exists from cache, get it from cache.
     * Otherwise, executes the $setFunc to generate a value that will be
     * stored in the cache and it will return it.
     *
     * Returns true if cache was used, false if it had to be set
     *
     * @param string $prefix
     * @param string $id
     * @param Request $r
     * @param callable $setFunc
     * @param int $timeout (seconds)
     * @return boolean
     */
    public static function getFromCacheOrSet($prefix, $id, $arg, $setFunc, &$returnValue, $timeout = 0) {
        if (is_null($id)) {
            // Unconditionally skipping cache.
            $returnValue = call_user_func($setFunc, $r);
            return false;
        }
        $cache = new Cache($prefix, $id);
        $returnValue = $cache->get();

        // If there wasn't a value in the cache for the key ($prefix, $id)
        if (is_null($returnValue)) {
            // Get the value from the function provided
            $returnValue = call_user_func($setFunc, $arg);

            // If the $setFunc() didn't disable the cache
            if (self::$cacheResults === true) {
                $cache->set($returnValue, $timeout);
            } else {
                // Reset value
                self::$cacheResults = true;
            }

            // Cache was not used
            return false;
        }

        // Cache was used
        return true;
    }

    /**
     * Delete the entry defined by $prefix-$id
     *
     * @param string $prefix
     * @param string $id
     */
    public static function deleteFromCache($prefix, $id = '') {
        $cache = new Cache($prefix, $id);
        $cache->delete();
    }

    /**
     * Gets the current cache version for all entries with prefix $prefix.
     *
     * @param string $prefix
     */
    private static function getVersion($prefix) {
        $key = 'v'.$prefix;
        return (int) CacheAdapter::getInstance()->entry($key, 0);
    }

    /**
     * Invalidate all entries that begin with $prefix.
     *
     * It does so by changing the current version used for these entries, so
     * old entries will never be fetched or updated again.
     *
     * @param string $prefix
     */
    public static function invalidateAllKeys($prefix) {
        if (!self::cacheEnabled()) {
            return;
        }
        $key = 'v'.$prefix;
        // Must do this in a loop to avoid race condition when two threads try
        // to invalidate the cache simultaneously.
        do {
            // Ensure the version key exists.
            $version = self::getVersion($prefix);
        } while (!CacheAdapter::getInstance()->cas($key, $version, $version + 1));
    }

    private static function cacheEnabled() {
        return defined('APC_USER_CACHE_ENABLED') &&
               APC_USER_CACHE_ENABLED === true;
    }

    /**
     * Invalidates all entries in the user cache.
     *
     * Only use this for testing purposes.
     */
    public static function clearCacheForTesting() {
        CacheAdapter::getInstance()->clear();
    }
}
