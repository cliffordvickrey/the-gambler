<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;
use function get_class;
use function sprintf;

class RulesHandler implements RequestHandlerInterface
{
    private $rules;

    public function __construct(RulesInterface $rules)
    {
        $this->rules = $rules;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!($this->rules instanceof JsonSerializable)) {
            throw new UnexpectedValueException(
                sprintf('%s is does not implement %s', get_class($this->rules), JsonSerializable::class)
            );
        }

        return new JsonResponse($this->rules);
    }
}
