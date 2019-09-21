<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Handler\RulesHandler;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RulesHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new RulesHandler($container->get(RulesInterface::class));
    }

}
