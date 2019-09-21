<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BetMiddleware implements MiddlewareInterface
{
    const ATTRIBUTE_AMOUNT = 'amount';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $betAmount = $requestDecorator->getBetAmount();

        $game = $requestDecorator->getGame();
        $game->bet($betAmount);

        return $handler->handle($request);
    }
}
