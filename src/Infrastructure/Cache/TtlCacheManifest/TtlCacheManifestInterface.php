<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

interface TtlCacheManifestInterface
{
    public function get(string $key): ?int;

    public function set(string $key, ?int $ttl): void;

    /**
     * @return string[]
     */
    public function getKeys(): array;
}