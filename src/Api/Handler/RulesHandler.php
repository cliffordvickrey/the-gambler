<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RulesHandler implements RequestHandlerInterface
{
    private $rules;

    public function __construct(RulesInterface $rules)
    {
        $this->rules = $rules;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->rules);
    }
}
