<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Api\ResponseEmitter\ResponseEmitter;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function () {
    // Build PHP-DI Container instance
    $container = require __DIR__ . '/../app/container.php';

    // Instantiate the app
    AppFactory::setContainer($container);
    $app = AppFactory::create();

    // Register middleware
    $middleware = require __DIR__ . '/../app/pipeline.php';
    $middleware($app);

    // Register routes
    $routes = require __DIR__ . '/../app/routes.php';
    $routes($app);

    // Create Request object from globals
    $serverRequestCreator = ServerRequestCreatorFactory::create();
    $request = $serverRequestCreator->createServerRequestFromGlobals();

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Run App & Emit Response
    try {
        $response = $app->handle($request);
    } catch (Throwable $e) {
        if ($e instanceof HttpNotFoundException | $e instanceof HttpMethodNotAllowedException) {
            $response = new JsonResponse(['errorMessage' => '404 Not Found'], StatusCodeInterface::STATUS_NOT_FOUND);
        } else {
            throw $e;
        }
    }

    $responseEmitter = new ResponseEmitter();
    $responseEmitter->emit($response);
});
