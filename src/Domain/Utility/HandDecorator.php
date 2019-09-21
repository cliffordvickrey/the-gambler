<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Utility;

use Cliffordvickrey\TheGambler\Domain\Collection\CardIterator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use InvalidArgumentException;
use IteratorAggregate;
use function array_combine;
use function array_keys;
use function array_map;
use function array_multisort;
use function count;
use function md5;
use function serialize;
use function sprintf;
use function str_repeat;

class HandDecorator implements IteratorAggregate
{
    private $hand;
    /** @var string[] */
    private $cardHashes;
    private $countsByRank;
    /** @var bool */
    private $isFlush;
    private $uniqueHandHash;
    private $uniqueHandTypeHash;

    public function __construct(Hand $hand)
    {
        if (!$hand->isValid()) {
            throw new InvalidArgumentException('Expected a valid hand');
        }

        $this->hand = $this->parseHand($hand);
    }

    private function parseHand(Hand $hand): Hand
    {
        $hand = clone $hand;
        $hand->sortByRankAndSuit();

        $suits = [];
        $firstSort = [];
        $countsByRank = [];

        foreach ($hand as $card) {
            $rankId = $card->getRank()->getValue();
            $suit = (string)$card->getSuit();
            $suits[$suit] = $suits[$suit] ?? [];
            $suits[$suit][] = $rankId;
            $firstSort[] = $rankId;
            $countsByRank[$rankId] = isset($countsByRank[$rankId]) ? ($countsByRank[$rankId] + 1) : 1;
        }

        $this->countsByRank = $countsByRank;
        $this->isFlush = 1 === count($suits);

        $suitIds = array_combine(
            array_keys($suits),
            array_map(function (array $rankIds): int {
                $template = str_repeat('%02d', count($rankIds));
                return (int)sprintf($template, ...$rankIds);
            }, $suits)
        );

        $secondSort = [];
        $cardHashes = [];
        foreach ($hand as $card) {
            $suit = (string)$card->getSuit();
            $secondSort[] = $suitIds[$suit];
            $cardHashes[] = md5(serialize(['rank' => $card->getRank()->getValue(), 'suitId' => $suitIds[$suit]]));
        }

        $canonicallySortedHand = $hand->getIterator()->getArrayCopy();
        array_multisort($firstSort, $secondSort, $canonicallySortedHand, $cardHashes);
        $this->cardHashes = $cardHashes;
        return new Hand(...$canonicallySortedHand);
    }

    public function getHandWithCanonicalSort(): Hand
    {
        return clone $this->hand;
    }

    public function getIterator(): CardIterator
    {
        return $this->hand->getIterator();
    }

    /**
     * @return array
     */
    public function getCountsByRank(): array
    {
        return $this->countsByRank;
    }

    public function getCanonicalHandTypeHash(): string
    {
        if (null !== $this->uniqueHandTypeHash) {
            return $this->uniqueHandTypeHash;
        }

        $toSerialize = array_map(function (Card $card): int {
            return $card->getRank()->getValue();
        }, $this->hand->getIterator()->getArrayCopy());

        $toSerialize[] = $this->isFlush();
        $this->uniqueHandTypeHash = md5(serialize($toSerialize));
        return $this->uniqueHandTypeHash;
    }

    public function isFlush(): bool
    {
        return $this->isFlush;
    }

    public function getCanonicalHandHash(): string
    {
        if (null !== $this->uniqueHandHash) {
            return $this->uniqueHandHash;
        }

        $this->uniqueHandHash = md5(serialize($this->cardHashes));
        return $this->uniqueHandHash;
    }

    /**
     * @return string[]
     */
    public function getCardHashes(): array
    {
        return $this->cardHashes;
    }
}
