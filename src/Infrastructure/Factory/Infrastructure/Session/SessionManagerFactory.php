<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Session;

use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManager;
use Psr\Container\ContainerInterface;

class SessionManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new SessionManager();
    }

}