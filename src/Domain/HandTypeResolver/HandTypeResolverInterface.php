<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;

interface HandTypeResolverInterface
{
    public function resolve(Hand $hand): HandType;
}