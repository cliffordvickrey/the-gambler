<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class DestroyGameMiddleware implements MiddlewareInterface
{
    private $gameRepository;
    private $sessionManager;

    public function __construct(GameRepositoryInterface $gameRepository, SessionManagerInterface $sessionManager)
    {
        $this->gameRepository = $gameRepository;
        $this->sessionManager = $sessionManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $session = $requestDecorator->getSession();
        $game = $requestDecorator->getGameNullable();

        if (null === $game) {
            return $handler->handle($request);
        }

        try {
            $this->gameRepository->delete($game);
        } catch (Throwable $e) {

        }

        $session->setGameId(null);
        $request = $request->withAttribute(GameResolvingMiddleware::ATTRIBUTE_GAME, null);

        return $handler->handle($request);
    }
}
