<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Response;

use Fig\Http\Message\StatusCodeInterface;
use JsonSerializable;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;
use stdClass;
use function json_encode;

class JsonResponse extends Response
{
    private $payload;

    /**
     * JsonResponse constructor.
     * @param array|float|JsonSerializable|int|stdClass|string $payload
     * @param int $status
     * @param HeadersInterface|null $headers
     */
    public function __construct(
        $payload, int $status = StatusCodeInterface::STATUS_OK, ?HeadersInterface $headers = null
    )
    {
        $this->payload = $payload;
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode($payload));

        if (null === $headers) {
            $headers = new Headers();
        }
        $headers->addHeader('Content-Type', 'application/json');

        parent::__construct($status, $headers, $body);
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
