<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Handler\HighScoresHandler;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class HighScoresHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new HighScoresHandler($container->get(HighScoresRepositoryInterface::class));
    }
}