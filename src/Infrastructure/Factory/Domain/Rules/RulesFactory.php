<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Rules;

use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RulesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): Rules
    {
        $config = $container->get('config');
        $ruleConfig = $config[RulesInterface::class] ?? null;
        if (null === $ruleConfig) {
            return Rules::fromDefaults();
        }
        return new Rules($ruleConfig['betAmount'], $ruleConfig['startingPurse'], $ruleConfig['handPayouts']);
    }
}
