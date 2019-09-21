<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GameHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $session = $requestDecorator->getSession(false);
        $game = $requestDecorator->getGame(false);
        $payload = ['session' => $session, 'game' => $game];
        $response = new JsonResponse($payload);
        return $response;
    }
}