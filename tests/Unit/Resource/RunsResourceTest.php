<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Runs\FunctionRunStatus;
use DealNews\InngestApi\Model\Runs\ScoreExperiment;
use DealNews\InngestApi\Model\Runs\ScoreInput;
use DealNews\InngestApi\Resource\RunsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RunsResourceTest extends TestCase {

    /**
     * @return array<string, mixed>
     */
    protected function functionRunPayload(): array {
        return [
            'id'         => 'run-1',
            'app'        => ['id' => 'app-1'],
            'function'   => ['id' => 'fn-1', 'name' => 'My Function', 'app' => ['id' => 'app-1']],
            'status'     => 'COMPLETED',
            'trigger'    => [
                'eventName' => 'user.created',
                'eventIds'  => ['evt-1'],
                'isBatch'   => false,
            ],
            'durationMs' => '1500',
            'output'     => ['ok' => true],
            'queuedAt'   => '2024-01-01T00:00:00Z',
            'startedAt'  => '2024-01-01T00:00:01Z',
            'endedAt'    => '2024-01-01T00:00:02Z',
        ];
    }

    public function testListReturnsPaginatedRuns(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [$this->functionRunPayload()],
                'page' => ['cursor' => 'next', 'hasMore' => true, 'limit' => 20],
            ])),
        ]);

        $result = (new RunsResource($http))->list(status: ['COMPLETED']);

        $this->assertCount(1, $result->items);
        $this->assertSame('run-1', $result->items[0]->id);
        $this->assertSame(FunctionRunStatus::Completed, $result->items[0]->status);
        $this->assertSame('app-1', $result->items[0]->app->id);
        $this->assertSame('fn-1', $result->items[0]->function->id);
        $this->assertSame('user.created', $result->items[0]->trigger->event_name);
        $this->assertSame('1500', $result->items[0]->duration_ms);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next', $result->page->cursor);
    }

    public function testListSendsFilterQueryParams(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));

        (new RunsResource($http))->list(
            cursor: 'cur-1',
            limit: 10,
            include_output: true,
            from: new \DateTimeImmutable('2024-01-01T00:00:00Z'),
            until: new \DateTimeImmutable('2024-01-02T00:00:00Z'),
            time_field: 'startedAt',
            status: ['COMPLETED', 'FAILED'],
            app_id: ['app-1'],
            function_id: ['fn-1'],
            is_deferred: false,
            order: 'ASC',
        );

        // Array filters are sent as repeated key=value pairs (RPC-style),
        // not PHP's bracketed status[0]=... form, so parse the raw pairs
        // instead of using parse_str() (which only aggregates duplicate
        // keys into an array when they use bracket syntax).
        $query = $history[0]['request']->getUri()->getQuery();
        $pairs = [];

        foreach (explode('&', $query) as $pair) {
            [$key, $value]  = array_map('rawurldecode', explode('=', $pair, 2));
            $pairs[$key][]  = $value;
        }

        $this->assertSame(['cur-1'], $pairs['cursor']);
        $this->assertSame(['10'], $pairs['limit']);
        $this->assertSame(['true'], $pairs['includeOutput']);
        $this->assertSame(['2024-01-01T00:00:00+00:00'], $pairs['from']);
        $this->assertSame(['2024-01-02T00:00:00+00:00'], $pairs['until']);
        $this->assertSame(['startedAt'], $pairs['timeField']);
        $this->assertSame(['COMPLETED', 'FAILED'], $pairs['status']);
        $this->assertSame(['app-1'], $pairs['appId']);
        $this->assertSame(['fn-1'], $pairs['functionId']);
        $this->assertSame(['false'], $pairs['isDeferred']);
        $this->assertSame(['ASC'], $pairs['order']);
    }

    public function testListByFunctionReturnsPaginatedRuns(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [$this->functionRunPayload()],
                'page' => ['cursor' => null, 'hasMore' => false, 'limit' => 20],
            ])),
        ]);

        $result = (new RunsResource($http))->listByFunction('app-1', 'fn-1');

        $this->assertCount(1, $result->items);
        $this->assertSame('run-1', $result->items[0]->id);
        $this->assertFalse($result->page->has_more);
    }

    public function testListByEventReturnsPaginatedRuns(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [$this->functionRunPayload()],
                'page' => ['cursor' => null, 'hasMore' => false, 'limit' => 20],
            ])),
        ]);

        $result = (new RunsResource($http))->listByEvent('evt-1');

        $this->assertCount(1, $result->items);
        $this->assertSame('run-1', $result->items[0]->id);
    }

    public function testGetReturnsRunWithNestedObjects(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => $this->functionRunPayload(),
            ])),
        ]);

        $run = (new RunsResource($http))->get('run-1');

        $this->assertSame('run-1', $run->id);
        $this->assertSame('app-1', $run->app->id);
        $this->assertSame('fn-1', $run->function->id);
        $this->assertSame('app-1', $run->function->app->id);
        $this->assertSame(['evt-1'], $run->trigger->event_ids);
        $this->assertSame(FunctionRunStatus::Completed, $run->status);
        $this->assertInstanceOf(\DateTimeImmutable::class, $run->queued_at);
    }

    public function testCancelReturnsCancelledRunId(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => ['runId' => 'run-1'],
            ])),
        ]);

        $result = (new RunsResource($http))->cancel('run-1');

        $this->assertSame('run-1', $result->run_id);
    }

    public function testRerunSendsFromStepAndReturnsNewRunId(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode([
                'data' => ['runId' => 'run-2'],
            ])),
            new Response(200, [], json_encode([
                'data' => ['runId' => 'run-2'],
            ])),
        ]));
        $stack->push(Middleware::history($history));

        $guzzle = new \GuzzleHttp\Client(['handler' => $stack]);
        $http   = new \DealNews\InngestApi\HttpClient('test-api-key', 'https://api.inngest.com/v2', null, $guzzle);

        $result = (new RunsResource($http))->rerun('run-1', step_id: null, input: null);

        $this->assertSame('run-2', $result->run_id);

        $result = (new RunsResource($http))->rerun('run-1', step_id: 'step-1', input: [['foo' => 'bar']]);

        $body = json_decode((string) $history[1]['request']->getBody(), true);
        $this->assertSame('step-1', $body['fromStep']['stepId']);
        $this->assertSame([['foo' => 'bar']], $body['fromStep']['input']);

        $body = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame([], $body);
    }

    public function testCreateScoresSendsArrayBodyAndReturnsScores(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'name'       => 'accuracy',
                        'value'      => 0.95,
                        'runId'      => 'run-1',
                        'stepId'     => 'generate-summary',
                        'experiment' => ['id' => 'model-routing', 'variant' => 'baseline'],
                    ],
                ],
            ])),
        ]);

        $scores = (new RunsResource($http))->createScores('run-1', [
            new ScoreInput(
                name: 'accuracy',
                value: 0.95,
                step_id: 'generate-summary',
                experiment: new ScoreExperiment('model-routing', 'baseline'),
            ),
        ]);

        $this->assertCount(1, $scores);
        $this->assertSame('accuracy', $scores[0]->name);
        $this->assertSame(0.95, $scores[0]->value);
        $this->assertSame('run-1', $scores[0]->run_id);
        $this->assertSame('model-routing', $scores[0]->experiment->id);
        $this->assertSame('baseline', $scores[0]->experiment->variant);
    }

    public function testGetTraceReturnsTraceWithNestedSpans(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'runId'    => 'run-1',
                    'rootSpan' => [
                        'id'       => 'span-1',
                        'name'     => 'root',
                        'status'   => 'COMPLETED',
                        'stepOp'   => 'RUN',
                        'children' => [
                            [
                                'id'       => 'span-2',
                                'name'     => 'child',
                                'status'   => 'FAILED',
                                'stepOp'   => 'SLEEP',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ])),
        ]);

        $trace = (new RunsResource($http))->getTrace('run-1');

        $this->assertSame('run-1', $trace->run_id);
        $this->assertSame('span-1', $trace->root_span->id);
        $this->assertCount(1, $trace->root_span->children);
        $this->assertSame('span-2', $trace->root_span->children[0]->id);
        $this->assertSame(\DealNews\InngestApi\Model\Runs\TraceSpanStatus::Failed, $trace->root_span->children[0]->status);
        $this->assertSame(\DealNews\InngestApi\Model\Runs\TraceStepOp::Sleep, $trace->root_span->children[0]->step_op);
    }
}
