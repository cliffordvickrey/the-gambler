<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NewGameMiddleware implements MiddlewareInterface
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
        if (null !== $session->getGameId()) {
            throw new LogicException('Cannot start a new game; game already started');
        }

        $game = $this->gameRepository->getNew();
        $this->gameRepository->save($game);
        $session->setGameId($game->getId());
        $request = $request->withAttribute(GameResolvingMiddleware::ATTRIBUTE_GAME, $game);

        return $handler->handle($request);
    }
}
