<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\Generator;

use Generator;
use InvalidArgumentException;
use Traversable;
use function count;
use function is_array;
use function iterator_to_array;

class CombinationGenerator
{
    /**
     * @param array|Traversable $traversable
     * @param int $numberChosen
     * @return Generator
     */
    public function __invoke($traversable, int $numberChosen): Generator
    {
        $toCombine = $traversable;
        if (!is_array($traversable)) {
            $toCombine = iterator_to_array($toCombine);
        }

        $count = count($toCombine);

        if ($count < $numberChosen) {
            throw new InvalidArgumentException('Number chosen cannot exceed the size of array to combine');
        }

        if (0 === $numberChosen) {
            yield [];
            return;
        }

        yield from self::recursiveGenerator($toCombine, $numberChosen, $count);
    }

    private static function recursiveGenerator(
        array $toCombine,
        int $numberChosen,
        int $count,
        int $i = 0,
        int $ii = 0,
        array $result = []
    ): Generator
    {
        if ($i === $numberChosen) {
            yield $result;
            return;
        }

        if ($ii >= $count) {
            return;
        }

        $result[$i] = $toCombine[$ii];

        yield from self::recursiveGenerator($toCombine, $numberChosen, $count, $i + 1, $ii + 1, $result);
        yield from self::recursiveGenerator($toCombine, $numberChosen, $count, $i, $ii + 1, $result);
    }
}
