<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\FlushStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\FourOfAKindStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\FullHouseStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\HandAnalyzerStrategyInterface;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\JacksOrBetterStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\RoyalFlushStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\StraightFlushStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\StraightStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\ThreeOfAKindStrategy;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\TwoPairStrategy;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;

class HandTypeResolver implements HandTypeResolverInterface
{
    /**
     * @var HandAnalyzerStrategyInterface[]
     */
    private $strategies;

    public function __construct(?array $strategies = null)
    {
        if (null === $strategies) {
            $this->strategies = $this->buildDefaultStrategies();
            return;
        }

        $this->strategies = $strategies;
    }

    /**
     * @return HandAnalyzerStrategyInterface[]
     */
    private function buildDefaultStrategies(): array
    {
        return [
            new JacksOrBetterStrategy(),
            new TwoPairStrategy(),
            new ThreeOfAKindStrategy(),
            new StraightStrategy(),
            new FlushStrategy(),
            new FullHouseStrategy(),
            new FourOfAKindStrategy(),
            new StraightFlushStrategy(),
            new RoyalFlushStrategy()
        ];
    }

    public function resolve(Hand $hand): HandType
    {
        $decorator = new HandDecorator($hand);
        $hands = new HandTypeCollection();

        foreach ($this->strategies as $strategy) {
            $hands = $strategy->analyze($decorator, $hands);
        }

        $handType = $hands->pop();
        return $handType;
    }
}
