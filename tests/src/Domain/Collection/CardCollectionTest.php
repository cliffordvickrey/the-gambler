<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use Cliffordvickrey\TheGambler\Domain\Collection\CardIterator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use function count;
use function iterator_to_array;

class CardCollectionTest extends TestCase
{
    public function testClone(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection1 = new CardCollection($card1, $card2);
        $collection2 = clone $collection1;
        $array1 = iterator_to_array($collection1->getIterator());
        $array2 = iterator_to_array($collection2->getIterator());
        $this->assertEquals($array1, $array2);
    }

    public function testGetIterator(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $iterator = $collection->getIterator();
        $this->assertInstanceOf(CardIterator::class, $iterator);
    }

    public function testSort(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card2, $card1);

        $array = iterator_to_array($collection->getIterator());
        $this->assertEquals('02', (string)$array[0]);
        $this->assertEquals('01', (string)$array[1]);
        $collection->sort();;

        $array = iterator_to_array($collection->getIterator());
        $this->assertEquals('01', (string)$array[0]);
        $this->assertEquals('02', (string)$array[1]);
    }

    public function testSortByRankAndSuit(): void
    {
        $card1 = Card::fromId(2);
        $card2 = Card::fromId(14);
        $collection = new CardCollection($card1, $card2);

        $array = iterator_to_array($collection->getIterator());
        $this->assertEquals('02', (string)$array[0]);
        $this->assertEquals('14', (string)$array[1]);
        $collection->sortByRankAndSuit();

        $array = iterator_to_array($collection->getIterator());
        $this->assertEquals('14', (string)$array[0]);
        $this->assertEquals('02', (string)$array[1]);
    }

    public function testToIndexedArray(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $indexedArray = $collection->toIndexedArray();
        $this->assertEquals('01', (string)$indexedArray[1]);
        $this->assertEquals('02', (string)$indexedArray[2]);
    }

    public function testCount(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $this->assertCount(2, $collection);
    }

    public function testSliceCard(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $sliced = $collection->slice(clone $card1);
        $this->assertEquals('01', (string)$sliced);
        $this->assertCount(1, $collection);
        $this->expectException(OutOfBoundsException::class);
        $collection->slice(clone $card1);
    }

    public function testSliceByOffset(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $sliced = $collection->slice(clone $card1);
        $this->assertEquals('01', (string)$sliced);
        $this->assertCount(1, $collection);
        $this->expectException(OutOfBoundsException::class);
        $collection->sliceByOffset(10);
    }

    public function sliceRandom(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $collection = new CardCollection($card1, $card2);
        $collection->sliceRandom();
        $collection->sliceRandom();
        $this->assertCount(0, $collection);
    }

    public function testSplice(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $card3 = Card::fromId(3);
        $collection = new CardCollection($card1, $card3);
        $collection->splice(1, $card2);

        $expected = 0;
        foreach ($collection as $card) {
            $expected++;
            $this->assertEquals($expected, $card->getId());
        }

        $this->expectException(OutOfBoundsException::class);
        $collection->splice(4, Card::fromId(4));
    }
}