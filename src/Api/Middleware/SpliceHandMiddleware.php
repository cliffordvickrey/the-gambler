<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SpliceHandMiddleware implements MiddlewareInterface
{
    const ATTRIBUTE_CARD_OFFSET = 'cardOffset';
    const ATTRIBUTE_NEW_CARD_ID = 'newCardId';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $game = $requestDecorator->getGame();
        $offset = $requestDecorator->getCardOffset();
        $card = $requestDecorator->getReplacementCard();
        if ($game->getMeta()->getCheated()) {
            $game->spliceHand($offset, $card);
        }
        return $handler->handle($request);
    }
}