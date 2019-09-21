<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\AuthenticationMiddleware;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;

class AuthenticationMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationMiddleware($container->get(SessionManagerInterface::class));
    }
}