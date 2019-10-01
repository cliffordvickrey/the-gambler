<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Probability\Generator;

use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function array_map;
use function array_unique;
use function implode;
use function iterator_to_array;

class CombinationGeneratorTest extends TestCase
{
    public function testInvoke(): void
    {
        $invokable = new CombinationGenerator();
        $generator = $invokable(new Deck(), 3);
        $results = [];

        foreach ($generator as $result) {
            $results[] = implode('', array_map('strval', $result));
        }

        $results = array_unique($results);
        $this->assertCount(22100, $results);
    }

    public function testInvokeNumberChosenTooLarge(): void
    {
        $invokable = new CombinationGenerator();
        $generator = $invokable(new Deck(), 53);
        $this->expectException(InvalidArgumentException::class);
        iterator_to_array($generator);
    }
}
