<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Util;

use Cliffordvickrey\TheGambler\Domain\Collection\CardIterator;
use Cliffordvickrey\TheGambler\Domain\Enum\Suit;
use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HandDecoratorTest extends TestCase
{
    public function testConstructInvalidHand(): void
    {
        $hand = new Hand(Card::fromId(1), Card::fromId(2), Card::fromId(3));
        $this->expectException(InvalidArgumentException::class);
        new HandDecorator($hand);
    }

    public function testGetIterator(): void
    {
        $hand = new Hand(Card::fromId(1), Card::fromId(2), Card::fromId(3), Card::fromId(4), Card::fromId(5));
        $handDecorator = new HandDecorator($hand);
        $iterator = $handDecorator->getIterator();
        $this->assertInstanceOf(CardIterator::class, $iterator);
    }

    public function testGetCountsByRank(): void
    {
        $hand = new Hand(Card::fromId(1), Card::fromId(14), Card::fromId(2), Card::fromId(15), Card::fromId(28));
        $handDecorator = new HandDecorator($hand);
        $countsByRank = $handDecorator->getCountsByRank();
        $this->assertEquals(2, $countsByRank[1]);
        $this->assertEquals(3, $countsByRank[2]);
    }

    public function testIsFlush(): void
    {
        $hand = new Hand(Card::fromId(1), Card::fromId(2), Card::fromId(3), Card::fromId(4), Card::fromId(5));
        $handDecorator = new HandDecorator($hand);
        $this->assertEquals(true, $handDecorator->isFlush());
        $hand = new Hand(Card::fromId(1), Card::fromId(2), Card::fromId(3), Card::fromId(4), Card::fromId(14));
        $handDecorator = new HandDecorator($hand);
        $this->assertEquals(false, $handDecorator->isFlush());
    }

    public function testGetCanonicalHandTypeHash(): void
    {
        $hand1 = new Hand(
            new Card(new Rank(1), new Suit(Suit::DIAMOND)),
            new Card(new Rank(2), new Suit(Suit::HEART)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::HEART)),
            new Card(new Rank(5), new Suit(Suit::CLUB))
        );

        $hand2 = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::SPADE)),
            new Card(new Rank(3), new Suit(Suit::CLUB)),
            new Card(new Rank(4), new Suit(Suit::SPADE)),
            new Card(new Rank(5), new Suit(Suit::HEART))
        );

        $hand3 = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(3), new Suit(Suit::CLUB)),
            new Card(new Rank(4), new Suit(Suit::CLUB)),
            new Card(new Rank(5), new Suit(Suit::CLUB))
        );

        $hash1 = (new HandDecorator($hand1))->getCanonicalHandTypeHash();
        $hash2 = (new HandDecorator($hand2))->getCanonicalHandTypeHash();
        $hash3 = (new HandDecorator($hand3))->getCanonicalHandTypeHash();

        $this->assertEquals($hash1, $hash2);
        $this->assertNotEquals($hash1, $hash3);
    }

    public function testGetCanonicalHandHash(): void
    {
        $hand1 = new Hand(
            new Card(new Rank(1), new Suit(Suit::DIAMOND)),
            new Card(new Rank(1), new Suit(Suit::HEART)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(2), new Suit(Suit::HEART)),
            new Card(new Rank(3), new Suit(Suit::CLUB))
        );

        $hand2 = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(1), new Suit(Suit::SPADE)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::SPADE)),
            new Card(new Rank(3), new Suit(Suit::HEART))
        );

        $hand3 = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(1), new Suit(Suit::SPADE)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::SPADE)),
            new Card(new Rank(3), new Suit(Suit::SPADE))
        );


        $hash1 = (new HandDecorator($hand1))->getCanonicalHandHash();
        $hash2 = (new HandDecorator($hand2))->getCanonicalHandHash();
        $hash3 = (new HandDecorator($hand3))->getCanonicalHandHash();

        $this->assertEquals($hash1, $hash2);
        $this->assertNotEquals($hash1, $hash3);
    }
}