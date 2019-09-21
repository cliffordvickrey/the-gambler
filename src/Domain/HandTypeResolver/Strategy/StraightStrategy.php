<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use function array_keys;
use function array_shift;
use function count;
use function current;
use function end;
use function reset;

class StraightStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        $countByRank = $handDecorator->getCountsByRank();
        if (5 !== count($countByRank)) {
            return $carry;
        }

        $rankIds = array_keys($countByRank);
        if (self::isAceHigh($rankIds)) {
            array_shift($rankIds);
            $rankIds[] = Rank::MAX_RANK + 1;
        }

        if (self::areRanksInOrder($rankIds)) {
            $handType = new HandType(HandType::STRAIGHT);
            $carry->add($handType);
        }

        return $carry;
    }

    private static function isAceHigh(array $rankIds): bool
    {
        $firstRankId = current($rankIds);
        if (Rank::MIN_RANK !== $firstRankId) {
            return false;
        }

        end($rankIds);
        $lastRankId = current($rankIds);
        reset($rankIds);
        return Rank::MAX_RANK === $lastRankId;
    }

    private static function areRanksInOrder(array $rankIds): bool
    {
        $expectedId = current($rankIds);
        for ($i = 1; $i < count($rankIds); $i++) {
            $expectedId++;
            if ($expectedId !== $rankIds[$i]) {
                return false;
            }
        }

        return true;
    }
}
