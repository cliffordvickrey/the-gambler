<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    const ATTRIBUTE_SESSION = 'session';

    private $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = null;

        $this->sessionManager->start();
        if ($this->sessionManager->isAuthenticated()) {
            $session = $this->sessionManager->getAuthenticatedSession();
        }

        $request = $request->withAttribute(self::ATTRIBUTE_SESSION, $session);
        return $handler->handle($request);
    }
}
