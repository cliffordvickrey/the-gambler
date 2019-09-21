<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Enum;

use function array_search;

final class Suit extends AbstractEnum
{
    const CLUB = 'club';
    const DIAMOND = 'diamond';
    const HEART = 'heart';
    const SPADE = 'spade';

    protected static $enum = [
        self::CLUB => self::CLUB,
        self::DIAMOND => self::DIAMOND,
        self::HEART => self::HEART,
        self::SPADE => self::SPADE
    ];

    protected static $ids = [
        self::CLUB => 1,
        self::DIAMOND => 2,
        self::HEART => 3,
        self::SPADE => 4
    ];

    public function getId(): int
    {
        return self::$ids[$this->value];
    }

    public static function fromId(int $id): self
    {
        $value = array_search($id, self::$ids) ?: '';
        return new static($value);
    }
}