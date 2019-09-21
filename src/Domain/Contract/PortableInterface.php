<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Contract;

use JsonSerializable;
use Serializable;

interface PortableInterface extends JsonSerializable, Serializable
{
    public function __toString(): string;
}
