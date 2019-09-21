<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Enum;

use Cliffordvickrey\TheGambler\Domain\Enum\Suit;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SuitTest extends TestCase
{
    public function testConstruct(): void
    {
        $enums = Suit::getEnum();
        foreach ($enums as $enum) {
            $suit = new Suit($enum);
            $this->assertEquals($enum, (string)$suit);
        }

        $this->expectException(InvalidArgumentException::class);
        new Suit('hammer');
    }

    public function testGetId(): void
    {
        $expected = 0;
        $enums = Suit::getEnum();
        foreach ($enums as $enum) {
            $expected++;
            $suit = new Suit($enum);
            $this->assertEquals($expected, $suit->getId());
        }
    }
}