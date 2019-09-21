<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Domain\Exception\UserFriendlyException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ErrorHandlingMiddleware implements MiddlewareInterface
{
    private $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            if ($e instanceof UserFriendlyException || $this->debug) {
                $errorMessage = $e->getMessage();
            } else {
                $errorMessage = 'There was an internal server error';
            }

//            echo (string)$e; exit();
            $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
            $response = new JsonResponse(['errorMessage' => $errorMessage], $statusCode);
        }

        return $response;
    }
}
