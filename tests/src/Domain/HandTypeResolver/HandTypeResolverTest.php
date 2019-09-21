<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\HandTypeResolver;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Enum\Suit;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy\HandAnalyzerStrategyInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HandTypeResolverTest extends TestCase
{
    /** @var HandTypeResolver */
    private $resolver;

    public function setUp(): void
    {
        $this->resolver = new HandTypeResolver();
    }

    public function testConstructor(): void
    {
        $strategy = new class implements HandAnalyzerStrategyInterface
        {
            public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection
            {
                $carry->add(new HandType(HandType::ROYAL_FLUSH));
                return $carry;
            }
        };

        $resolver = new HandTypeResolver([$strategy]);

        $hand = new Hand(
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::DIAMOND)),
            new Card(new Rank(5), new Suit(Suit::DIAMOND))
        );

        $result = $resolver->resolve($hand);
        $this->assertEquals(HandType::ROYAL_FLUSH, (string)$result);
    }

    public function testResolveInvalidHand(): void
    {
        $hand = new Hand(
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::DIAMOND)),
            new Card(new Rank(5), new Suit(Suit::DIAMOND))
        );

        $this->expectException(InvalidArgumentException::class);
        $this->resolver->resolve($hand);
    }

    public function testResolveNothing(): void
    {
        $hand = new Hand(
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::DIAMOND)),
            new Card(new Rank(5), new Suit(Suit::DIAMOND))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::NOTHING, (string)$result);
    }

    public function testResolveJacksOrBetter(): void
    {
        $hand = new Hand(
            new Card(new Rank(11), new Suit(Suit::CLUB)),
            new Card(new Rank(11), new Suit(Suit::DIAMOND)),
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(3), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::JACKS_OR_BETTER, (string)$result);
    }

    public function testResolveTwoPair(): void
    {
        $hand = new Hand(
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(3), new Suit(Suit::CLUB)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::TWO_PAIR, (string)$result);
    }

    public function testResolveThreeOfAKind(): void
    {
        $hand = new Hand(
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(2), new Suit(Suit::HEART)),
            new Card(new Rank(3), new Suit(Suit::DIAMOND)),
            new Card(new Rank(4), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::THREE_OF_A_KIND, (string)$result);
    }

    public function testStraightAceLow(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND)),
            new Card(new Rank(3), new Suit(Suit::HEART)),
            new Card(new Rank(4), new Suit(Suit::DIAMOND)),
            new Card(new Rank(5), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::STRAIGHT, (string)$result);
    }

    public function testStraightAceHigh(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(10), new Suit(Suit::DIAMOND)),
            new Card(new Rank(11), new Suit(Suit::HEART)),
            new Card(new Rank(12), new Suit(Suit::DIAMOND)),
            new Card(new Rank(13), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::STRAIGHT, (string)$result);
    }

    public function testFlush(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(9), new Suit(Suit::CLUB)),
            new Card(new Rank(11), new Suit(Suit::CLUB)),
            new Card(new Rank(12), new Suit(Suit::CLUB)),
            new Card(new Rank(13), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::FLUSH, (string)$result);
    }

    public function testFullHouse(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(1), new Suit(Suit::DIAMOND)),
            new Card(new Rank(1), new Suit(Suit::HEART)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::FULL_HOUSE, (string)$result);
    }

    public function testFourOfAKind(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(1), new Suit(Suit::DIAMOND)),
            new Card(new Rank(1), new Suit(Suit::HEART)),
            new Card(new Rank(1), new Suit(Suit::SPADE)),
            new Card(new Rank(2), new Suit(Suit::DIAMOND))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::FOUR_OF_A_KIND, (string)$result);
    }

    public function testStraightFlush(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(2), new Suit(Suit::CLUB)),
            new Card(new Rank(3), new Suit(Suit::CLUB)),
            new Card(new Rank(4), new Suit(Suit::CLUB)),
            new Card(new Rank(5), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::STRAIGHT_FLUSH, (string)$result);
    }

    public function testRoyalFlush(): void
    {
        $hand = new Hand(
            new Card(new Rank(1), new Suit(Suit::CLUB)),
            new Card(new Rank(10), new Suit(Suit::CLUB)),
            new Card(new Rank(11), new Suit(Suit::CLUB)),
            new Card(new Rank(12), new Suit(Suit::CLUB)),
            new Card(new Rank(13), new Suit(Suit::CLUB))
        );

        $result = $this->resolver->resolve($hand);
        $this->assertEquals(HandType::ROYAL_FLUSH, (string)$result);
    }
}