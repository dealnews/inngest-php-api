<?php

namespace DealNews\InngestApi\Tests\Unit\Resource\V1;

use DealNews\InngestApi\Resource\V1\WebhooksResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class WebhooksResourceTest extends TestCase {

    /**
     * @return array<string, mixed>
     */
    protected function webhookPayload(): array {
        return [
            'id'         => 'wh-1',
            'name'       => 'Clerk',
            'url'        => 'https://inn.gs/e/abc123',
            'transform'  => 'function transform(evt) { return evt; }',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-02T00:00:00Z',
        ];
    }

    public function testListReturnsWebhooks(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode(['data' => [$this->webhookPayload()]])),
        ]);

        $result = (new WebhooksResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('wh-1', $result->items[0]->id);
        $this->assertSame('Clerk', $result->items[0]->name);
    }

    public function testGetReturnsWebhook(): void {
        $http = MockApi::v1Client([
            new Response(200, [], json_encode(['data' => $this->webhookPayload()])),
        ]);

        $webhook = (new WebhooksResource($http))->get('wh-1');

        $this->assertSame('wh-1', $webhook->id);
        $this->assertInstanceOf(\DateTimeImmutable::class, $webhook->created_at);
    }

    public function testCreateSendsNameAndTransform(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => $this->webhookPayload()])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        $webhook = (new WebhooksResource($http))->create('Clerk', 'function transform(evt) { return evt; }');

        $this->assertSame('wh-1', $webhook->id);

        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/webhooks', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['name' => 'Clerk', 'transform' => 'function transform(evt) { return evt; }'], $body);
    }

    public function testUpdateSendsPutRequest(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => $this->webhookPayload()])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new WebhooksResource($http))->update('wh-1', name: 'Clerk v2');

        $request = $history[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v1/webhooks/wh-1', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['name' => 'Clerk v2'], $body);
    }

    public function testDeleteSendsDeleteRequest(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => ['id' => 'wh-1']])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new \DealNews\InngestApi\HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        (new WebhooksResource($http))->delete('wh-1');

        $request = $history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/v1/webhooks/wh-1', $request->getUri()->getPath());
    }
}
