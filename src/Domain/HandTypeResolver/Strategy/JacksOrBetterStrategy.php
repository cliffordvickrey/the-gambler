<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use function array_filter;
use function count;
use const ARRAY_FILTER_USE_BOTH;

class JacksOrBetterStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        $countByRank = $handDecorator->getCountsByRank();

        $pairsOfJacksOrBetter = array_filter($countByRank, function (int $sum, int $rankId): bool {
            return $sum > 1 && self::isJacksOrBetter($rankId);
        }, ARRAY_FILTER_USE_BOTH);

        if (count($pairsOfJacksOrBetter) > 0) {
            $handType = new HandType(HandType::JACKS_OR_BETTER);
            $carry->add($handType);
        }

        return $carry;
    }

    private static function isJacksOrBetter(int $rankId): bool
    {
        return 1 === $rankId || $rankId > 10;
    }
}
