<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sessions;

use DealNews\InngestApi\Model\Sessions\FunctionRunStatus;
use DealNews\InngestApi\Model\Sessions\SessionRun;
use PHPUnit\Framework\TestCase;

class SessionRunTest extends TestCase {

    public function testFromArrayMapsFieldsAndNestedFunctionRef(): void {
        $run = SessionRun::fromArray([
            'id'        => 'run-1',
            'eventName' => 'orders/payment.created',
            'function'  => ['id' => 'fn-1', 'name' => 'My Function', 'app' => ['id' => 'app-1']],
            'status'    => 'FAILED',
            'queuedAt'  => '2024-01-01T00:00:00Z',
            'startedAt' => '2024-01-01T00:00:01Z',
            'endedAt'   => '2024-01-01T00:00:02Z',
        ]);

        $this->assertSame('run-1', $run->id);
        $this->assertSame('orders/payment.created', $run->event_name);
        $this->assertSame(FunctionRunStatus::Failed, $run->status);
        $this->assertSame('fn-1', $run->function->id);
        $this->assertSame('app-1', $run->function->app->id);
        $this->assertSame('2024-01-01T00:00:00+00:00', $run->queued_at->format('c'));
        $this->assertSame('2024-01-01T00:00:01+00:00', $run->started_at->format('c'));
        $this->assertSame('2024-01-01T00:00:02+00:00', $run->ended_at->format('c'));
    }

    public function testFromArrayDefaultsMissingFieldsToNull(): void {
        $run = SessionRun::fromArray([]);

        $this->assertNull($run->id);
        $this->assertNull($run->event_name);
        $this->assertNull($run->function);
        $this->assertNull($run->status);
        $this->assertNull($run->queued_at);
        $this->assertNull($run->started_at);
        $this->assertNull($run->ended_at);
    }

    public function testFromArrayReturnsNullStatusForUnknownValue(): void {
        $run = SessionRun::fromArray(['status' => 'SOMETHING_NEW']);

        $this->assertNull($run->status);
    }
}
