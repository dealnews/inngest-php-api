<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Sessions;

use DealNews\InngestApi\Model\Sessions\SessionGroup;
use PHPUnit\Framework\TestCase;

class SessionGroupTest extends TestCase {

    public function testFromArrayMapsFieldsAndNestedFunctions(): void {
        $group = SessionGroup::fromArray([
            'id'             => 'session-1',
            'functions'      => [
                ['id' => 'fn-1', 'name' => 'First Function', 'app' => ['id' => 'app-1']],
                ['id' => 'fn-2', 'name' => 'Second Function'],
            ],
            'runCount'       => 3,
            'failedRunCount' => 1,
            'failureRate'    => 0.33,
            'lastActiveAt'   => '2024-02-01T12:00:00Z',
        ]);

        $this->assertSame('session-1', $group->id);
        $this->assertSame(3, $group->run_count);
        $this->assertSame(1, $group->failed_run_count);
        $this->assertSame(0.33, $group->failure_rate);
        $this->assertSame('2024-02-01T12:00:00+00:00', $group->last_active_at->format('c'));

        $this->assertCount(2, $group->functions);
        $this->assertSame('fn-1', $group->functions[0]->id);
        $this->assertSame('app-1', $group->functions[0]->app->id);
        $this->assertSame('fn-2', $group->functions[1]->id);
        $this->assertNull($group->functions[1]->app);
    }

    public function testFromArrayDefaultsMissingFieldsToNull(): void {
        $group = SessionGroup::fromArray([]);

        $this->assertNull($group->id);
        $this->assertSame([], $group->functions);
        $this->assertNull($group->run_count);
        $this->assertNull($group->failed_run_count);
        $this->assertNull($group->failure_rate);
        $this->assertNull($group->last_active_at);
    }
}
