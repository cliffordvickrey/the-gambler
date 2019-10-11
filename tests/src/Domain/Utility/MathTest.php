<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Utility;

use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    public function testMean(): void
    {
        $values = [1, 2, 3];
        $mean = Math::mean($values);
        $this->assertEquals(2.0, $mean);
    }

    public function testStdDev(): void
    {
        $values = [1, 2, 3, 1];
        $stdDev = Math::stDev($values);
        $this->assertEquals(0.82915619758885, $stdDev);
    }

    public function testLogTransform(): void
    {
        $values = [1, 2, 3];
        $logTransformed = Math::logTransform($values);
        $this->assertEquals(0.0, $logTransformed[0]);
        $this->assertEquals(0.69314718055995, $logTransformed[1]);
        $this->assertEquals(1.0986122886681, $logTransformed[2]);
    }

    public function testGroupMean(): void
    {
        $values = [1 => 2, 2 => 5, 3 => 10];
        $groupMean = Math::groupMean($values);
        $this->assertEquals(7.0, $groupMean);
    }

    public function testGroupStDev(): void
    {
        $values = [1 => 2, 2 => 5, 3 => 10];
        $groupStDev = Math::groupStDev($values);
        $this->assertEquals(3.1622776601683795, $groupStDev);
    }

    public function testLogTransformGrouped(): void
    {
        $values = [1 => 2, 2 => 5, 4 => 10];
        $logTransformed = Math::logTransform($values, true);
        $this->assertEquals(0.0, $logTransformed[1]);
        $this->assertEquals(1.3862943611198906, $logTransformed[2]);
        $this->assertEquals(2.1972245773362196, $logTransformed[4]);
        $this->assertEquals(1.6516410045120942, Math::groupMean($logTransformed));
    }

    public function testStandardize(): void
    {
        $z = Math::standardize(125, 4.89043, 7.934);
        $this->assertEquals(15.138589614318, $z);
    }

    public function testPercentile(): void
    {
        $p = Math::percentile(1.25);
        $this->assertEquals(0.896165, $p);
    }
}
