<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Probability\Service;

use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityService;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\ProbabilityTreeCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProbabilityServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        return new ProbabilityService(
            $container->get(ProbabilityTreeCacheInterface::class),
            $container->get(HandTypeResolverInterface::class),
            $container->get(RulesInterface::class)
        );
    }

}