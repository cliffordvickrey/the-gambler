<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Enum;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function json_decode;
use function json_encode;
use function serialize;
use function unserialize;

class HandTypeTest extends TestCase
{
    public function testConstruct(): void
    {
        $enums = HandType::getEnum();
        foreach ($enums as $enum) {
            $handType = new HandType($enum);
            $this->assertEquals($enum, (string)$handType);
        }

        $this->expectException(InvalidArgumentException::class);
        new HandType('threesCompany');
    }

    public function testSerialize(): void
    {
        $handType = new HandType(HandType::FLUSH);
        $serialized = serialize($handType);
        $unSerialized = unserialize($serialized);
        $this->assertInstanceOf(HandType::class, $unSerialized);
        /** @var HandType $unSerialized */
        $this->assertEquals((string)HandType::FLUSH, (string)$unSerialized);
    }

    public function testJsonSerialize(): void
    {
        $handType = new HandType(HandType::FLUSH);
        $this->assertEquals(HandType::FLUSH, json_decode(json_encode($handType)));
    }
}