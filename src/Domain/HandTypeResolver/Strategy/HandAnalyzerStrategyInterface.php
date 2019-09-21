<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\HandTypeResolver\Strategy;

use Cliffordvickrey\TheGambler\Domain\Collection\HandTypeCollection;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;

interface HandAnalyzerStrategyInterface
{
    /**
     * @param HandDecorator $handDecorator
     * @param HandTypeCollection $carry
     * @return HandTypeCollection
     */
    public function analyze(HandDecorator $handDecorator, HandTypeCollection $carry): HandTypeCollection;
}
