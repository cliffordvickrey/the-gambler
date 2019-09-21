<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use function array_values;
use function count;
use function sort;

class FullHouseStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        $countByRank = $handDecorator->getCountsByRank();

        if (count($countByRank) > 2) {
            return $carry;
        }

        $groups = array_values($countByRank);
        sort($groups);

        if (2 === $groups[0] && 3 === $groups[1]) {
            $handType = new HandType(HandType::FULL_HOUSE);
            $carry->add($handType);
        }

        return $carry;
    }
}
