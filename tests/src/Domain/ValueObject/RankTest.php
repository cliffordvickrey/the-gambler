<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\ValueObject\Rank;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RankTest extends TestCase
{
    public function testConstruct(): void
    {
        for ($i = Rank::MIN_RANK; $i < Rank::MAX_RANK; $i++) {
            $rank = new Rank($i);
            $this->assertEquals($i, $rank->getValue());
        }

        $this->expectException(InvalidArgumentException::class);
        new Rank(14);
    }
}
