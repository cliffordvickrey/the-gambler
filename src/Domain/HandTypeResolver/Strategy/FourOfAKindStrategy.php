<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use function array_filter;
use function count;

class FourOfAKindStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        if (!$carry->has(new HandType(HandType::THREE_OF_A_KIND))) {
            return $carry;
        }

        $countByRank = $handDecorator->getCountsByRank();
        $quadruples = array_filter($countByRank, function (int $count): bool {
            return $count > 3;
        });

        if (count($quadruples) > 0) {
            $handType = new HandType(HandType::FOUR_OF_A_KIND);
            $carry->add($handType);
        }

        return $carry;
    }
}
