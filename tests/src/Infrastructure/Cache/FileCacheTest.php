<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\src\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\CacheException;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use function realpath;

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
     * @throws InvalidArgumentException
     */
    public function testGet(): void
    {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testGetWithTtl(): void
    {
        $this->cache->set('foo', 'bar', -1);
        $this->assertEquals(null, $this->cache->get('foo'));
        $this->assertEquals(-1, $this->ttl->getForReading()->get('foo'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetMultiple(): void
    {
        $toSet = ['foo1' => 'bar1', 'foo2' => 'bar2'];
        $this->cache->setMultiple($toSet);
        $multiple = $this->cache->getMultiple(['foo1', 'foo2']);
        $this->assertEquals($multiple['foo1'], 'bar1');
        $this->assertEquals($multiple['foo2'], 'bar2');
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testDelete(): void
    {
        $this->cache->set('foo', 'bar', 500);
        $this->cache->delete('foo');
        $manifest = $this->ttl->getForReading();
        $this->assertEquals(null, $manifest->get('foo'));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testDeleteMultiple(): void
    {
        $toSet = ['foo1' => 'bar1', 'foo2' => 'bar2'];
        $this->cache->setMultiple($toSet);
        $this->cache->deleteMultiple(['foo1', 'foo2']);
        $this->assertEquals(null, $this->cache->get('foo1'));
        $this->assertEquals(null, $this->cache->get('foo2'));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function testGarbageCollection(): void
    {
        $this->cache->set('bad1', 'nope', -1);
        $this->cache->set('bad2', 'nope', -1);
        $this->cache->set('good1', 'yay', 100);
        $this->cache->set('good2', 'yay', 100);

        $this->cache->runGarbageCollection();
        $manifest = $this->ttl->getForReading();
        $keys = $manifest->getKeys();

        $this->assertNotContains('bad1', $keys);
        $this->assertNotContains('bad2', $keys);
        $this->assertContains('good1', $keys);
        $this->assertContains('good2', $keys);
    }

    public function tearDown(): void
    {
        $this->cache->clear();
    }
}
