<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Resource\KeysResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class KeysResourceTest extends TestCase {

    public function testListEventKeysReturnsKeys(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'key-1', 'name' => 'default', 'key' => 'evt-secret', 'environment' => 'production'],
                ],
                'page' => ['cursor' => null, 'hasMore' => false, 'limit' => 20],
            ])),
        ]);

        $result = (new KeysResource($http))->listEventKeys();

        $this->assertCount(1, $result->items);
        $this->assertSame('evt-secret', $result->items[0]->key);
        $this->assertFalse($result->page->has_more);
    }

    public function testListSigningKeysReturnsKeys(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'key-2', 'name' => 'default', 'key' => 'signkey-secret', 'environment' => 'production'],
                ],
            ])),
        ]);

        $result = (new KeysResource($http))->listSigningKeys();

        $this->assertSame('signkey-secret', $result->items[0]->key);
    }
}
