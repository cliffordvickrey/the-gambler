<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\CacheException;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Traversable;
use function get_class;
use function iterator_to_array;

class FileCacheTest extends TestCase
{
    /** @var string */
    private $path;
    /** @var FileCache */
    private $cache;
    /** @var TtlCacheManifestRepositoryInterface */
    private $ttl;

    public function setUp(): void
    {
        $this->path = __DIR__ . '/../../../temp';
        $this->ttl = new TtlCacheManifestRepository($this->path);
        $this->cache = new FileCache($this->path, null, $this->ttl);
    }

    /**
     * @throws CacheException
     */
    public function testGet(): void
    {
        try {
            $this->cache->set('foo', 'bar');
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    /**
     * @throws CacheException
     */
    public function testGetWithTtl(): void
    {
        try {
            $this->cache->set('foo', 'bar', -1);
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }
        $this->assertEquals(null, $this->cache->get('foo'));
        $this->assertEquals(-1, $this->ttl->getForReading()->get('foo'));
    }

    public function testGetMultiple(): void
    {
        $toSet = ['foo1' => 'bar1', 'foo2' => 'bar2'];
        try {
            $this->cache->setMultiple($toSet);
            $multiple = $this->cache->getMultiple(['foo1', 'foo2']);
            if ($multiple instanceof Traversable) {
                $multiple = iterator_to_array($multiple);
            }
            $this->assertEquals($multiple['foo1'], 'bar1');
            $this->assertEquals($multiple['foo2'], 'bar2');
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }
    }

    /**
     * @throws CacheException
     */
    public function testDelete(): void
    {
        try {
            $this->cache->set('foo', 'bar', 500);
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }

        $this->cache->delete('foo');
        $manifest = $this->ttl->getForReading();
        $this->assertEquals(null, $manifest->get('foo'));
    }

    /**
     * @throws CacheException
     */
    public function testDeleteMultiple(): void
    {
        $toSet = ['foo1' => 'bar1', 'foo2' => 'bar2'];

        try {
            $this->cache->setMultiple($toSet);
            $this->cache->deleteMultiple(['foo1', 'foo2']);
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }

        $this->assertEquals(null, $this->cache->get('foo1'));
        $this->assertEquals(null, $this->cache->get('foo2'));
    }

    /**
     * @throws CacheException
     */
    public function testGarbageCollection(): void
    {
        try {
            $this->cache->set('bad1', 'nope', -1);
            $this->cache->set('bad2', 'nope', -1);
            $this->cache->set('good1', 'yay', 100);
            $this->cache->set('good2', 'yay', 100);
        } catch (InvalidArgumentException $e) {
            $this->fail('Unhandled ' . get_class($e));
        }

        $this->cache->runGarbageCollection();
        $manifest = $this->ttl->getForReading();
        $keys = $manifest->getKeys();

        $this->assertNotContains('bad1', $keys);
        $this->assertNotContains('bad2', $keys);
        $this->assertContains('good1', $keys);
        $this->assertContains('good2', $keys);
    }

    /**
     * @throws CacheException
     */
    public function tearDown(): void
    {
        $this->cache->clear();
    }
}
