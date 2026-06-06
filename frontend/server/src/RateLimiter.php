<?php

namespace OmegaUp;

/**
 * Provides Redis-based rate limiting for API endpoints.
 *
 * Uses a fixed-window counter algorithm with hourly buckets.
 * Keys auto-expire via Redis TTL, so no cleanup is needed.
 *
 * If the cache backend is unavailable (e.g., Redis down),
 * the request is allowed through (fail-open design).
 */
class RateLimiter {
    /**
     * Whether rate limiting is enabled.
     * Can be disabled in tests via setForTesting().
     */
    private static bool $isEnabledForTesting = true;

    /**
     * Default window size in seconds (1 hour).
     */
    private const WINDOW_SECONDS = 3600;

    /**
     * TTL for Redis keys (2 hours).
     * Set to 2x the window to ensure keys survive across bucket boundaries.
     */
    private const KEY_TTL_SECONDS = 7200;

    private const DEFAULT_LIMITS = [
        'Course::apiCreate'     => 5,
        'Contest::apiCreate'    => 10,
        'Problem::apiCreate'    => 20,
        'Group::apiCreate'      => 5,
        'TeamsGroup::apiCreate' => 5,
        'School::apiCreate'     => 5,
    ];

    /**
     * Enable or disable rate limiting for testing purposes.
     */
    public static function setForTesting(bool $enabled): void {
        self::$isEnabledForTesting = $enabled;
    }

    /**
     * Asserts that the given identity has not exceeded the rate limit
     * for the specified endpoint within the current time window.
     *
     * System administrators are exempt from rate limiting.
     *
     * @param \OmegaUp\DAO\VO\Identities $identity The identity making the request
     * @param int $limit       Maximum number of requests allowed per window
     *
     * @throws \OmegaUp\Exceptions\RateLimitExceededException if the limit is exceeded
     */
    public static function assertWithinLimit(
        \OmegaUp\DAO\VO\Identities $identity,
        ?int $limit = null
    ): void {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        /** @var array{class?: string, function?: string, type?: string} $caller */
        $caller = $backtrace[1] ?? [];

        $callerClass = $caller['class'] ?? 'Unknown';
        $callerFunction = $caller['function'] ?? 'unknown';
        $callerType = $caller['type'] ?? '::';

        $lastBackslashPosition = strrpos($callerClass, '\\');
        $shortClass = $lastBackslashPosition === false
            ? $callerClass
            : substr($callerClass, $lastBackslashPosition + 1);

        $endpoint = "{$shortClass}{$callerType}{$callerFunction}";
        $limit = $limit ?? (self::DEFAULT_LIMITS[$endpoint] ?? 10);

        if (!self::$isEnabledForTesting) {
            return;
        }

        // System administrators are exempt from rate limiting
        if (\OmegaUp\Authorization::isSystemAdmin($identity)) {
            return;
        }

        $identityId = intval($identity->identity_id);

        // Calculate the current hourly bucket number.
        // All requests within the same hour share the same bucket.
        $bucket = intdiv(
            \OmegaUp\Time::get(),
            self::WINDOW_SECONDS
        );

        // Use Cache class to interact with the cache backend.
        // This properly handles autoloading (CacheAdapter lives in
        // Cache.php) and fail-open when cache is disabled.
        $cache = new \OmegaUp\Cache(
            'rate_limit',
            "{$endpoint}:{$identityId}:{$bucket}"
        );

        try {
            // Atomically increment the counter with TTL.
            // This avoids race conditions where multiple concurrent requests
            // could both read the same value and exceed the limit.
            $count = $cache->incWithTTL(self::KEY_TTL_SECONDS);

            if ($count > $limit) {
                throw new \OmegaUp\Exceptions\RateLimitExceededException(
                    'contentCreationRateLimitExceeded'
                );
            }
        } catch (\OmegaUp\Exceptions\RateLimitExceededException $e) {
            // Re-throw rate limit exceptions — these are intentional
            throw $e;
        } catch (\Exception $e) {
            // Fail-open: if Redis/cache is down, allow the request.
            // Log the error for monitoring but don't block the user.
            self::logCacheError($endpoint, $e);
        }
    }

    /**
     * Logs cache errors for monitoring purposes.
     *
     * @param string $endpoint The endpoint that experienced the error
     * @param \Exception $e The exception that occurred
     */
    private static function logCacheError(
        string $endpoint,
        \Exception $e
    ): void {
        error_log(
            "RateLimiter: Cache error for {$endpoint}: " .
            $e->getMessage()
        );
    }
}
