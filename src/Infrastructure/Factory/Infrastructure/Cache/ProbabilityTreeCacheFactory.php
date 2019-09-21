<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest\NullTtlCacheManifestRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class ProbabilityTreeCacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): CacheInterface
    {
        $directory = __DIR__ . '/../../../../../data/probability';
        return new FileCache(
            $directory,
            $container->get(SerializerInterface::class),
            new NullTtlCacheManifestRepository(),
            false
        );
    }
}