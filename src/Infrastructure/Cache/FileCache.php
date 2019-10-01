<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\NullTtlCacheManifestRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\TtlCacheManifestRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\PhpSerializer;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;
use UnexpectedValueException;
use function array_map;
use function fclose;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function flock;
use function fopen;
use function fwrite;
use function glob;
use function is_bool;
use function is_dir;
use function is_file;
use function is_resource;
use function is_string;
use function mkdir;
use function preg_replace;
use function rtrim;
use function sprintf;
use function stream_get_contents;
use function time;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use const LOCK_SH;
use const LOCK_UN;

class FileCache extends AbstractCache implements GarbageCollectionInterface
{
    private $directory;
    /** @var SerializerInterface */
    private $serializer;
    private $ttlSupported;
    /** @var TtlCacheManifestRepositoryInterface */
    private $ttlCacheManifestRepository;
    private $lockSupported;

    public function __construct(
        string $directory,
        ?SerializerInterface $serializer = null,
        ?TtlCacheManifestRepositoryInterface $ttlCacheManifestRepository = null,
        bool $lockSupported = true
    )
    {
        $directory = rtrim($directory, '/\\');

        if (!is_dir($directory) && !mkdir($directory)) {
            throw new InvalidArgumentException(sprintf('Directory "%s" is invalid', $directory));
        }

        $this->directory = $directory;

        if (null === $serializer) {
            $serializer = new PhpSerializer();
        }

        $this->serializer = $serializer;

        if (null === $ttlCacheManifestRepository) {
            $ttlCacheManifestRepository = new TtlCacheManifestRepository($directory);
        }

        $this->ttlCacheManifestRepository = $ttlCacheManifestRepository;
        $this->ttlSupported = !($ttlCacheManifestRepository instanceof NullTtlCacheManifestRepository);
        $this->lockSupported = $lockSupported;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|string|null
     * @throws CacheException
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        $fileName = self::keyToFileName($key);
        $contents = $this->fileGetContents($fileName);

        if (null === $contents) {
            return $default;
        }

        return $this->serializer->unSerialize($contents);
    }

    /**
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function has($key)
    {
        self::assertString($key);
        $fileName = $this->keyToFileName($key);

        if (!is_file($fileName)) {
            return false;
        }

        if ($this->ttlSupported) {
            $modDate = filemtime($fileName);
            if (false === $modDate) {
                throw new CacheException(sprintf('Could not get modification date of "%s"', $fileName));
            }

            $manifest = $this->ttlCacheManifestRepository->getForReading();

            $ttl = $manifest->get($key);
            if (null !== $ttl && time() > ($modDate + $ttl)) {
                return false;
            }
        }

        return true;
    }

    private static function assertString($key)
    {
        if (!is_string($key)) {
            throw new UnexpectedValueException('Expected a string');
        }
    }

    private function keyToFileName(string $key): string
    {
        $sanitizedKey = preg_replace('/[^a-z0-9]+/i', '-', $key);
        return sprintf('%s%s%s.txt', $this->directory, DIRECTORY_SEPARATOR, $sanitizedKey);
    }

    /**
     * @param string $fileName
     * @return string|null
     * @throws CacheException
     */
    private function fileGetContents(string $fileName): ?string
    {
        if (!is_file($fileName)) {
            return null;
        }

        if (!$this->lockSupported) {
            $contents = file_get_contents($fileName);
            if (false === $contents) {
                throw new CacheException(sprintf('Could not open file "%s" for reading', $fileName));
            }
            return $contents;
        }

        $resource = fopen($fileName, 'r');
        if (false === $resource) {
            throw new CacheException(sprintf('Could not open file "%s" for reading', $fileName));
        }

        $locked = flock($resource, LOCK_SH);
        if (!$locked) {
            throw new CacheException(sprintf('Could not acquire a shared lock on file "%s"', $fileName));
        }

        try {
            $fileContents = stream_get_contents($resource);
            if (false === $fileContents) {
                throw new CacheException(sprintf('Failed to read contents of file "%s"', $fileName));
            }
            return $fileContents;
        } catch (CacheException $e) {
            throw $e;
        } finally {
            flock($resource, LOCK_UN);
            fclose($resource);
        }
    }

    /**
     * (@inheritDoc)
     * @throws CacheException
     */
    public function set($key, $value, $ttl = null)
    {
        self::assertString($key);
        $fileName = $this->keyToFileName($key);
        $serialized = $this->serializer->serialize($value);
        $this->filePutContents($fileName, $serialized);

        if ($this->ttlSupported) {
            if ($ttl instanceof DateInterval) {
                $ttl = self::dateIntervalToSeconds($ttl);
            }

            $manifest = $this->ttlCacheManifestRepository->getForWriting();
            $manifest->set($key, $ttl);
            $this->ttlCacheManifestRepository->save($manifest);
        }

        return true;
    }

    /**
     * @param string $fileName
     * @param string $contents
     * @throws CacheException
     */
    private function filePutContents(string $fileName, string $contents): void
    {
        if (!$this->lockSupported) {
            $bytesWritten = file_put_contents($fileName, $contents);
            if (false === $bytesWritten) {
                throw new CacheException(sprintf('Failed to write to file "%s"', $fileName));
            }
            return;
        }

        $resource = fopen($fileName, 'w');

        if (!is_resource($resource)) {
            throw new CacheException(sprintf('Failed to open "%s" for writing', $fileName));
        }

        $locked = flock($resource, LOCK_EX);
        if (!$locked) {
            throw new CacheException(sprintf('Could not acquire an exclusive lock on file "%s"', $fileName));
        }

        try {
            $bytesWritten = fwrite($resource, $contents);
            if (false === $bytesWritten) {
                throw new CacheException(sprintf('Failed to write to file "%s"', $fileName));
            }
        } catch (CacheException $e) {
            throw $e;
        } finally {
            flock($resource, LOCK_UN);
            fclose($resource);
        }
    }

    private static function dateIntervalToSeconds(DateInterval $dateInterval): int
    {
        $start = new DateTimeImmutable();
        $end = $start->add($dateInterval);
        return $end->getTimestamp() - $start->getTimestamp();
    }

    /**
     * @return bool
     * @throws CacheException
     */
    public function clear()
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*');
        if (is_bool($files)) {
            throw new CacheException('Failed to glob files for deletion');
        }
        array_map('unlink', $files);
        return true;
    }

    /**
     * @throws CacheException
     */
    public function runGarbageCollection(): void
    {
        if (!$this->ttlSupported) {
            return;
        }

        $manifest = $this->ttlCacheManifestRepository->getForReading();

        $expiredKeys = array_filter($manifest->getKeys(), function ($key): bool {
            return !$this->has($key);
        });

        array_map(function ($key): bool {
            try {
                return $this->delete($key);
            } catch (Throwable $e) {
                return false;
            }
        }, $expiredKeys);
    }

    /**
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function delete($key)
    {
        self::assertString($key);
        $fileName = $this->keyToFileName($key);

        if (is_file($fileName)) {
            try {
                unlink($fileName);
            } catch (Throwable $e) {
                throw new CacheException(sprintf('Could not delete "%s"', $fileName));
            }
        }

        if ($this->ttlSupported) {
            $manifest = $this->ttlCacheManifestRepository->getForWriting();
            $manifest->set($key, null);
            $this->ttlCacheManifestRepository->save($manifest);
        }

        return true;
    }
}
