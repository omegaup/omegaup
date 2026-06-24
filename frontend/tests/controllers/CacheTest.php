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
            /** @var int $invocations Help static analyzers understand this captured variable. */
            $invocations = 0;
        /** @psalm-suppress UndefinedVariable */
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
}
