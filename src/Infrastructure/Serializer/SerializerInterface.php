<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Serializer;

interface SerializerInterface
{
    /**
     * @param mixed $object
     * @return string
     */
    public function serialize($object): string;

    /**
     * @param string $serialized
     * @return mixed
     */
    public function unSerialize(string $serialized);
}
