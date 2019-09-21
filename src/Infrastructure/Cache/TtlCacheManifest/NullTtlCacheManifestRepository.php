<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

class NullTtlCacheManifestRepository implements TtlCacheManifestRepositoryInterface
{
    public function delete(): void
    {
    }

    public function getForReading(): TtlCacheManifestInterface
    {
        return new NullTtlCacheManifest();
    }

    public function getForWriting(): TtlCacheManifestInterface
    {
        return new NullTtlCacheManifest();
    }

    public function save(TtlCacheManifestInterface $manifest): void
    {
    }
}