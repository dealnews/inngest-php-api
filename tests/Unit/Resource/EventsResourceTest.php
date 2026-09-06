<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Resource\EventsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EventsResourceTest extends TestCase {

    public function testSendReturnsEventId(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => ['eventId' => '01H08W4TMBNKMEWFD0TYC532GG'],
            ])),
        ]);

        $sent = (new EventsResource($http))->send('user.signup', ['userId' => '123']);

        $this->assertSame('01H08W4TMBNKMEWFD0TYC532GG', $sent->event_id);
    }
}
