<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sandboxes;

use DealNews\InngestApi\Model\Sandboxes\Sandbox;
use DealNews\InngestApi\Model\Sandboxes\SandboxStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SandboxTest extends TestCase {

    public function testFromArrayMapsNestedResourcesAndDates(): void {
        $sandbox = Sandbox::fromArray([
            'id'        => 'sbx-1',
            'name'      => 'build-sandbox',
            'status'    => 'RUNNING',
            'resources' => ['vcpu' => 4, 'memoryMb' => 4096],
            'imageRef'  => 'ghcr.io/inngest/sandbox:latest',
            'vpcId'     => 'vpc-1',
            'createdAt' => '2024-01-01T00:00:00Z',
            'startedAt' => '2024-01-01T00:00:05Z',
        ]);

        $this->assertSame('sbx-1', $sandbox->id);
        $this->assertSame(SandboxStatus::Running, $sandbox->status);
        $this->assertSame(4, $sandbox->resources->vcpu);
        $this->assertSame(4096, $sandbox->resources->memory_mb);
        $this->assertSame('ghcr.io/inngest/sandbox:latest', $sandbox->image_ref);
        $this->assertSame('vpc-1', $sandbox->vpc_id);
        $this->assertNotNull($sandbox->created_at);
        $this->assertNotNull($sandbox->started_at);
        $this->assertNull($sandbox->ended_at);
    }

    #[DataProvider('statusProvider')]
    public function testFromArrayMapsStatus(?string $raw, ?SandboxStatus $expected): void {
        $sandbox = Sandbox::fromArray(array_filter([
            'id'     => 'sbx-2',
            'status' => $raw,
        ], static fn ($value) => $value !== null));

        $this->assertSame($expected, $sandbox->status);
    }

    /**
     * @return array<string, array{0: ?string, 1: ?SandboxStatus}>
     */
    public static function statusProvider(): array {
        return [
            'unspecified'  => ['UNSPECIFIED', SandboxStatus::Unspecified],
            'pending'      => ['PENDING', SandboxStatus::Pending],
            'starting'     => ['STARTING', SandboxStatus::Starting],
            'running'      => ['RUNNING', SandboxStatus::Running],
            'paused'       => ['PAUSED', SandboxStatus::Paused],
            'terminating'  => ['TERMINATING', SandboxStatus::Terminating],
            'terminated'   => ['TERMINATED', SandboxStatus::Terminated],
            'failed'       => ['FAILED', SandboxStatus::Failed],
            'missing'      => [null, null],
            'unknown'      => ['SOMETHING_NEW', null],
        ];
    }

    public function testFromArrayDefaultsToNulls(): void {
        $sandbox = Sandbox::fromArray([]);

        $this->assertNull($sandbox->id);
        $this->assertNull($sandbox->status);
        $this->assertNull($sandbox->resources);
        $this->assertNull($sandbox->error);
    }
}
