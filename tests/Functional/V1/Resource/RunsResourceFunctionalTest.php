<?php

namespace DealNews\InngestApi\Tests\Functional\V1\Resource;

use DealNews\InngestApi\Model\V1\Events\Event;
use DealNews\InngestApi\Model\V1\Runs\FunctionRun;
use DealNews\InngestApi\Tests\Functional\V1\V1FunctionalTestCase;

class RunsResourceFunctionalTest extends V1FunctionalTestCase {

    protected static ?FunctionRun $first_run = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        self::skipIfUnavailable(function () {
            $events = self::$client->events()->list(limit: 1)->items;

            /** @var Event|null $event */
            $event = $events[0] ?? null;

            if ($event !== null) {
                $runs = self::$client->events()->listRuns($event->internal_id)->items;

                self::$first_run = $runs[0] ?? null;
            }
        });
    }

    public function testGetReturnsFirstRunFoundViaAnEvent(): void {
        $run = $this->firstRun();

        $fetched = $this->client()->runs()->get($run->run_id);

        $this->assertSame($run->run_id, $fetched->run_id);
    }

    public function testJobsReturnsJobsForFirstRunFoundViaAnEvent(): void {
        $run = $this->firstRun();

        $result = $this->client()->runs()->jobs($run->run_id);

        $this->assertIsArray($result->items);
    }

    protected function firstRun(): FunctionRun {
        if (self::$first_run === null) {
            $this->markTestSkipped('No function runs were found for a recent event in this account.');
        }

        return self::$first_run;
    }
}
