<?php

namespace DealNews\InngestApi\Tests\Functional\V1\Resource;

use DealNews\InngestApi\Model\V1\Events\Event;
use DealNews\InngestApi\Tests\Functional\V1\V1FunctionalTestCase;

class EventsResourceFunctionalTest extends V1FunctionalTestCase {

    protected static ?Event $first_event = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        self::skipIfUnavailable(function () {
            $events = self::$client->events()->list(limit: 1)->items;

            self::$first_event = $events[0] ?? null;
        });
    }

    public function testListReturnsEvents(): void {
        $result = $this->client()->events()->list(limit: 5);

        foreach ($result->items as $event) {
            $this->assertInstanceOf(Event::class, $event);
        }
    }

    public function testGetReturnsFirstListedEvent(): void {
        $event = $this->firstEvent();

        $fetched = $this->client()->events()->get($event->internal_id);

        $this->assertSame($event->internal_id, $fetched->internal_id);
    }

    public function testListRunsReturnsRunsForFirstListedEvent(): void {
        $event = $this->firstEvent();

        $result = $this->client()->events()->listRuns($event->internal_id);

        $this->assertIsArray($result->items);
    }

    protected function firstEvent(): Event {
        if (self::$first_event === null) {
            $this->markTestSkipped('No events exist in this account.');
        }

        return self::$first_event;
    }
}
