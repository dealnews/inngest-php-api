<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Resource\PartnerAccountsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PartnerAccountsResourceTest extends TestCase {

    public function testListReturnsAccounts(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'sub-1', 'name' => 'Sub Account', 'email' => 'sub@example.com'],
                ],
            ])),
        ]);

        $result = (new PartnerAccountsResource($http))->list();

        $this->assertSame('sub-1', $result->items[0]->id);
    }

    public function testCreateReturnsAccountWithApiKey(): void {
        $http = MockApi::client([
            new Response(201, [], json_encode([
                'data' => [
                    'id'     => 'sub-2',
                    'name'   => 'New Sub',
                    'email'  => 'new@example.com',
                    'apiKey' => 'sk-inn-api-abc123',
                ],
            ])),
        ]);

        $account = (new PartnerAccountsResource($http))->create('New Sub', 'new@example.com');

        $this->assertSame('sk-inn-api-abc123', $account->api_key);
    }
}
