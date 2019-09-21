<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

class NullTtlCacheManifest implements TtlCacheManifestInterface
{
    public function get(string $key): ?int
    {
        return null;
    }

    public function set(string $key, ?int $ttl): void
    {
    }

    public function getKeys(): array
    {
        return [];
    }
}
