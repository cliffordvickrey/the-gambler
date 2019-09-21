<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Contract;

use Countable;
use IteratorAggregate;

interface CollectionInterface extends IteratorAggregate, Countable
{

}