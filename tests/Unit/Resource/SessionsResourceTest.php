<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\HttpClient;
use DealNews\InngestApi\Model\Sessions\FunctionRunStatus;
use DealNews\InngestApi\Resource\SessionsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SessionsResourceTest extends TestCase {

    public function testListKeysReturnsPaginatedSessionKeys(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'session-key-1', 'createdAt' => '2024-01-01T00:00:00Z'],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 20,
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-02T00:00:00Z',
                ],
            ])),
        ]);

        $result = (new SessionsResource($http))->listKeys();

        $this->assertCount(1, $result->items);
        $this->assertSame('session-key-1', $result->items[0]->id);
        $this->assertSame('2024-01-01T00:00:00+00:00', $result->items[0]->created_at->format('c'));
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
        $this->assertSame('2024-01-02T00:00:00+00:00', $result->metadata->fetched_at->format('c'));
    }

    public function testListKeysSendsSearchCursorAndLimit(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        (new SessionsResource($http))->listKeys(search: 'checkout', cursor: 'abc', limit: 10);

        $query = $history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('search=checkout', $query);
        $this->assertStringContainsString('cursor=abc', $query);
        $this->assertStringContainsString('limit=10', $query);
    }

    public function testListReturnsPaginatedSessionGroups(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'             => 'session-1',
                        'functions'      => [
                            [
                                'id'   => 'fn-1',
                                'name' => 'My Function',
                                'app'  => ['id' => 'app-1'],
                            ],
                        ],
                        'runCount'       => 5,
                        'failedRunCount' => 1,
                        'failureRate'    => 0.2,
                        'lastActiveAt'   => '2024-01-03T00:00:00Z',
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => false,
                    'limit'   => 20,
                ],
            ])),
        ]);

        $result = (new SessionsResource($http))->list('session-key-1');

        $this->assertCount(1, $result->items);
        $group = $result->items[0];
        $this->assertSame('session-1', $group->id);
        $this->assertSame(5, $group->run_count);
        $this->assertSame(1, $group->failed_run_count);
        $this->assertSame(0.2, $group->failure_rate);
        $this->assertCount(1, $group->functions);
        $this->assertSame('fn-1', $group->functions[0]->id);
        $this->assertSame('app-1', $group->functions[0]->app->id);
        $this->assertFalse($result->page->has_more);
    }

    public function testListSendsSessionKeyInPathAndDateRangeInQuery(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));

        $from  = new \DateTimeImmutable('2024-01-01T00:00:00Z');
        $until = new \DateTimeImmutable('2024-01-31T00:00:00Z');

        (new SessionsResource($http))->list('session-key-1', from: $from, until: $until);

        $request = $history[0]['request'];
        $this->assertSame('/v2/sessions/session-key-1', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('from=' . rawurlencode($from->format(\DateTimeInterface::RFC3339)), $query);
        $this->assertStringContainsString('until=' . rawurlencode($until->format(\DateTimeInterface::RFC3339)), $query);
    }

    public function testListRunsReturnsPaginatedSessionRuns(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'        => 'run-1',
                        'eventName' => 'orders/payment.created',
                        'function'  => ['id' => 'fn-1', 'name' => 'My Function', 'app' => ['id' => 'app-1']],
                        'status'    => 'COMPLETED',
                        'queuedAt'  => '2024-01-01T00:00:00Z',
                        'startedAt' => '2024-01-01T00:00:01Z',
                        'endedAt'   => '2024-01-01T00:00:02Z',
                    ],
                ],
                'page' => [
                    'cursor'  => null,
                    'hasMore' => false,
                    'limit'   => 20,
                ],
            ])),
        ]);

        $result = (new SessionsResource($http))->listRuns('session-key-1', 'session-1');

        $this->assertCount(1, $result->items);
        $run = $result->items[0];
        $this->assertSame('run-1', $run->id);
        $this->assertSame('orders/payment.created', $run->event_name);
        $this->assertSame(FunctionRunStatus::Completed, $run->status);
        $this->assertSame('fn-1', $run->function->id);
        $this->assertSame('app-1', $run->function->app->id);
        $this->assertNotNull($run->queued_at);
        $this->assertNotNull($run->started_at);
        $this->assertNotNull($run->ended_at);
    }

    public function testListRunsSendsSessionKeyAndSessionIdInPath(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        (new SessionsResource($http))->listRuns('session-key-1', 'session-1', limit: 5);

        $request = $history[0]['request'];
        $this->assertSame('/v2/sessions/session-key-1/session-1/runs', $request->getUri()->getPath());
        $this->assertStringContainsString('limit=5', $request->getUri()->getQuery());
    }
}
