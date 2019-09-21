<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Api\ResponseEmitter\ResponseEmitter;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Exception\HttpSpecializedException;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function () {
    // build PHP-DI Container instance
    $container = require __DIR__ . '/../app/container.php';

    // get the config
    $config = $container->get('config');

    // instantiate the app
    AppFactory::setContainer($container);
    $app = AppFactory::create();

    // route caching
    if ($config['production']) {
        $routeCollector = $app->getRouteCollector();
        $routeCollector->setCacheFile(__DIR__ . '/../data/route-cache.php');
    }

    // register middleware
    $middleware = require __DIR__ . '/../app/pipeline.php';
    $middleware($app);

    // register routes
    $routes = require __DIR__ . '/../app/routes.php';
    $routes($app);

    // create Request object from globals
    $serverRequestCreator = ServerRequestCreatorFactory::create();
    $request = $serverRequestCreator->createServerRequestFromGlobals();

    // add routing middleware
    $app->addRoutingMiddleware();

    // run app & emit Response
    try {
        $response = $app->handle($request);
    } catch (Throwable $e) {
        // handle all exceptions that ErrorHandlingMiddleware misses
        if ($e instanceof HttpSpecializedException) {
            $response = new JsonResponse(['errorMessage' => $e->getMessage()], $e->getCode());
        } elseif (!$config['debug']) {
            $response = new JsonResponse(
                ['errorMessage' => 'There was an internal server error'],
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            );
        } else {
            throw $e;
        }
    }

    $responseEmitter = new ResponseEmitter();
    $responseEmitter->emit($response);
});
