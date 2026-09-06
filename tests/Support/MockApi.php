<?php

namespace DealNews\InngestApi\Tests\Support;

use DealNews\InngestApi\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Builds an HttpClient backed by a Guzzle MockHandler, so tests never hit
 * the real Inngest API.
 */
class MockApi {

    /**
     * @param Response[] $responses
     */
    public static function client(array $responses, ?string $environment = null): HttpClient {
        $stack  = HandlerStack::create(new MockHandler($responses));
        $guzzle = new Client(['handler' => $stack]);

        return new HttpClient('test-api-key', 'https://api.inngest.com/v2', $environment, $guzzle);
    }
}
