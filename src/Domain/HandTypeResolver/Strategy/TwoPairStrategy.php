<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use function array_filter;
use function count;

class TwoPairStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        $countByRank = $handDecorator->getCountsByRank();
        $pairs = array_filter($countByRank, function (int $count): bool {
            return $count > 1;
        });

        if (count($pairs) > 1) {
            $handType = new HandType(HandType::TWO_PAIR);
            $carry->add($handType);
        }

        return $carry;
    }
}