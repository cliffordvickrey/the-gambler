<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use function is_string;
use function rtrim;

abstract class AbstractFileCacheFactory implements FactoryInterface
{
    protected function getPath(ContainerInterface $container, string $name): string
    {
        $config = $container->get('config');
        $path = $config[FileCache::class]['root'] ?? null;

        if (!is_string($path)) {
            $path = __DIR__ . '/../../../../../data';
        } else {
            $path = rtrim($path, '/\\');
        }

        return $path . '/' . $name;
    }
}
