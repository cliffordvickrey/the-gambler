<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\UpdateHighScoresMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UpdateHighScoresMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new UpdateHighScoresMiddleware($container->get(HighScoresRepositoryInterface::class));
    }
}