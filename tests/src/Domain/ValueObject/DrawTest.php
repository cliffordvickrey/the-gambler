<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function serialize;
use function sprintf;
use function unserialize;

class DrawTest extends TestCase
{
    public function testConstruct(): void
    {
        new Draw(false, false, false, false, false);
        $this->expectException(InvalidArgumentException::class);
        new Draw(false, false, false, false);
    }

    public function testGet(): void
    {
        $draw = new Draw(false, true, false, true, false);
        $this->assertEquals(false, $draw->get(0));
        $this->assertEquals(true, $draw->get(1));
        $this->assertEquals(false, $draw->get(2));
        $this->assertEquals(true, $draw->get(3));
        $this->assertEquals(false, $draw->get(4));
        $this->expectException(OutOfBoundsException::class);
        $draw->get(5);
    }

    public function testSerialize(): void
    {
        $draw = new Draw(false, true, false, true, false);
        $serialized = serialize($draw);
        /** @var Draw $draw */
        $draw = unserialize($serialized);
        $this->assertEquals(false, $draw->get(0));
        $this->assertEquals(true, $draw->get(1));
        $this->assertEquals(false, $draw->get(2));
        $this->assertEquals(true, $draw->get(3));
        $this->assertEquals(false, $draw->get(4));
    }

    public function testGetId(): void
    {
        $expectedId = 0;

        for ($i = 0; $i < 2; $i++) {
            for ($ii = 0; $ii < 2; $ii++) {
                for ($iii = 0; $iii < 2; $iii++) {
                    for ($iv = 0; $iv < 2; $iv++) {
                        for ($v = 0; $v < 2; $v++) {
                            $expectedId++;
                            $draw = new Draw((bool)$i, (bool)$ii, (bool)$iii, (bool)$iv, (bool)$v);
                            $this->assertEquals($expectedId, $draw->getId());
                        }
                    }
                }
            }
        }
    }

    public function testFromId(): void
    {
        for ($i = Draw::MIN_ID; $i < Draw::MAX_ID; $i++) {
            $draw = Draw::fromId($i);
            $this->assertEquals($i, $draw->getId());
        }
        $this->expectException(InvalidArgumentException::class);
        Draw::fromId(33);
    }

    public function testJsonSerialize(): void
    {
        for ($i = Draw::MIN_ID; $i < Draw::MAX_ID; $i++) {
            $draw = Draw::fromId($i);
            $iterator = $draw->getIterator();
            $array = iterator_to_array($iterator);
            $this->assertEquals($array, json_decode(json_encode($draw) ?: ''));
        }
    }

    public function testToString(): void
    {
        for ($i = Draw::MIN_ID; $i < Draw::MAX_ID; $i++) {
            $draw = Draw::fromId($i);
            $this->assertEquals(sprintf('%02d', $i), (string)$draw);
        }
    }
}
