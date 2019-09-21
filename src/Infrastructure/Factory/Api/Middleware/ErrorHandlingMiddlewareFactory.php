<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\ErrorHandlingMiddleware;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ErrorHandlingMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $debug = $config['debug'] ?? false;
        return new ErrorHandlingMiddleware($debug);
    }
}
