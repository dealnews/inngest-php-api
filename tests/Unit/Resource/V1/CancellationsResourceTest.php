<?php

namespace DealNews\InngestApi\Tests\Unit\Resource\V1;

use DealNews\InngestApi\Resource\V1\CancellationsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CancellationsResourceTest extends TestCase {

    public function testListReturnsCancellations(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'                    => 'cancel-1',
                        'environment_id'        => 'env-1',
                        'function_internal_id'  => 'fn-internal-1',
                        'function_id'           => 'fn-1',
                        'started_before'        => '2024-01-02T00:00:00Z',
                        'started_after'         => '2024-01-01T00:00:00Z',
                        'if'                    => 'event.data.ok == true',
                    ],
                ],
            ])),
        ]);

        $result = (new CancellationsResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('cancel-1', $result->items[0]->id);
        $this->assertSame('event.data.ok == true', $result->items[0]->if);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->items[0]->started_before);
    }

    public function testCreateSendsBodyAndReturnsCancellation(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    'id'                   => 'cancel-1',
                    'environment_id'       => 'env-1',
                    'function_internal_id' => 'fn-internal-1',
                    'function_id'          => 'fn-1',
                    'started_before'       => '2024-01-02T00:00:00Z',
                ],
            ])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        $cancellation = (new CancellationsResource($http))->create(
            app_id: 'app-1',
            function_id: 'fn-1',
            started_before: new \DateTimeImmutable('2024-01-02T00:00:00Z'),
            if: 'event.data.ok == true',
        );

        $this->assertSame('cancel-1', $cancellation->id);

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('app-1', $body['app_id']);
        $this->assertSame('fn-1', $body['function_id']);
        $this->assertSame('2024-01-02T00:00:00+00:00', $body['started_before']);
        $this->assertSame('event.data.ok == true', $body['if']);
        $this->assertArrayNotHasKey('started_after', $body);
    }

    public function testDeleteSendsDeleteRequest(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], ''),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new CancellationsResource($http))->delete('cancel-1');

        $request = $history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/v1/cancellations/cancel-1', $request->getUri()->getPath());
    }
}
