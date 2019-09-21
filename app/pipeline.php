<?php
declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Middleware\BindRoutingArgumentsMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\ErrorHandlingMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\GameResolvingMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\GarbageCollectionMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SessionMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(GameResolvingMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(GarbageCollectionMiddleware::class);
    $app->add(BindRoutingArgumentsMiddleware::class);
    $app->add(ErrorHandlingMiddleware::class);
};
