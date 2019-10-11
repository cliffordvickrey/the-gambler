<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Game\Factory;

use Cliffordvickrey\TheGambler\Domain\Game\Service\GameService;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GameServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new GameService(
            $container->get(HandTypeResolverInterface::class),
            $container->get(ProbabilityServiceInterface::class),
            $container->get(RulesInterface::class)
        );
    }

}