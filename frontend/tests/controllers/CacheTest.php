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
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(1, $cache->entry($key, 1));
        $this->assertEquals(1, $cache->entry($key, 2));
        $this->assertEquals(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheAdd(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(true, $cache->add($key, 1));
        $this->assertEquals(false, $cache->add($key, 2));
        $this->assertEquals(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheCAS(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(false, $cache->cas($key, 0, 1));
        $this->assertEquals(true, $cache->store($key, 0));
        $this->assertEquals(true, $cache->cas($key, 0, 1));
        $this->assertEquals(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheDelete(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(false, $cache->delete($key));
        $this->assertEquals(true, $cache->store($key, 1));
        $this->assertEquals(1, $cache->fetch($key));
        $this->assertEquals(true, $cache->delete($key));
        $this->assertEquals(false, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheStore(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(true, $cache->store($key, 1));
        $this->assertEquals(1, $cache->fetch($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheInc(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(1, $cache->inc($key));
        $this->assertEquals(2, $cache->inc($key));
    }

    /**
     * @dataProvider cacheAdapterProvider
     */
    public function testCacheGetOrSet(\OmegaUp\CacheAdapter $cache) {
        $key = uniqid();
        $this->assertEquals(false, $cache->fetch($key));
        $this->assertEquals(
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
}
