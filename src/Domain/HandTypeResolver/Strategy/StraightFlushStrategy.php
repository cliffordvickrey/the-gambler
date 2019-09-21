<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;

class StraightFlushStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        if ($carry->has(new HandType(HandType::STRAIGHT)) && $carry->has(new HandType(HandType::FLUSH))) {
            $handType = new HandType(HandType::STRAIGHT_FLUSH);
            $carry->add($handType);
        }

        return $carry;
    }

}