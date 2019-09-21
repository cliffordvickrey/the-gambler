<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache\TtlCacheManifest;

use JsonSerializable;
use function array_filter;
use function array_keys;

final class TtlCacheManifest implements TtlCacheManifestInterface, JsonSerializable
{
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function set(string $key, ?int $ttl): void
    {

        if (null === $ttl) {
            unset($this->values[$key]);
            return;
        }

        $this->values[$key] = $ttl;
    }

    public function get(string $key): ?int
    {
        $value = $this->values[$key] ?? null;
        if (!is_int($value)) {
            return null;
        }
        return $value;
    }

    public function jsonSerialize(): array
    {
        return $this->values;
    }

    /**
     * @inheritDoc
     */
    public function getKeys(): array
    {
        $keys = array_keys($this->values);
        return array_filter($keys, 'is_string');
    }
}
