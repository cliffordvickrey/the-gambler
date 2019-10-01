<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameNotFoundException;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function is_string;

class GameResolvingMiddleware implements MiddlewareInterface
{
    const ATTRIBUTE_GAME = 'game';
    const ATTRIBUTE_GAME_ID = 'gameId';
    const ATTRIBUTE_RESOLVE_GAME_FROM_SESSION = 'resolveGameFromSession';

    private $gameRepository;

    public function __construct(GameRepositoryInterface $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $session = $requestDecorator->getSessionNullable();
        if (null === $session) {
            // no session? No game!
            return $handler->handle($request);
        }

        $gameId = $request->getAttribute(self::ATTRIBUTE_GAME_ID);

        if (is_string($gameId)) {
            $gameId = new GameId($gameId);
        } elseif (!is_string($gameId) && $requestDecorator->getResolveGameFromSession()) {
            $gameId = $session->getGameId();
        } else {
            $gameId = null;
        }

        if (null === $gameId) {
            return $handler->handle($request);
        }

        try {
            $game = $this->gameRepository->get($gameId);
        } catch (GameNotFoundException $e) {
            $game = null;
        }

        $request = $request->withAttribute(self::ATTRIBUTE_GAME, $game);
        return $handler->handle($request);
    }
}
