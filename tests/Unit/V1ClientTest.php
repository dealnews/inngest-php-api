<?php

namespace DealNews\InngestApi\Tests\Unit;

use DealNews\InngestApi\Resource\V1\CancellationsResource;
use DealNews\InngestApi\Resource\V1\EventsResource;
use DealNews\InngestApi\Resource\V1\RunsResource;
use DealNews\InngestApi\Resource\V1\SignalsResource;
use DealNews\InngestApi\Resource\V1\WebhooksResource;
use DealNews\InngestApi\V1Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class V1ClientTest extends TestCase {

    #[DataProvider('resourceGetterProvider')]
    public function testResourceGettersReturnSameInstance(string $method, string $expected_class): void {
        $client = new V1Client('test-key');

        $this->assertInstanceOf($expected_class, $client->{$method}());
        $this->assertSame($client->{$method}(), $client->{$method}());
    }

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function resourceGetterProvider(): array {
        return [
            'cancellations' => ['cancellations', CancellationsResource::class],
            'events'        => ['events', EventsResource::class],
            'runs'          => ['runs', RunsResource::class],
            'signals'       => ['signals', SignalsResource::class],
            'webhooks'      => ['webhooks', WebhooksResource::class],
        ];
    }
}
