<?php

namespace DealNews\InngestApi\Tests\Unit\Resource\V1;

use DealNews\InngestApi\Model\V1\Runs\FunctionRunStatus;
use DealNews\InngestApi\Resource\V1\RunsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class RunsResourceTest extends TestCase {

    public function testGetReturnsFunctionRun(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode([
                'data' => [
                    'run_id'           => 'run-1',
                    'run_started_at'   => '2024-01-01T00:00:00Z',
                    'ended_at'         => '2024-01-01T00:00:02Z',
                    'status'           => 'Failed',
                    'output'           => ['error' => 'boom'],
                    'function_id'      => 'fn-1',
                    'function_version' => 3,
                    'environment_id'   => 'env-1',
                    'event_id'         => 'evt-1',
                    'batch_id'         => null,
                    'original_run_id'  => null,
                    'cron'             => null,
                ],
            ])),
        ]);

        $run = (new RunsResource($http))->get('run-1');

        $this->assertSame('run-1', $run->run_id);
        $this->assertSame(FunctionRunStatus::Failed, $run->status);
        $this->assertSame(['error' => 'boom'], $run->output);
        $this->assertSame(3, $run->function_version);
        $this->assertInstanceOf(\DateTimeImmutable::class, $run->run_started_at);
        $this->assertInstanceOf(\DateTimeImmutable::class, $run->ended_at);
    }

    public function testCancelSendsDeleteRequest(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(204, [], ''),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new RunsResource($http))->cancel('run-1');

        $request = $history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/v1/runs/run-1', $request->getUri()->getPath());
    }

    public function testJobsReturnsJobsAndMetadata(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode([
                'data' => [
                    ['at' => '2024-01-01T00:00:00Z', 'position' => 0, 'attempt' => 0],
                    ['at' => '2024-01-01T00:00:01Z', 'position' => 1, 'attempt' => 0],
                ],
                'metadata' => ['fetchedAt' => '2024-01-01T00:00:05Z'],
            ])),
        ]);

        $result = (new RunsResource($http))->jobs('run-1');

        $this->assertCount(2, $result->items);
        $this->assertSame(0, $result->items[0]->position);
        $this->assertSame(1, $result->items[1]->position);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->metadata->fetched_at);
    }
}
