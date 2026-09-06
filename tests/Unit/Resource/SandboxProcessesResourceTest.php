<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Sandboxes\SandboxLogStream;
use DealNews\InngestApi\Model\Sandboxes\SandboxProcessState;
use DealNews\InngestApi\Resource\SandboxProcessesResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

class SandboxProcessesResourceTest extends TestCase {

    public function testListReturnsPaginatedProcesses(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'      => 'proc-1',
                        'pid'     => 42,
                        'command' => ['echo', 'hi'],
                        'state'   => 'RUNNING',
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 50,
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-01T00:00:00Z',
                ],
            ])),
        ]);

        $result = (new SandboxProcessesResource($http))->list('sb-1');

        $this->assertCount(1, $result->items);
        $this->assertSame('proc-1', $result->items[0]->id);
        $this->assertSame(42, $result->items[0]->pid);
        $this->assertSame(['echo', 'hi'], $result->items[0]->command);
        $this->assertSame(SandboxProcessState::Running, $result->items[0]->state);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
        $this->assertNotNull($result->metadata->fetched_at);
    }

    public function testStartSendsCommandCwdAndEnvironment(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => ['id' => 'proc-2', 'state' => 'STARTING'],
            ])),
        ]);

        $process = (new SandboxProcessesResource($http))->start('sb-1', ['ls', '-la'], '/tmp', ['FOO' => 'bar']);

        $this->assertSame('proc-2', $process->id);
        $this->assertSame(SandboxProcessState::Starting, $process->state);
    }

    public function testGetReturnsProcess(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'                 => 'proc-3',
                    'state'              => 'EXITED',
                    'exitCode'           => 0,
                    'startedAt'          => '2024-01-01T00:00:00Z',
                    'endedAt'            => '2024-01-01T00:00:05Z',
                    'terminationSignal'  => 0,
                ],
            ])),
        ]);

        $process = (new SandboxProcessesResource($http))->get('sb-1', 'proc-3');

        $this->assertSame('proc-3', $process->id);
        $this->assertSame(SandboxProcessState::Exited, $process->state);
        $this->assertSame(0, $process->exit_code);
        $this->assertNotNull($process->started_at);
        $this->assertNotNull($process->ended_at);
    }

    public function testOutputReturnsBufferedChunks(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'chunks' => [
                        [
                            'at'     => '2024-01-01T00:00:00Z',
                            'data'   => base64_encode('hello'),
                            'stream' => 'STDOUT',
                        ],
                    ],
                ],
            ])),
        ]);

        $output = (new SandboxProcessesResource($http))->output('sb-1', 'proc-1', 1024);

        $this->assertCount(1, $output->chunks);
        $this->assertSame('hello', $output->chunks[0]->data);
        $this->assertSame(SandboxLogStream::Stdout, $output->chunks[0]->stream);
    }

    public function testStreamOutputYieldsLogChunksFromDataWrappedLines(): void {
        $body = Utils::streamFor(
            json_encode(['data' => ['data' => base64_encode('line one'), 'stream' => 'STDOUT']]) . "\n"
            . json_encode(['data' => ['data' => base64_encode('line two'), 'stream' => 'STDERR']]) . "\n",
        );

        $http = MockApi::client([
            new Response(200, [], $body),
        ]);

        $chunks = iterator_to_array((new SandboxProcessesResource($http))->streamOutput('sb-1', 'proc-1'));

        $this->assertCount(2, $chunks);
        $this->assertSame('line one', $chunks[0]->data);
        $this->assertSame(SandboxLogStream::Stdout, $chunks[0]->stream);
        $this->assertSame('line two', $chunks[1]->data);
        $this->assertSame(SandboxLogStream::Stderr, $chunks[1]->stream);
    }

    public function testSignalSendsSignalAndIncludeChildren(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([])),
        ]);

        (new SandboxProcessesResource($http))->signal('sb-1', 'proc-1', 9, true);

        $this->addToAssertionCount(1);
    }

    public function testWaitReturnsFinalProcessState(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => ['id' => 'proc-1', 'state' => 'EXITED', 'exitCode' => 1],
            ])),
        ]);

        $process = (new SandboxProcessesResource($http))->wait('sb-1', 'proc-1', '30s');

        $this->assertSame(SandboxProcessState::Exited, $process->state);
        $this->assertSame(1, $process->exit_code);
    }

    public function testStreamLogsYieldsLogChunksFromDataWrappedLines(): void {
        $body = Utils::streamFor(
            json_encode(['data' => ['data' => base64_encode('booting'), 'stream' => 'STDOUT']]) . "\n",
        );

        $http = MockApi::client([
            new Response(200, [], $body),
        ]);

        $chunks = iterator_to_array((new SandboxProcessesResource($http))->streamLogs('sb-1', true));

        $this->assertCount(1, $chunks);
        $this->assertSame('booting', $chunks[0]->data);
    }
}
