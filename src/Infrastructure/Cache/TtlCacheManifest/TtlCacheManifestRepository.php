<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\CacheException;
use InvalidArgumentException;
use Throwable;
use function fclose;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function is_array;
use function is_file;
use function json_decode;
use function json_encode;
use function rewind;
use function sprintf;
use function stream_get_contents;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const LOCK_SH;
use const LOCK_UN;

class TtlCacheManifestRepository implements TtlCacheManifestRepositoryInterface
{
    const FILE_NAME = 'ttl-manifest.json';

    private $fileName;
    /** @var resource */
    private $resource;

    public function __construct(string $directory)
    {
        $directory = rtrim($directory, '/\\');

        if (!is_dir($directory)) {
            throw new InvalidArgumentException(sprintf('Directory "%s" is invalid', $directory));
        }

        $this->fileName = sprintf('%s%s%s', $directory, DIRECTORY_SEPARATOR, self::FILE_NAME);
    }

    /**
     * @return TtlCacheManifestInterface
     * @throws CacheException
     */
    public function getForReading(): TtlCacheManifestInterface
    {
        return $this->getFromResource();
    }

    /**
     * @param bool $exclusiveLock
     * @return TtlCacheManifestInterface
     * @throws CacheException
     */
    private function getFromResource(bool $exclusiveLock = false): TtlCacheManifestInterface
    {
        $this->initResource($exclusiveLock);

        $manifest = null;

        try {
            $contents = stream_get_contents($this->resource);

            if ($exclusiveLock) {
                rewind($this->resource);
            }

            if (false === $contents) {
                throw new CacheException('Failed to read contents of cache TTL manifest');
            }
            $manifest = json_decode($contents, true);
        } catch (CacheException $e) {
            throw $e;
        } finally {
            if (!$exclusiveLock) {
                $this->destroyResource();
            }
        }

        if (!is_array($manifest)) {
            $manifest = [];
        }
        return new TtlCacheManifest($manifest);
    }

    /**
     * @param bool $forWriting
     * @throws CacheException
     */
    private function initResource(bool $forWriting = false): void
    {
        if (null !== $this->resource) {
            return;
        }

        $resource = fopen($this->fileName, $forWriting ? 'c+' : 'r');
        if (false === $resource) {
            throw new CacheException(sprintf('Could not open file "%s" for reading and writing', $this->resource));
        }

        $locked = flock($resource, $forWriting ? LOCK_UN : LOCK_SH);
        if (!$locked) {
            throw new CacheException('Could not acquire a lock on file "%s"', $this->resource);
        }

        $this->resource = $resource;
    }

    private function destroyResource(): void
    {
        if (null === $this->resource) {
            return;
        }
        flock($this->resource, LOCK_UN);
        fclose($this->resource);
        $this->resource = null;
    }

    /**
     * @return TtlCacheManifestInterface
     * @throws CacheException
     */
    public function getForWriting(): TtlCacheManifestInterface
    {
        return $this->getFromResource(true);
    }

    /**
     * @throws CacheException
     */
    public function delete(): void
    {
        if (is_file($this->fileName)) {
            $this->save(new TtlCacheManifest([]));
            try {
                unlink($this->fileName);
            } catch (Throwable $e) {

            }
        }
    }

    /**
     * @param TtlCacheManifestInterface $manifest
     * @throws CacheException
     */
    public function save(TtlCacheManifestInterface $manifest): void
    {
        $this->initResource(true);

        try {
            $json = json_encode($manifest);

            ftruncate($this->resource, 0);
            rewind($this->resource);
            $bytesWritten = fwrite($this->resource, $json);
            if (false === $bytesWritten) {
                throw new CacheException('Could not write to cache TTL manifest');
            }
        } catch (CacheException $e) {
            throw $e;
        } finally {
            $this->destroyResource();
        }
    }

}
