<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @return object
     */
    public function __invoke(ContainerInterface $container);
}