<?php

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
    const PROBLEMS_SOLVED_RANK_LIST = 'problems-solved-rank-list';
    const CONTESTS_LIST_PUBLIC = 'contest-list-public';
    const CONTESTS_LIST_SYSTEM_ADMIN = 'contest-list-sys-admin';
    const CONTESTS_LIST_USER_ID = 'contest-list-user-id';

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
     * @param int $timeout
     * @return boolean
     */
    public function set($value, $timeout = 0) {
        if ($this->enabled === true) {
            if (apc_store($this->key, $value, $timeout) === true) {
                $this->log->debug('apc_stored successful for key: ' . $this->key);
                return true;
            } else {
                $this->log->debug('apc_store failed for key: ' . $this->key);
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
            if (apc_delete($this->key) === true) {
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
            if (($result = apc_fetch($this->key)) !== false) {
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
     * @param int $timeout
     * @return boolean
     */
    public static function getFromCacheOrSet($prefix, $id, Request $r, $setFunc, &$returnValue, $timeout = 0) {
        $cache = new Cache($prefix, $id);
        $returnValue = $cache->get();

        // If there wasn't a value in the cache for the key ($prefix, $id)
        if (is_null($returnValue)) {
            // Get the value from the function provided
            $returnValue = call_user_func($setFunc, $r);

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
        $version = apc_fetch('v'.$prefix);
        if ($version === false) {
            apc_store('v'.$prefix, 0);
            return 0;
        }
        return $version;
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
        if (self::cacheEnabled()) {
            apc_inc('v'.$prefix, 1, $success);
            if (!$success) {
                apc_store('v'.$prefix, 1);
            }
        }
    }

    private static function cacheEnabled() {
        return defined('APC_USER_CACHE_ENABLED') &&
               APC_USER_CACHE_ENABLED === true;
    }
}
