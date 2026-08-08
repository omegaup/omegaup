<?php

class CacheTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A PHPUnit data provider for all cache adapter implementations.
     *
     * @return list<list<\OmegaUp\CacheAdapter>>
     */
    public function cacheAdapterProvider(): array {
        $adapters = [
            [new \OmegaUp\RedisCacheAdapter()],
            [new \OmegaUp\InProcessCacheAdapter()],
        ];
        if (apcu_enabled()) {
            array_push($adapters, [new \OmegaUp\APCCacheAdapter()]);
        }
        return $adapters;
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheEntry(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(1, $cache->entry($key, 1));
        $this->assertSame(1, $cache->entry($key, 2));
        $this->assertSame(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheAdd(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(true, $cache->add($key, 1));
        $this->assertSame(false, $cache->add($key, 2));
        $this->assertSame(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheCAS(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(false, $cache->cas($key, 0, 1));
        $this->assertSame(true, $cache->store($key, 0));
        $this->assertSame(true, $cache->cas($key, 0, 1));
        $this->assertSame(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheDelete(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(false, $cache->delete($key));
        $this->assertSame(true, $cache->store($key, 1));
        $this->assertSame(1, $cache->fetch($key));
        $this->assertSame(true, $cache->delete($key));
        $this->assertSame(false, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheStore(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(true, $cache->store($key, 1));
        $this->assertSame(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheInc(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(1, $cache->inc($key));
        $this->assertSame(2, $cache->inc($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheGetOrSet(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertSame(false, $cache->fetch($key));
        $this->assertSame(
            'hello!',
            $cache->getOrSet(
                $key,
                'random',
                function () {
                    return 'hello!';
                }
            )
        );
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheGetOrSetOnlyComputesOnce(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid('getorset-once-');
        $lockGroup = 'test-lock-group-' . uniqid();
        $invocations = 0;
        $callback = function () use (&$invocations) {
            $invocations++;
            return 'computed';
        };

        $this->assertSame(false, $cache->fetch($key));

        $this->assertSame(
            'computed',
            $cache->getOrSet(
                $key,
                $lockGroup,
                $callback,
                60
            )
        );
        $this->assertSame(1, $invocations);

        $this->assertSame(
            'computed',
            $cache->getOrSet(
                $key,
                $lockGroup,
                $callback,
                60
            )
        );
        $this->assertSame(1, $invocations);
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheIncWithTTL(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid('incwithttl-');
        $ttl = 2; // 2 seconds TTL

        // First increment should return 1
        $this->assertSame(1, $cache->incWithTTL($key, $ttl));

        // Second increment should return 2
        $this->assertSame(2, $cache->incWithTTL($key, $ttl));

        // Third increment should return 3
        $this->assertSame(3, $cache->incWithTTL($key, $ttl));

        // Verify by doing another increment (should be 4)
        $this->assertSame(4, $cache->incWithTTL($key, $ttl));

        // TTL expiration test only for Redis and APCu
        // InProcessCacheAdapter doesn't support TTL
        if (!($cache instanceof \OmegaUp\InProcessCacheAdapter)) {
            // Wait for TTL to expire
            sleep($ttl + 1);

            // After expiry, should start at 1 again
            $this->assertSame(1, $cache->incWithTTL($key, $ttl));
        }
    }

    /**
     * A counter written by incWithTTL() must be readable by fetch(): both must
     * agree on the storage format.
     *
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheIncWithTTLThenFetch(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid('incwithttl-fetch-');
        $ttl = 60;

        $this->assertSame(1, $cache->incWithTTL($key, $ttl));
        $this->assertSame(2, $cache->incWithTTL($key, $ttl));

        // fetch() must return the counter, not treat it as a miss.
        $this->assertSame(2, $cache->fetch($key));
    }

    /**
     * inc() and incWithTTL() must share the same storage format, so a counter
     * started with one can be continued with the other.
     *
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheIncThenIncWithTTL(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid('inc-incwithttl-');
        $ttl = 60;

        // inc() stores using the serialized format.
        $this->assertSame(1, $cache->inc($key));

        // incWithTTL() must read that value and continue, not reset or return 0.
        $this->assertSame(2, $cache->incWithTTL($key, $ttl));
        $this->assertSame(3, $cache->incWithTTL($key, $ttl));

        $this->assertSame(3, $cache->fetch($key));
    }

    /**
     * Regression test for #10074: incWithTTL() must preserve the existing TTL
     * of a key whose serialized value is `i:0;`, not overwrite it with the
     * argument TTL. A key stored with value 0 and a short TTL, then incremented
     * with a much larger argument TTL, must expire at the original (short) TTL.
     *
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheIncWithTTLPreservesTTLOnZeroValue(
        \OmegaUp\CacheAdapter $cache
    ): void {
        if ($cache instanceof \OmegaUp\InProcessCacheAdapter) {
            // InProcessCacheAdapter does not support TTL.
            return;
        }
        $key = uniqid('incwithttl-zero-');
        $originalTtl = 3;
        $argumentTtl = 30;

        // Store value 0 with a short TTL (writes the serialized `i:0;`).
        $this->assertTrue($cache->store($key, 0, $originalTtl));

        // Increment with a much larger argument TTL. If the bug were present,
        // the key's TTL would be reset to $argumentTtl (30s); with the fix, the
        // original 3s TTL is preserved.
        $this->assertSame(1, $cache->incWithTTL($key, $argumentTtl));

        // Wait past the original TTL but well before the argument TTL.
        sleep($originalTtl + 5);

        // The key must have expired at the original (short) TTL, so the next
        // increment starts again at 1.
        $this->assertSame(1, $cache->incWithTTL($key, $argumentTtl));
    }
}
