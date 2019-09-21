<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    public function testConstruct(): void
    {
        $deck = new Deck();
        $this->assertCount(52, $deck);

        $iterator = $deck->getIterator();

        for ($i = 1; $i < 5; $i++) {
            for ($ii = 1; $ii < 14; $ii++) {
                $card = $iterator->current();
                $this->assertEquals($i, $card->getSuit()->getId());
                $this->assertEquals($ii, $card->getRank()->getValue());
                $iterator->next();
            }
        }
    }
}