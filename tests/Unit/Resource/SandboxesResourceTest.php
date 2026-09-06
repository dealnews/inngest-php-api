<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Sandboxes\SandboxStatus;
use DealNews\InngestApi\Resource\SandboxesResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SandboxesResourceTest extends TestCase {

    public function testListReturnsPaginatedSandboxes(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'        => 'sbx-1',
                        'name'      => 'build-sandbox',
                        'status'    => 'RUNNING',
                        'resources' => ['vcpu' => 2, 'memoryMb' => 2048],
                        'createdAt' => '2024-01-01T00:00:00Z',
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 50,
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-01T00:00:01Z',
                ],
            ])),
        ]);

        $result = (new SandboxesResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('sbx-1', $result->items[0]->id);
        $this->assertSame(SandboxStatus::Running, $result->items[0]->status);
        $this->assertSame(2, $result->items[0]->resources->vcpu);
        $this->assertSame(2048, $result->items[0]->resources->memory_mb);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
        $this->assertNotNull($result->metadata->fetched_at);
    }

    public function testCreateSendsNameAndResources(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'        => 'sbx-2',
                    'name'      => 'my-sandbox',
                    'status'    => 'PENDING',
                    'resources' => ['vcpu' => 1, 'memoryMb' => 1024],
                ],
            ])),
        ]);

        $sandbox = (new SandboxesResource($http))->create('my-sandbox', vcpu: 1, memory_mb: 1024);

        $this->assertSame('sbx-2', $sandbox->id);
        $this->assertSame('my-sandbox', $sandbox->name);
        $this->assertSame(SandboxStatus::Pending, $sandbox->status);
    }

    public function testGetReturnsSandbox(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'     => 'sbx-3',
                    'name'   => 'my-sandbox',
                    'status' => 'RUNNING',
                ],
            ])),
        ]);

        $sandbox = (new SandboxesResource($http))->get('sbx-3');

        $this->assertSame('sbx-3', $sandbox->id);
        $this->assertSame(SandboxStatus::Running, $sandbox->status);
    }

    public function testDestroyReturnsFinalState(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'      => 'sbx-4',
                    'status'  => 'TERMINATED',
                    'endedAt' => '2024-01-02T00:00:00Z',
                ],
            ])),
        ]);

        $sandbox = (new SandboxesResource($http))->destroy('sbx-4');

        $this->assertSame(SandboxStatus::Terminated, $sandbox->status);
        $this->assertNotNull($sandbox->ended_at);
    }

    public function testExecReturnsDecodedStdoutAndStderr(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'exitCode' => 0,
                    'stdout'   => base64_encode('hello'),
                    'stderr'   => base64_encode(''),
                    'encoding' => 'utf-8',
                ],
            ])),
        ]);

        $result = (new SandboxesResource($http))->exec('sbx-5', ['echo', 'hello']);

        $this->assertSame(0, $result->exit_code);
        $this->assertSame('hello', $result->stdout);
        $this->assertSame('', $result->stderr);
        $this->assertSame('utf-8', $result->encoding);
    }

    public function testReadFileReturnsRawBodyAndContentType(): void {
        $http = MockApi::client([
            new Response(200, ['Content-Type' => 'text/plain'], 'file contents'),
        ]);

        $file = (new SandboxesResource($http))->readFile('sbx-6', '/tmp/out.txt');

        $this->assertSame('file contents', $file->content);
        $this->assertSame('text/plain', $file->content_type);
    }

    public function testWriteFileReturnsBytesWritten(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'path'         => '/tmp/out.txt',
                    'bytesWritten' => '13',
                ],
            ])),
        ]);

        $result = (new SandboxesResource($http))->writeFile('sbx-7', 'file contents', path: '/tmp/out.txt');

        $this->assertSame('/tmp/out.txt', $result->path);
        $this->assertSame(13, $result->bytes_written);
    }
}
