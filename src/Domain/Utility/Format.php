<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Utility;

use function array_map;
use function count;
use function explode;
use function is_float;
use function number_format;
use function preg_match;
use function preg_replace;
use function round;
use function rtrim;
use function sprintf;
use function strlen;
use function substr;
use function trim;

class Format
{
    const DEFAULT_PRECISION = 6;

    public static function dollarFormat($amount, int $precision = self::DEFAULT_PRECISION): string
    {
        if (!is_float($amount)) {
            $amount = (float)$amount;
        }

        $dollarAmount = '$' . number_format($amount, $precision);
        $dollarAmount = rtrim($dollarAmount, '0');

        if (substr($dollarAmount, -2) === '.0') {
            $dollarAmount .= '0';
        } elseif (substr($dollarAmount, -1) === '.') {
            $dollarAmount .= '00';
        }

        return $dollarAmount;
    }

    public static function percentFormat($amount, int $precision = self::DEFAULT_PRECISION): string
    {
        if (!is_float($amount)) {
            $amount = (float)$amount;
        }

        $percent = number_format(($amount) * 100, $precision);
        $percent = rtrim($percent, '0');
        $percent = rtrim($percent, '.');
        return $percent . '%';
    }

    public static function numberFormat($amount, int $decimals = self::DEFAULT_PRECISION): string
    {
        return number_format($amount, $decimals);
    }

    public static function percentFormatRounded($amount, int $precision = self::DEFAULT_PRECISION): string
    {
        $percentage = self::percentFormat($amount, $precision);

        $parts = explode('.', $percentage, 2);
        if (2 !== count($parts)) {
            return $percentage;
        }

        list ($whole, $decimal) = $parts;

        $hasZeroPadding = (bool)preg_match('/^0+/', $decimal, $zeroPaddingMatches);
        $zeroPadding = $zeroPaddingMatches[0] ?? '';

        $decimal = preg_replace('/^0+/', '', preg_replace('/%$/', '', $decimal) ?? '');
        $decimalLength = strlen($decimal ?? '');

        if ($hasZeroPadding && $decimalLength > 1) {
            $decimal = (string)round((int)$decimal, -1 * ($decimalLength - 1));
        } elseif (!$hasZeroPadding && $decimalLength > 2) {
            $decimal = (string)round((int)$decimal, -1 * ($decimalLength - 2));
        }
        $decimal = trim($decimal ?? '', '0');

        return sprintf('%s.%s%s%s', $whole, $zeroPadding, $decimal, '%');
    }
}
