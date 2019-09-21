<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Utility;

use function is_float;
use function number_format;
use function rtrim;
use function substr;

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
}
