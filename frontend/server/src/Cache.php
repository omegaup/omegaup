<?php

namespace OmegaUp;

/**
 * A class that abstracts away support for Redis / APC user cache for PHP7 / Travis.
 */
abstract class CacheAdapter {
    /** @var CacheAdapter|null */
    private static $_instance = null;

    public static function getInstance(): CacheAdapter {
        if (is_null(CacheAdapter::$_instance)) {
            /** @psalm-suppress TypeDoesNotContainType OMEGAUP_CACHE_IMPLEMENTATION is really a variable */
            if (OMEGAUP_CACHE_IMPLEMENTATION === 'redis') {
                CacheAdapter::$_instance = new RedisCacheAdapter();
            /** @psalm-suppress TypeDoesNotContainType OMEGAUP_CACHE_IMPLEMENTATION is really a variable */
            } elseif (OMEGAUP_CACHE_IMPLEMENTATION === 'apcu') {
                CacheAdapter::$_instance = new APCCacheAdapter();
            } else {
                CacheAdapter::$_instance = new InProcessCacheAdapter();
            }
        }
        return CacheAdapter::$_instance;
    }

    /**
     * @psalm-template T
     * @param string $key
     * @param T $defaultVar
     * @param int $ttl
     * @return T
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

    public function inc(string $key): int {
        // Must do this in a loop to avoid race condition when two threads try
        // to invalidate the cache simultaneously.
        do {
            // Ensure the version key exists.
            $version = $this->entry($key, 0);
            $newVersion = $version + 1;
        } while (
            !$this->cas(
                $key,
                $version,
                $newVersion
            )
        );
        return $newVersion;
    }

    /**
     * If the specified $id exists in cache, gets its associated value from the
     * cache.  Otherwise, executes $setFunc() to generate the associated
     * value, stores it, and returns it.
     *
     * @template T
     *
     * @param string $key
     * @param string $lockGroup
     * @param callable():T $setFunc
     * @param int $timeout (seconds)
     * @param ?bool &$cacheUsed Whether the $id had a pre-computed value in the cache.
     *
     * @return T the value returned from the cache or $setFunc().
     */
    public function getOrSet(
        string $key,
        string $lockGroup,
        callable $setFunc,
        int $timeout = 0,
        ?bool &$cacheUsed = null
    ) {
        /** @var false|T */
        $returnValue = $this->fetch($key);

        // If there was a value in the cache for the key.
        if ($returnValue !== false) {
            if (!is_null($cacheUsed)) {
                $cacheUsed = true;
            }
            return $returnValue;
        }
        // Get a lock to prevent multiple requests from trying to create the
        // same cache entry. The name of the lockfile is derived from the
        // provided lock group, not the full key, since it is still preferred
        // to rate-limit all possible cache interactions with the same lock
        // group, since they typically deal with pagination. That way multiple
        // independent caches can still make progress.
        //
        // This is preferred over apcu_entry() because that function grabs a
        // *global* lock that blocks every single APCu function call!
        $lockFile = '/tmp/omegaup-cache-' . sha1($lockGroup) . '.lock';

        $f = fopen($lockFile, 'w');
        try {
            flock($f, LOCK_EX);

            // Maybe by the time we acquired the lock it had already been
            // populated by another request.
            /** @var false|T */
            $returnValue = $this->fetch($key);
            if ($returnValue !== false) {
                if (!is_null($cacheUsed)) {
                    $cacheUsed = true;
                }
                return $returnValue;
            }

            // Get the value from the function provided
            /** @var T */
            $returnValue = call_user_func($setFunc);
            $this->store($key, $returnValue, $timeout);

            if (!is_null($cacheUsed)) {
                $cacheUsed = false;
            }
            return $returnValue;
        } finally {
            flock($f, LOCK_UN);
            fclose($f);
            // By the time the code reaches this point, it might be the case
            // that another request managed to reach the critical section and
            // also opened the file. In that case, it could be the case that
            // multiple requests will attempt to unlink the lock file, so it's
            // totally okay for them to fail.
            //
            // Additionally, since this is performed after the call to set(),
            // even if we somehow managed to unlink the file between the time
            // another request checked if the cache was set and it tried to
            // open the lockfile (thus opening a completely unrelated file that
            // is not synchronized with the current one at all), that other
            // request will finish acquiring the lockfile and then see that the
            // cache value had already been set, causing no additional
            // evaluations of $setFunc.
            @unlink($lockFile);
        }
    }
}

/**
 * Implementation of CacheAdapter that uses Redis.
 */
class RedisCacheAdapter extends CacheAdapter {
    private const LOCK_TTL_MS = 30000;
    private const LOCK_POLL_INTERVAL_MS = 50;

    /** @var \Redis $redis
     * @readonly
     */
    private $redis;

    public function __construct() {
        $this->redis = new \Redis();
        $this->redis->pconnect(REDIS_HOST, REDIS_PORT);
        /** @psalm-suppress RedundantCondition REDIS_PASS is really a variable */
        if (REDIS_PASS !== '' && !$this->redis->auth(REDIS_PASS)) {
            throw new \Exception('Redis authentication failed');
        }
    }

    /**
     * @psalm-template T
     * @param string $key
     * @param T $defaultVar
     * @param int $ttl
     * @return T
     */
    public function entry(string $key, $defaultVar, int $ttl = 0) {
        $flags = ['nx'];
        if ($ttl > 0) {
            $flags['ex'] = $ttl;
        }
        while (true) {
            $this->redis->watch($key);
            /** @var string|false $current */
            $current = $this->redis->get($key);
            if ($current !== false) {
                $this->redis->unwatch();
                /** @var T */
                return unserialize($current);
            }

            /** @psalm-suppress InvalidMethodCall calls in a pipeline return a Redis */
            if (
                $this->redis->multi()->set(
                    $key,
                    serialize($defaultVar)
                )->exec()
            ) {
                return $defaultVar;
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     * @return bool
     */
    public function add(string $key, $var, int $ttl = 0): bool {
        $flags = ['nx'];
        if ($ttl > 0) {
            $flags['ex'] = $ttl;
        }
        /** @var bool */
        return $this->redis->set($key, serialize($var), $flags);
    }

    public function cas(string $key, int $old, int $new): bool {
        while (true) {
            $this->redis->watch($key);
            /** @var string|false $current */
            $current = $this->redis->get($key);
            if ($current === false || unserialize($current) !== $old) {
                $this->redis->unwatch();
                return false;
            }
            /** @psalm-suppress InvalidMethodCall calls in a pipeline return a Redis */
            if ($this->redis->multi()->set($key, serialize($new))->exec()) {
                break;
            }
        }
        return true;
    }

    public function clear(): void {
        $this->redis->flushall();
    }

    public function delete(string $key): bool {
        return $this->redis->del($key) == 1;
    }

    /**
     * @param string $key
     */
    public function fetch(string $key): mixed {
        /** @var string|false $ret */
        $ret = $this->redis->get($key);
        if ($ret === false) {
            return false;
        }
        return unserialize($ret);
    }

    /**
     * @param string $key
     * @param mixed $var
     * @param int $ttl
     */
    public function store(string $key, $var, int $ttl = 0): bool {
        $flags = [];
        if ($ttl > 0) {
            $flags['ex'] = $ttl;
        }
        /** @var bool */
        return $this->redis->set($key, serialize($var), $flags);
    }

    public function inc(string $key): int {
        $current = 0;
        while (true) {
            $this->redis->watch($key);
            /** @var string|false $current */
            $current = $this->redis->get($key);
            if ($current !== false) {
                /** @var int */
                $current = unserialize($current);
            } else {
                $current = 0;
            }
            $current++;
            /** @psalm-suppress InvalidMethodCall calls in a pipeline return a Redis */
            if ($this->redis->multi()->set($key, serialize($current))->exec()) {
                break;
            }
        }
        return $current;
    }

    /**
     * @return array{0: string, 1: string}|false
     */
    private function acquireLock(string $lockGroup): array|false {
        $lockKey = 'omegaup-cache-lock-' . sha1($lockGroup);
        $token = bin2hex(random_bytes(16));
        $acquired = $this->redis->set(
            $lockKey,
            $token,
            ['nx', 'px' => self::LOCK_TTL_MS]
        );
        return $acquired ? [$lockKey, $token] : false;
    }

    private function releaseLock(string $lockKey, string $token): void {
        $script = <<<LUA
        if redis.call('get', KEYS[1]) == ARGV[1] then
            return redis.call('del', KEYS[1])
        else
            return 0
        end
        LUA;
        $this->redis->eval($script, [$lockKey, $token], 1);
    }

    /**
     * If the specified $id exists in cache, gets its associated value from the
     * cache.  Otherwise, executes $setFunc() to generate the associated
     * value, stores it, and returns it.
     *
     * @template T
     *
     * @param string $key
     * @param string $lockGroup
     * @param callable():T $setFunc
     * @param int $timeout (seconds)
     * @param ?bool &$cacheUsed Whether the $id had a pre-computed value in the cache.
     *
     * @return T the value returned from the cache or $setFunc().
     */
    public function getOrSet(
        string $key,
        string $lockGroup,
        callable $setFunc,
        int $timeout = 0,
        ?bool &$cacheUsed = null
    ) {
        /** @var false|T */
        $current = $this->fetch($key);
        if ($current !== false) {
            if (!is_null($cacheUsed)) {
                $cacheUsed = true;
            }
            return $current;
        }

        $lock = $this->acquireLock($lockGroup);
        while ($lock === false) {
            $current = $this->fetch($key);
            if ($current !== false) {
                if (!is_null($cacheUsed)) {
                    $cacheUsed = true;
                }
                /** @var T */
                return $current;
            }
            usleep(self::LOCK_POLL_INTERVAL_MS * 1000);
            $lock = $this->acquireLock($lockGroup);
        }

        [$lockKey, $token] = $lock;
        try {
            /** @var false|T */
            $current = $this->fetch($key);
            if ($current !== false) {
                if (!is_null($cacheUsed)) {
                    $cacheUsed = true;
                }
                return $current;
            }

            /** @var T */
            $returnValue = call_user_func($setFunc);
            $this->store($key, $returnValue, $timeout);

            if (!is_null($cacheUsed)) {
                $cacheUsed = false;
            }
            return $returnValue;
        } finally {
            $this->releaseLock($lockKey, $token);
        }
    }
}

/**
 * Implementation of CacheAdapter that uses the real APC functions.
 */
class APCCacheAdapter extends CacheAdapter {
    /**
     * @psalm-template T
     * @param string $key
     * @param T $defaultVar
     * @param int $ttl
     * @return T
     */
    public function entry(string $key, $defaultVar, int $ttl = 0) {
        /** @var T */
        return apcu_entry(
            $key,
            fn (string $key) => $defaultVar,
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
     * @psalm-template T
     * @param string $key
     * @param T $defaultVar
     * @param int $ttl
     * @return T
     */
    public function entry(string $key, $defaultVar, int $ttl = 0) {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $defaultVar;
        }
        /** @var T */
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
    const AUTHORS_RANK = 'authors-rank-';
    const AUTHORS_RANK_WITH_QUALITY_PROBLEMS = 'authors-rank-quality-problems-';
    const SESSION_PREFIX = 'session-';
    const CONTESTANT_SCOREBOARD_PREFIX = 'scoreboard-';
    const ADMIN_SCOREBOARD_PREFIX = 'scoreboard-admin-';
    const CONTESTANT_SCOREBOARD_EVENTS_PREFIX = 'scoreboard-events-';
    const ADMIN_SCOREBOARD_EVENTS_PREFIX = 'scoreboard-events-admin-';
    const CODERS_OF_THE_MONTH = 'coders-of-the-month';
    const CONTEST_INFO = 'contest-info-';
    const PROBLEM_SETTINGS = 'problem-settings-json-';
    const PROBLEM_SETTINGS_DISTRIB = 'problem-settings-distrib-json-';
    const PROBLEM_STATEMENT = 'statement-';
    const PROBLEM_SOLUTION = 'solution-';
    const PROBLEM_CDP_DATA = 'problem-cdp-data-';
    const PROBLEM_SOLUTION_EXISTS = 'solution-exists-';
    const PROBLEM_STATS = 'problem-stats-';
    const PROBLEMS_LIST = 'problems-list-';
    const RUN_ADMIN_DETAILS = 'run-admin-details-';
    const RUN_COUNTS = 'run-counts-';
    const RUN_TOTAL_COUNTS = 'run-total-counts';
    const USER_PROFILE = 'profile-';
    const USER_COMPARE_DATA = 'user-compare-data-';
    const PROBLEMS_SOLVED_RANK = 'problems-solved-rank-';
    const CONTESTS_LIST_PUBLIC = 'contest-list-public';
    const CONTESTS_CONTESTANTS_LIST = 'contest-contestants-list';
    const CONTESTS_LIST_SYSTEM_ADMIN = 'contest-list-sys-admin';
    const CONTESTS_LIST_USER_ID = 'contest-list-user-id';
    const SCHOOL_RANK = 'school-rank';
    const SCHOOL_STUDENTS_PROGRESS = 'school-students-progress-';
    const SCHOOLS_OF_THE_MONTH = 'schools-of-the-month';
    const TAGS_LIST = 'tags-list-';
    const PROBLEM_CASES_CONTENTS = 'problem-cases-contents-';
    const DATA_CASES_FILES = 'data-cases-files-';
    const PROBLEM_CASES_METADATA = 'problem-cases-metadata-';
    const PROBLEM_IDENTITY_TYPE = 'problems-identity-type-';

    /** @var \Monolog\Logger */
    private $log;

    /** @var string */
    protected $key;

    /**
     * Inicializa el cache para el key dado
     * @param string $key el id del cache
     */
    public function __construct(string $prefix, string $id = '') {
        $this->log = \Monolog\Registry::omegaup()->withName('cache');

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
     *
     * @return bool
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
     * @return bool
     */
    public function delete(): bool {
        if (!self::isEnabled()) {
            return false;
        }
        if (CacheAdapter::getInstance()->delete($this->key) !== true) {
            $this->log->debug(
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
            $this->log->debug("Cache miss for key: {$this->key}");
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
     * @template T
     *
     * @param string $prefix
     * @param string $id
     * @param callable():T $setFunc
     * @param int $timeout (seconds)
     * @param ?bool &$cacheUsed Whether the $id had a pre-computed value in the cache.
     *
     * @return T the value returned from the cache or $setFunc().
     */
    public static function getFromCacheOrSet(
        string $prefix,
        string $id,
        callable $setFunc,
        int $timeout = 0,
        ?bool &$cacheUsed = null
    ) {
        $cache = new \OmegaUp\Cache($prefix, $id);
        /** @var null|T */
        return CacheAdapter::getInstance()->getOrSet(
            $cache->key,
            $prefix,
            $setFunc,
            $timeout,
            $cacheUsed
        );
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
        return CacheAdapter::getInstance()->entry("v{$prefix}", 0);
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
        CacheAdapter::getInstance()->inc("v{$prefix}");
    }

    private static function isEnabled(): bool {
        /** @psalm-suppress RedundantCondition OMEGAUP_CACHE_IMPLEMENTATION is really a variable */
        return defined('OMEGAUP_CACHE_IMPLEMENTATION') &&
            OMEGAUP_CACHE_IMPLEMENTATION !== '';
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
