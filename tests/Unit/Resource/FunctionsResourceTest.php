<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Functions\FunctionConcurrencyScope;
use DealNews\InngestApi\Model\Functions\FunctionTriggerType;
use DealNews\InngestApi\Resource\FunctionsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FunctionsResourceTest extends TestCase {

    public function testListReturnsPaginatedFunctions(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'            => 'send-welcome-email',
                        'slug'          => 'send-welcome-email',
                        'name'          => 'Send welcome email',
                        'app'           => ['id' => 'production-app'],
                        'isArchived'    => false,
                        'isPaused'      => false,
                        'triggers'      => [
                            ['type' => 'EVENT', 'value' => 'user.created'],
                        ],
                        'configuration' => [
                            'retries'     => ['isDefault' => true, 'value' => 3],
                            'concurrency' => [
                                ['key' => 'account', 'limit' => ['value' => 5], 'scope' => 'ACCOUNT'],
                            ],
                        ],
                    ],
                ],
                'page' => ['cursor' => null, 'hasMore' => false, 'limit' => 20],
            ])),
        ]);

        $result = (new FunctionsResource($http))->list('production-app');

        $this->assertCount(1, $result->items);
        $function = $result->items[0];
        $this->assertSame('send-welcome-email', $function->id);
        $this->assertSame('production-app', $function->app->id);
        $this->assertSame(FunctionTriggerType::Event, $function->triggers[0]->type);
        $this->assertSame('user.created', $function->triggers[0]->value);
        $this->assertTrue($function->configuration->retries->is_default);
        $this->assertSame(FunctionConcurrencyScope::Account, $function->configuration->concurrency[0]->scope);
        $this->assertSame(5, $function->configuration->concurrency[0]->limit->value);
        $this->assertFalse($result->page->has_more);
    }

    public function testGetReturnsFunction(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'   => 'send-welcome-email',
                    'slug' => 'send-welcome-email',
                    'name' => 'Send welcome email',
                ],
            ])),
        ]);

        $function = (new FunctionsResource($http))->get('production-app', 'send-welcome-email');

        $this->assertSame('send-welcome-email', $function->id);
    }

    public function testInvokeReturnsResult(): void {
        $http = MockApi::client([
            new Response(201, [], json_encode([
                'data' => [
                    'runId'       => '01hp1zx8m3ng9vp6qn0xk7j4cy',
                    'queuedAt'    => '2024-01-10T09:15:00Z',
                    'startedAt'   => '2024-01-10T09:15:01Z',
                    'completedAt' => '2024-01-10T09:15:02Z',
                    'result'      => '{"success": true}',
                ],
            ])),
        ]);

        $result = (new FunctionsResource($http))->invoke(
            'production-app',
            'send-welcome-email',
            data: ['message' => 'Hello, World!'],
            idempotency_key: 'user-action-123',
        );

        $this->assertSame('01hp1zx8m3ng9vp6qn0xk7j4cy', $result->run_id);
        $this->assertSame('{"success": true}', $result->result);
        $this->assertNull($result->error);
        $this->assertSame('2024-01-10T09:15:02+00:00', $result->completed_at?->format('c'));
    }
}
