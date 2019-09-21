<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class BindRoutingArgumentsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $arguments = $route->getArguments();

        foreach ($arguments as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if ('/' === $route->getPattern()) {
            $request = $request->withAttribute(GameResolvingMiddleware::ATTRIBUTE_RESOLVE_GAME_FROM_SESSION, true);
        }

        return $handler->handle($request);
    }

}