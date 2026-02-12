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
     * Default window size in seconds (1 hour).
     */
    private const WINDOW_SECONDS = 3600;

    /**
     * TTL for Redis keys (2 hours).
     * Set to 2x the window to ensure keys survive across bucket boundaries.
     */
    private const KEY_TTL_SECONDS = 7200;

    /**
     * Asserts that the given identity has not exceeded the rate limit
     * for the specified endpoint within the current time window.
     *
     * @param string $endpoint A unique identifier for the endpoint
     *                         (e.g., 'Group::apiCreate')
     * @param int $identityId  The identity ID of the user making the request
     * @param int $limit       Maximum number of requests allowed per window
     *
     * @throws \OmegaUp\Exceptions\RateLimitExceededException if the limit is exceeded
     */
    public static function assertWithinLimit(
        string $endpoint,
        int $identityId,
        int $limit
    ): void {
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
            $current = $cache->get();

            if ($current !== null && intval($current) >= $limit) {
                throw new \OmegaUp\Exceptions\RateLimitExceededException(
                    'contentCreationRateLimitExceeded'
                );
            }

            // Increment the counter (or initialize to 1).
            $cache->set(
                $current === null ? 1 : intval($current) + 1,
                self::KEY_TTL_SECONDS
            );
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
