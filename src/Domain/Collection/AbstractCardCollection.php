<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\Contract\CollectionInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use OutOfBoundsException;
use function array_combine;
use function array_map;
use function array_values;
use function asort;
use function count;
use function floor;
use function ksort;
use function mt_rand;
use function sprintf;

abstract class AbstractCardCollection implements CollectionInterface
{
    /** @var Card[] */
    protected $cards = [];

    public function __clone()
    {
        foreach ($this->cards as $index => $card) {
            $this->cards[$index] = clone $card;
        }
    }

    public function getIterator(): CardIterator
    {
        return new CardIterator($this->cards);
    }

    public function sort(): void
    {
        $indexed = $this->toIndexedArray();
        asort($indexed);
        $this->cards = array_values($indexed);
    }

    /**
     * @return Card[]
     */
    public function toIndexedArray(): array
    {
        $keys = array_map(function (Card $card): int {
            return $card->getId();
        }, $this->cards);

        $array = array_combine($keys, $this->cards) ?: [];
        return $array;
    }

    public function sortByRankAndSuit(): void
    {
        $indexed = $this->toIndexedArray();
        $rankIndexed = [];

        foreach ($indexed as $id => $card) {
            $rankId = ($id % Rank::MAX_RANK) ?: Rank::MAX_RANK;
            $suitId = (int)floor(($id - 1) / Rank::MAX_RANK) + 1;
            $newIndex = (($rankId - 1) * 4) + $suitId;
            $rankIndexed[$newIndex] = $card;
        }

        ksort($rankIndexed);
        $this->cards = array_values($rankIndexed);
    }

    public function count(): int
    {
        return count($this->cards);
    }

    public function slice(Card $card): Card
    {
        $cardId = $card->getId();
        $indexed = $this->toIndexedArray();

        if (!isset($indexed[$cardId])) {
            throw new OutOfBoundsException(sprintf('Card "%s" not found in %s', (string)$card, static::class));
        }

        $cardToReturn = $indexed[$cardId];
        unset($indexed[$cardId]);
        $this->cards = array_values($indexed);
        return $cardToReturn;
    }

    public function sliceRandom(): Card
    {
        if (0 === count($this->cards)) {
            throw new OutOfBoundsException(sprintf('Cannot slice a card at random; %s is empty', self::class));
        }

        $offset = mt_rand(0, count($this->cards) - 1);
        return $this->sliceByOffset($offset);
    }

    public function sliceByOffset(int $offset): Card
    {
        if (!isset($this->cards[$offset])) {
            throw new OutOfBoundsException(sprintf('Card at offset %d not found in %s', $offset, static::class));
        }

        $cardToReturn = $this->cards[$offset];
        unset($this->cards[$offset]);
        $this->cards = array_values($this->cards);
        return $cardToReturn;
    }

    public function has(Card $card): bool
    {
        $indexed = $this->toIndexedArray();
        $cardId = $card->getId();
        return isset($indexed[$cardId]);
    }

    public function getByOffset(int $offset): Card
    {
        if (!isset($this->cards[$offset])) {
            throw new OutOfBoundsException(sprintf('Card at offset %d not found in %s', $offset, static::class));
        }

        return $this->cards[$offset];
    }

    public function splice(int $offset, Card $replacement): void
    {
        if (!isset($this->cards[$offset])) {
            throw new OutOfBoundsException(sprintf('Card at offset %d not found in %s', $offset, static::class));
        }

        $this->cards[$offset] = $replacement;
    }

    public function push(Card $card): void
    {
        $this->cards[] = $card;
    }
}
