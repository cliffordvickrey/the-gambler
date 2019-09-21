<?php

declare (strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\GarbageCollectionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function rand;

class GarbageCollectionMiddleware implements MiddlewareInterface
{
    const DEFAULT_RANDOM_VALUE = 100;

    private $garbageCollection;
    private $randomValue;

    public function __construct(GarbageCollectionInterface $garbageCollection, ?int $randomValue = null)
    {
        $this->garbageCollection = $garbageCollection;
        $this->randomValue = $randomValue ?? self::DEFAULT_RANDOM_VALUE;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ('POST' !== $request->getMethod()) {
            return $handler->handle($request);
        }

        $value = rand(1, $this->randomValue);
        if (1 === $value) {
            $this->garbageCollection->runGarbageCollection();
        }
        return $handler->handle($request);
    }

}