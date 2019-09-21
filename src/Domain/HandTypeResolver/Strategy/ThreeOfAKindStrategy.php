<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use function array_filter;
use function count;

class ThreeOfAKindStrategy implements HandAnalyzerStrategyInterface
{
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
    {
        $countByRank = $handDecorator->getCountsByRank();
        $triples = array_filter($countByRank, function (int $count): bool {
            return $count > 2;
        });

        if (count($triples) > 0) {
            $handType = new HandType(HandType::THREE_OF_A_KIND);
            $carry->add($handType);
        }

        return $carry;
    }

}