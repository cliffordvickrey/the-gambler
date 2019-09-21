<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Handler\GameHandler;
use Cliffordvickrey\TheGambler\Api\Handler\HighScoresHandler;
use Cliffordvickrey\TheGambler\Api\Handler\RulesHandler;
use Cliffordvickrey\TheGambler\Api\Middleware\AuthenticationMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\BetMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\CheatMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\DestroyGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\NewGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\PlayMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SaveGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SpliceHandMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\UpdateHighScoresMiddleware;
use Slim\App;

return function (App $app) {
    $app->get('/', GameHandler::class);

    $app->get('/rules', RulesHandler::class);

    $app->get('/high-scores[/{gameId}]', HighScoresHandler::class);

    $app->post('/authenticate/{playerName}', GameHandler::class)
        ->add(AuthenticationMiddleware::class);

    $app->post('/new-game', GameHandler::class)
        ->add(NewGameMiddleware::class);

    $app->post('/bet/{gameId}/{amount:\d+}', GameHandler::class)
        ->add(UpdateHighScoresMiddleware::class)
        ->add(SaveGameMiddleware::class)
        ->add(BetMiddleware::class);

    $app->post('/play/{gameId}/{draw:\d+}', GameHandler::class)
        ->add(UpdateHighScoresMiddleware::class)
        ->add(SaveGameMiddleware::class)
        ->add(PlayMiddleware::class);

    $app->post('/cheat/{gameId}', GameHandler::class)
        ->add(SaveGameMiddleware::class)
        ->add(CheatMiddleware::class);

    $app->post('/splice/{gameId}/{cardOffset:\d+}/{newCardId:\d+}', GameHandler::class)
        ->add(SaveGameMiddleware::class)
        ->add(SpliceHandMiddleware::class);

    $app->post('/destroy/{gameId}', GameHandler::class)
        ->add(DestroyGameMiddleware::class);
};
