<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache;

interface GarbageCollectionInterface
{
    public function runGarbageCollection(): void;
}