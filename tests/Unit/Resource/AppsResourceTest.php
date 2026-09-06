<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Apps\AppMethod;
use DealNews\InngestApi\Model\Apps\SyncStatus;
use DealNews\InngestApi\Resource\AppsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AppsResourceTest extends TestCase {

    public function testListReturnsPaginatedApps(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'            => 'my-app',
                        'name'          => 'my-app',
                        'functionCount' => 2,
                        'isArchived'    => false,
                        'method'        => 'SERVE',
                        'createdAt'     => '2024-01-10T09:15:00Z',
                        'latestSync'    => [
                            'sdkLanguage' => 'typescript',
                            'sdkVersion'  => '3.22.0',
                            'url'         => 'https://example.com/api/inngest',
                            'status'      => 'success',
                        ],
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 20,
                ],
            ])),
        ]);

        $result = (new AppsResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('my-app', $result->items[0]->id);
        $this->assertSame(2, $result->items[0]->function_count);
        $this->assertSame(AppMethod::Serve, $result->items[0]->method);
        $this->assertSame(SyncStatus::Success, $result->items[0]->latest_sync->status);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
    }

    public function testGetReturnsApp(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'            => 'my-app',
                    'name'          => 'my-app',
                    'functionCount' => 2,
                    'isArchived'    => false,
                    'method'        => 'SERVE',
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-20T14:22:33Z',
                ],
            ])),
        ]);

        $app = (new AppsResource($http))->get('my-app');

        $this->assertSame('my-app', $app->id);
        $this->assertFalse($app->is_archived);
    }

    public function testSyncReturnsResultOnSuccess(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'     => 'sync-1',
                    'appId'  => 'my-app',
                    'status' => 'success',
                ],
            ])),
        ]);

        $result = (new AppsResource($http))->sync('my-app', 'https://example.com/api/inngest');

        $this->assertSame('sync-1', $result->id);
        $this->assertSame(SyncStatus::Success, $result->status);
        $this->assertNull($result->error);
    }

    public function testSyncReturns422AsSuccessfulResultWithError(): void {
        $http = MockApi::client([
            new Response(422, [], json_encode([
                'data' => [
                    'id'     => 'sync-2',
                    'appId'  => 'my-app',
                    'status' => 'error',
                    'error'  => [
                        'code'    => 'connection_refused',
                        'message' => 'Could not connect to the app URL',
                    ],
                ],
            ])),
        ]);

        $result = (new AppsResource($http))->sync('my-app', 'https://example.com/api/inngest');

        $this->assertSame(SyncStatus::Error, $result->status);
        $this->assertSame('connection_refused', $result->error->code);
        $this->assertSame('Could not connect to the app URL', $result->error->message);
    }
}
