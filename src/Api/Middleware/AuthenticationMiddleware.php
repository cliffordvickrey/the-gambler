<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Snipe\BanBuilder\CensorWords;

class AuthenticationMiddleware implements MiddlewareInterface
{
    const ATTRIBUTE_PLAYER_NAME = 'playerName';

    private $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws GameException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $playerName = $requestDecorator->getPlayerName();

        // no funny business. This is a family gambling simulator, you sickos!
        $censorWords = new CensorWords();
        $censoredPlayerName = $censorWords->censorString($playerName);
        $player = new Player($censoredPlayerName['clean']);

        $this->sessionManager->authenticate($player);

        $session = $this->sessionManager->getAuthenticatedSession();
        $request = $request->withAttribute(SessionMiddleware::ATTRIBUTE_SESSION, $session);

        return $handler->handle($request);
    }
}
