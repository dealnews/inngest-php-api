<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Runs;

use DealNews\InngestApi\Model\Runs\TraceSpan;
use DealNews\InngestApi\Model\Runs\TraceSpanStatus;
use DealNews\InngestApi\Model\Runs\TraceStepOp;
use PHPUnit\Framework\TestCase;

class TraceSpanTest extends TestCase {

    public function testFromArrayParsesRecursiveChildrenAndMetadata(): void {
        $span = TraceSpan::fromArray([
            'id'         => 'span-1',
            'name'       => 'root',
            'stepId'     => 'step-1',
            'stepOp'     => 'INVOKE',
            'status'     => 'RUNNING',
            'durationMs' => '42',
            'input'      => ['a' => 1],
            'output'     => null,
            'queuedAt'   => '2024-01-01T00:00:00Z',
            'startedAt'  => '2024-01-01T00:00:01Z',
            'metadata'   => [
                ['kind' => 'ai', 'scope' => 'run', 'updatedAt' => '2024-01-01T00:00:01Z', 'values' => ['model' => 'gpt']],
            ],
            'children' => [
                [
                    'id'       => 'span-2',
                    'name'     => 'child',
                    'status'   => 'COMPLETED',
                    'stepOp'   => 'SEND_EVENT',
                    'children' => [
                        [
                            'id'     => 'span-3',
                            'name'   => 'grandchild',
                            'status' => 'SKIPPED',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('span-1', $span->id);
        $this->assertSame(TraceStepOp::Invoke, $span->step_op);
        $this->assertSame(TraceSpanStatus::Running, $span->status);
        $this->assertSame('42', $span->duration_ms);
        $this->assertCount(1, $span->metadata);
        $this->assertSame('ai', $span->metadata[0]->kind);
        $this->assertSame(['model' => 'gpt'], $span->metadata[0]->values);

        $this->assertCount(1, $span->children);
        $child = $span->children[0];
        $this->assertSame('span-2', $child->id);
        $this->assertSame(TraceStepOp::SendEvent, $child->step_op);

        $this->assertCount(1, $child->children);
        $grandchild = $child->children[0];
        $this->assertSame('span-3', $grandchild->id);
        $this->assertSame(TraceSpanStatus::Skipped, $grandchild->status);
        $this->assertSame([], $grandchild->children);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void {
        $span = TraceSpan::fromArray([]);

        $this->assertNull($span->id);
        $this->assertNull($span->status);
        $this->assertNull($span->step_op);
        $this->assertSame([], $span->children);
        $this->assertSame([], $span->metadata);
    }
}
