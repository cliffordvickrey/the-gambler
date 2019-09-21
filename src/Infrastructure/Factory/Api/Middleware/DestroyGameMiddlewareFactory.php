<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\DestroyGameMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;

class DestroyGameMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new DestroyGameMiddleware(
            $container->get(GameRepositoryInterface::class),
            $container->get(SessionManagerInterface::class)
        );
    }
}
