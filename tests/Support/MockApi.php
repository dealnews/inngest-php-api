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
    public static function client(
        array $responses,
        ?string $environment = null,
        string $base_uri = 'https://api.inngest.com/v2',
    ): HttpClient {
        $stack  = HandlerStack::create(new MockHandler($responses));
        $guzzle = new Client(['handler' => $stack]);

        return new HttpClient('test-api-key', $base_uri, $environment, $guzzle);
    }

    /**
     * @param Response[] $responses
     */
    public static function v1Client(array $responses, ?string $environment = null): HttpClient {
        return self::client($responses, $environment, 'https://api.inngest.com');
    }
}
