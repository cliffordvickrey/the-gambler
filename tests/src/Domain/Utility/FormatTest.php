<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Utility;

use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function testDollarFormat(): void
    {
        $value = Format::dollarFormat(2.5);
        $this->assertEquals('$2.5', $value);
        $value = Format::dollarFormat(2.55);
        $this->assertEquals('$2.55', $value);
        $value = Format::dollarFormat(2.555, 3);
        $this->assertEquals('$2.555', $value);
    }

    public function testPercentFormat(): void
    {
        $value = Format::percentFormat(.25);
        $this->assertEquals('25%', $value);
        $value = Format::percentFormat(.255);
        $this->assertEquals('25.5%', $value);
        $value = Format::percentFormat(.2555, 1);
        $this->assertEquals('25.6%', $value);
    }

    public function testPercentFormatRounded(): void
    {
        $value = Format::percentFormatRounded(.250055);
        $this->assertEquals('25.006%', $value);
        $value = Format::percentFormatRounded(.25555);
        $this->assertEquals('25.56%', $value);
    }


}