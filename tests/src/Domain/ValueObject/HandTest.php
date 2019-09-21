<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use PHPUnit\Framework\TestCase;
use function json_encode;
use function serialize;
use function unserialize;

class HandTest extends TestCase
{
    public function testIsValid(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $card3 = Card::fromId(3);
        $card4 = Card::fromId(4);
        $card5 = Card::fromId(5);

        $hand = new Hand($card1, $card2, $card3, $card4, $card5);
        $this->assertEquals(true, $hand->isValid());

        $hand = new Hand($card1, $card2, $card3, $card4);
        $this->assertEquals(false, $hand->isValid());

        $hand = new Hand($card1, $card2, $card3, $card4, $card4);
        $this->assertEquals(false, $hand->isValid());
    }

    public function testSerialize(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $card3 = Card::fromId(3);
        $card4 = Card::fromId(4);
        $card5 = Card::fromId(5);

        $hand = new Hand($card1, $card2, $card3, $card4, $card5);
        $serialized = serialize($hand);
        $unSerialized = unserialize($serialized);
        $this->assertEquals($hand, $unSerialized);
    }

    public function testToString(): void
    {
        $card1 = Card::fromId(1);
        $card2 = Card::fromId(2);
        $card3 = Card::fromId(3);
        $card4 = Card::fromId(4);
        $card5 = Card::fromId(5);

        $hand = new Hand($card1, $card2, $card3, $card4, $card5);
        $this->assertEquals('0102030405', (string)$hand);
    }

    public function testJsonSerialize(): void
    {
        $cards = [
            Card::fromId(1),
            Card::fromId(2),
            Card::fromId(3),
            Card::fromId(4),
            Card::fromId(5)
        ];

        $hand = new Hand(...$cards);
        $this->assertEquals(json_encode($cards), json_encode($hand));
    }
}
