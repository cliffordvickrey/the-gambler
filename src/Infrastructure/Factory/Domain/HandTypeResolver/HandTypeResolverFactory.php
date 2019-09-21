<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\HandTypeResolver;

use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class HandTypeResolverFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): HandTypeResolver
    {
        return new HandTypeResolver();
    }
}