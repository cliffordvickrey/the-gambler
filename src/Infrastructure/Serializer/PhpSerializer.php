<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Serializer;

use function serialize;
use function unserialize;

class PhpSerializer implements SerializerInterface
{
    private $options;

    public function __construct(?array $classWhiteList = null)
    {
        if (null !== $classWhiteList) {
            $this->options = ['allowed_classes' => $classWhiteList];
        }
    }

    public function serialize($object): string
    {
        return serialize($object);
    }

    public function unSerialize(string $serialized)
    {
        return unserialize($serialized, $this->options ?? []);
    }
}
