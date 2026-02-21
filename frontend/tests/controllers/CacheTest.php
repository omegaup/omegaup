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
}
