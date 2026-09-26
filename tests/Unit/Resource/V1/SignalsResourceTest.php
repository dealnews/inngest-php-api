<?php

namespace DealNews\InngestApi\Tests\Unit\Resource\V1;

use DealNews\InngestApi\Resource\V1\SignalsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SignalsResourceTest extends TestCase {

    public function testResumeReturnsRunId(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode(['run_id' => 'run-1'])),
        ]);

        $result = (new SignalsResource($http))->resume('my-signal', ['ok' => true]);

        $this->assertSame('run-1', $result->run_id);
    }

    public function testResumeSendsSignalAndDataInBody(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['run_id' => 'run-1'])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new SignalsResource($http))->resume('my-signal', ['ok' => true]);

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('my-signal', $body['signal']);
        $this->assertSame(['ok' => true], $body['data']);
    }

    public function testResumeOmitsDataWhenNotGiven(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['run_id' => 'run-1'])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new SignalsResource($http))->resume('my-signal');

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame(['signal' => 'my-signal'], $body);
    }
}
