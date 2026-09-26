<?php

namespace DealNews\InngestApi\Tests\Unit\Resource\V1;

use DealNews\InngestApi\Resource\V1\EventsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EventsResourceTest extends TestCase {

    /**
     * @return array<string, mixed>
     */
    protected function eventPayload(): array {
        return [
            'internal_id'   => 'evt-internal-1',
            'accountID'     => 'acct-1',
            'environmentID' => 'env-1',
            'source'        => 'key',
            'sourceID'      => 'key-1',
            'receivedAt'    => '2024-01-01T00:00:00Z',
            'id'            => 'evt-1',
            'name'          => 'user.created',
            'data'          => ['foo' => 'bar'],
            'user'          => null,
            'ts'            => 1704067200000,
            'v'             => '1',
        ];
    }

    public function testListReturnsEventsAndMetadata(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode([
                'data'     => [$this->eventPayload()],
                'metadata' => ['fetchedAt' => '2024-01-01T00:00:05Z', 'cachedUntil' => null],
            ])),
        ]);

        $result = (new EventsResource($http))->list(name: 'user.created', limit: 10);

        $this->assertCount(1, $result->items);
        $this->assertSame('evt-internal-1', $result->items[0]->internal_id);
        $this->assertSame('user.created', $result->items[0]->name);
        $this->assertSame(['foo' => 'bar'], $result->items[0]->data);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->items[0]->received_at);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->metadata->fetched_at);
    }

    public function testListSendsQueryParams(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new EventsResource($http))->list(
            received_before: new \DateTimeImmutable('2024-01-02T00:00:00Z'),
            received_after:  new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            limit:           5,
            name:            'user.created',
            cursor:          'cur-1',
        );

        $request = $history[0]['request'];
        $this->assertSame('/v1/events', $request->getUri()->getPath());

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertSame('2024-01-02T00:00:00+00:00', $query['received_before']);
        $this->assertSame('2024-01-01T00:00:00+00:00', $query['received_after']);
        $this->assertSame('5', $query['limit']);
        $this->assertSame('user.created', $query['name']);
        $this->assertSame('cur-1', $query['cursor']);
    }

    public function testGetReturnsEvent(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode(['data' => $this->eventPayload()])),
        ]);

        $event = (new EventsResource($http))->get('evt-internal-1');

        $this->assertSame('evt-internal-1', $event->internal_id);
        $this->assertSame('evt-1', $event->id);
    }

    public function testListRunsReturnsFunctionRuns(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'run_id'           => 'run-1',
                        'run_started_at'   => '2024-01-01T00:00:00Z',
                        'status'           => 'Completed',
                        'function_id'      => 'fn-1',
                        'function_version' => 1,
                        'environment_id'   => 'env-1',
                    ],
                ],
                'metadata' => ['fetchedAt' => '2024-01-01T00:00:05Z'],
            ])),
        ]);

        $result = (new EventsResource($http))->listRuns('evt-internal-1');

        $this->assertCount(1, $result->items);
        $this->assertSame('run-1', $result->items[0]->run_id);
        $this->assertSame(\DealNews\InngestApi\Model\V1\Runs\FunctionRunStatus::Completed, $result->items[0]->status);
    }
}
