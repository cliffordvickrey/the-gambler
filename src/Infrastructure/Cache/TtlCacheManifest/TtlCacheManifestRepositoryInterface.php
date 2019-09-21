<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

interface TtlCacheManifestRepositoryInterface
{
    public function delete(): void;

    public function getForReading(): TtlCacheManifestInterface;

    public function getForWriting(): TtlCacheManifestInterface;

    public function save(TtlCacheManifestInterface $manifest): void;
}