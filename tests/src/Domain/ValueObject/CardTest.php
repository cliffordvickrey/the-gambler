<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Enum\Suit;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use PHPUnit\Framework\TestCase;
use function serialize;
use function sprintf;
use function unserialize;

class CardTest extends TestCase
{
    public function testClone(): void
    {
        $card1 = new Card(new Rank(1), new Suit(Suit::CLUB));
        $card2 = clone $card1;
        $this->assertEquals($card1, $card2);
    }

    public function testSerialize(): void
    {
        $deck = new Deck();

        foreach ($deck as $card) {
            $serialized = serialize($card);
            $unSerialized = unserialize($serialized);
            $this->assertEquals($card, $unSerialized);
        }
    }

    public function testGetId(): void
    {
        $deck = new Deck();

        foreach ($deck as $i => $card) {
            $this->assertEquals(((int)$i) + 1, $card->getId());
        }
    }

    public function testFromId(): void
    {
        $id = 0;

        foreach (Suit::getEnum() as $suit) {
            for ($i = Rank::MIN_RANK; $i <= Rank::MAX_RANK; $i++) {
                $id++;
                $card1 = new Card(new Rank($i), new Suit($suit));
                $card2 = Card::fromId($id);
                $this->assertEquals($card1, $card2);
            }
        }
    }

    public function testGetRank(): void
    {
        for ($i = Rank::MIN_RANK; $i <= Rank::MAX_RANK; $i++) {
            $card = new Card(new Rank($i), new Suit(Suit::CLUB));
            $this->assertEquals($i, $card->getRank()->getValue());
        }
    }

    public function testGetSuit(): void
    {
        foreach (Suit::getEnum() as $suit) {
            $card = new Card(new Rank(1), new Suit($suit));
            $this->assertEquals($suit, (string)$card->getSuit());
        }
    }

    public function testToString(): void
    {
        $deck = new Deck();

        foreach ($deck as $card) {
            $id = $card->getId();
            $this->assertEquals(sprintf('%02d', $id), (string)$card);
        }
    }

    public function testJsonSerialize(): void
    {
        $deck = new Deck();

        foreach ($deck as $card) {
            $id = $card->getId();
            $this->assertEquals($id, $card->jsonSerialize());
        }
    }
}