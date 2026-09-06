<?php

namespace DealNews\InngestApi\Tests\Unit;

use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class HttpClientStreamTest extends TestCase {

    public function testStreamYieldsDecodedNewlineDelimitedJsonLines(): void {
        $body = Utils::streamFor(
            json_encode(['data' => ['at' => '2024-01-01T00:00:00Z']]) . "\n"
            . json_encode(['data' => ['at' => '2024-01-01T00:00:01Z']]) . "\n",
        );

        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], $body),
        ]));

        $http  = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $lines = iterator_to_array($http->stream('GET', '/sandboxes/sb-1/logs'));

        $this->assertCount(2, $lines);
        $this->assertSame('2024-01-01T00:00:00Z', $lines[0]['data']['at']);
        $this->assertSame('2024-01-01T00:00:01Z', $lines[1]['data']['at']);
    }

    public function testStreamSkipsBlankLinesAndDecodesTrailingLineWithoutNewline(): void {
        $body = Utils::streamFor(
            "\n" . json_encode(['data' => ['at' => 'a']]) . "\n\n" . json_encode(['data' => ['at' => 'b']]),
        );

        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], $body),
        ]));

        $http  = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $lines = iterator_to_array($http->stream('GET', '/sandboxes/sb-1/logs'));

        $this->assertCount(2, $lines);
        $this->assertSame('a', $lines[0]['data']['at']);
        $this->assertSame('b', $lines[1]['data']['at']);
    }

    public function testStreamAppliesBearerAuthAndEnvironmentHeader(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], Utils::streamFor('')),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('secret-key', 'https://api.inngest.com/v2', 'branch-env', new Client(['handler' => $stack]));
        iterator_to_array($http->stream('GET', '/sandboxes/sb-1/logs'));

        $request = $history[0]['request'];

        $this->assertSame('Bearer secret-key', $request->getHeaderLine('Authorization'));
        $this->assertSame('branch-env', $request->getHeaderLine('X-Inngest-Env'));
        $this->assertSame('https://api.inngest.com/v2/sandboxes/sb-1/logs', (string) $request->getUri());
    }

    public function testStreamThrowsMappedExceptionBeforeStreamingOnErrorStatus(): void {
        $stack = HandlerStack::create(new MockHandler([
            new Response(404, [], json_encode(['errors' => [['code' => 'not_found', 'message' => 'Sandbox not found']]])),
        ]));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Sandbox not found');

        iterator_to_array($http->stream('GET', '/sandboxes/sb-1/logs'));
    }
}
