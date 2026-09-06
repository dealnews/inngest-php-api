<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\HttpClient;
use DealNews\InngestApi\Model\Webhooks\EventFilter;
use DealNews\InngestApi\Model\Webhooks\FilterType;
use DealNews\InngestApi\Resource\WebhooksResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class WebhooksResourceTest extends TestCase {

    public function testListSendsEnvironmentHeader(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        (new WebhooksResource($http))->list('production');

        $this->assertSame('production', $history[0]['request']->getHeaderLine('X-Inngest-Env'));
    }

    public function testCreateReturnsWebhookWithEventFilter(): void {
        $http = MockApi::client([
            new Response(201, [], json_encode([
                'data' => [
                    'id'          => 'wh-1',
                    'name'        => 'Payment Processing Webhook',
                    'url'         => 'https://api.inngest.com/webhooks/wh-1',
                    'environment' => 'production',
                    'eventFilter' => ['events' => ['orders/payment.created'], 'filter' => 'ALLOW'],
                ],
            ])),
        ]);

        $webhook = (new WebhooksResource($http))->create(
            environment: 'production',
            name: 'Payment Processing Webhook',
            event_filter: new EventFilter(['orders/payment.created'], FilterType::Allow),
        );

        $this->assertSame('wh-1', $webhook->id);
        $this->assertSame(['orders/payment.created'], $webhook->event_filter->events);
        $this->assertSame(FilterType::Allow, $webhook->event_filter->filter);
    }
}
