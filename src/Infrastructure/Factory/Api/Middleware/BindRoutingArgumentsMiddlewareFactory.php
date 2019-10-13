<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\BindRoutingArgumentsMiddleware;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use function is_string;

class BindRoutingArgumentsMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $apiRoot = '';
        if (isset($config['apiRoot']) && is_string($config['apiRoot'])) {
            $apiRoot = $config['apiRoot'];
        }

        return new BindRoutingArgumentsMiddleware($apiRoot);
    }
}
