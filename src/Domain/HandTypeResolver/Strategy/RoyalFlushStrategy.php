<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use function end;
use function key;
use function reset;

class RoyalFlushStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        if (!$carry->has(new HandType(HandType::STRAIGHT_FLUSH))) {
            return $carry;
        }

        $countByRank = $handDecorator->getCountsByRank();
        if (Rank::MIN_RANK !== key($countByRank)) {
            return $carry;
        }

        end($countByRank);

        if (Rank::MAX_RANK === key($countByRank)) {
            $handType = new HandType(HandType::ROYAL_FLUSH);
            $carry->add($handType);
        }

        reset($countByRank);

        return $carry;
    }

}