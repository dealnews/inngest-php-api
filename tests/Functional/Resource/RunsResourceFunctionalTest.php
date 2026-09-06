<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Runs\FunctionRun;
use DealNews\InngestApi\Model\Runs\FunctionTrace;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class RunsResourceFunctionalTest extends FunctionalTestCase {

    protected static ?FunctionRun $first_run = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $runs = self::$client->runs()->list(limit: 1)->items;

        self::$first_run = $runs[0] ?? null;
    }

    public function testListReturnsRuns(): void {
        $result = $this->client()->runs()->list(limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $run) {
            $this->assertInstanceOf(FunctionRun::class, $run);
        }
    }

    public function testGetAndTraceForFirstListedRun(): void {
        $run = $this->firstRun();

        $fetched = $this->client()->runs()->get($run->id);
        $this->assertSame($run->id, $fetched->id);

        $trace = $this->client()->runs()->getTrace($run->id);
        $this->assertInstanceOf(FunctionTrace::class, $trace);
    }

    public function testListByFunctionReturnsRunsForThatFunction(): void {
        $run = $this->firstRun();

        if ($run->app === null || $run->function === null) {
            $this->markTestSkipped('The most recent run has no app/function reference.');
        }

        $result = $this->client()->runs()->listByFunction($run->app->id, $run->function->id, limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $item) {
            $this->assertInstanceOf(FunctionRun::class, $item);
        }
    }

    public function testListByEventReturnsRunsForThatEvent(): void {
        $run = $this->firstRun();

        if ($run->trigger === null || $run->trigger->event_ids === []) {
            $this->markTestSkipped('The most recent run was not triggered by a single event.');
        }

        $result = $this->client()->runs()->listByEvent($run->trigger->event_ids[0], limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $item) {
            $this->assertInstanceOf(FunctionRun::class, $item);
        }
    }

    protected function firstRun(): FunctionRun {
        if (self::$first_run === null) {
            $this->markTestSkipped('No runs exist in this account.');
        }

        return self::$first_run;
    }
}
