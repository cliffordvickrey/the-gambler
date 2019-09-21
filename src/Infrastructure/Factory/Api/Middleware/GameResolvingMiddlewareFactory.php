<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\GameResolvingMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GameResolvingMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new GameResolvingMiddleware($container->get(GameRepositoryInterface::class));
    }

}