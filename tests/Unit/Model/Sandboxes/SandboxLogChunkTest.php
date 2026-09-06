<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sandboxes;

use DealNews\InngestApi\Model\Sandboxes\SandboxLogChunk;
use DealNews\InngestApi\Model\Sandboxes\SandboxLogStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SandboxLogChunkTest extends TestCase {

    public function testFromArrayDecodesBase64DataAndMapsStream(): void {
        $chunk = SandboxLogChunk::fromArray([
            'at'       => '2024-01-01T00:00:00Z',
            'data'     => base64_encode('hello world'),
            'encoding' => 'utf-8',
            'stream'   => 'STDOUT',
        ]);

        $this->assertSame('hello world', $chunk->data);
        $this->assertSame('utf-8', $chunk->encoding);
        $this->assertSame(SandboxLogStream::Stdout, $chunk->stream);
        $this->assertNotNull($chunk->at);
    }

    #[DataProvider('streamProvider')]
    public function testFromArrayMapsAllStreamValues(string $raw, SandboxLogStream $expected): void {
        $chunk = SandboxLogChunk::fromArray(['stream' => $raw]);

        $this->assertSame($expected, $chunk->stream);
    }

    /**
     * @return array<string, array{0: string, 1: SandboxLogStream}>
     */
    public static function streamProvider(): array {
        return [
            'unspecified' => ['UNSPECIFIED', SandboxLogStream::Unspecified],
            'stdout'      => ['STDOUT', SandboxLogStream::Stdout],
            'stderr'      => ['STDERR', SandboxLogStream::Stderr],
        ];
    }

    public function testFromArrayHandlesMissingDataAndStream(): void {
        $chunk = SandboxLogChunk::fromArray([]);

        $this->assertNull($chunk->data);
        $this->assertNull($chunk->stream);
        $this->assertNull($chunk->at);
    }
}
