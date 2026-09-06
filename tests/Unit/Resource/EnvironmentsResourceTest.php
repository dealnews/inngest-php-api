<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Environments\EnvType;
use DealNews\InngestApi\Resource\EnvironmentsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class EnvironmentsResourceTest extends TestCase {

    public function testListReturnsPaginatedEnvironments(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'          => 'env-1',
                        'name'        => 'main',
                        'type'        => 'BRANCH',
                        'isArchived'  => false,
                        'createdAt'   => '2024-01-01T00:00:00Z',
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 50,
                ],
            ])),
        ]);

        $result = (new EnvironmentsResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('env-1', $result->items[0]->id);
        $this->assertSame(EnvType::Branch, $result->items[0]->type);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
    }

    public function testCreateSendsIdAndName(): void {
        $http = MockApi::client([
            new Response(201, [], json_encode([
                'data' => ['id' => 'env-2', 'name' => 'staging', 'type' => 'BRANCH'],
            ])),
        ]);

        $env = (new EnvironmentsResource($http))->create('env-2', 'staging');

        $this->assertSame('env-2', $env->id);
        $this->assertSame('staging', $env->name);
    }

    public function testPatchArchivesEnvironment(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => ['id' => 'env-2', 'name' => 'staging', 'isArchived' => true],
            ])),
        ]);

        $env = (new EnvironmentsResource($http))->patch('env-2', true);

        $this->assertTrue($env->is_archived);
    }
}
