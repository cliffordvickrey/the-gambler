<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Cache;

use function array_key_exists;

class InMemoryCache extends AbstractCache
{
    private $memo = [];

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->memo) ? $this->memo[$key] : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $this->memo[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->memo[$key]);
    }

    public function clear()
    {
        $this->memo = [];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->memo);
    }
}
