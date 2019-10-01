<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Response;

use Fig\Http\Message\StatusCodeInterface;
use JsonSerializable;
use RuntimeException;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;
use stdClass;
use function is_bool;
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

        $jsonEncoded = json_encode($payload);

        if (is_bool($jsonEncoded)) {
            throw new RuntimeException('Could not JSON-serialize payload');
        }

        $body = $streamFactory->createStream($jsonEncoded);

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
