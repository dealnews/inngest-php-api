<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Resource\AccountResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AccountResourceTest extends TestCase {

    public function testGetReturnsAccount(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'        => 'acct-1',
                    'name'      => 'Current User',
                    'email'     => 'user@company.com',
                    'createdAt' => '2024-01-10T09:15:00Z',
                    'updatedAt' => '2024-01-20T12:00:00Z',
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-20T14:22:33Z',
                ],
            ])),
        ]);

        $account = (new AccountResource($http))->get();

        $this->assertSame('acct-1', $account->id);
        $this->assertSame('Current User', $account->name);
        $this->assertSame('user@company.com', $account->email);
        $this->assertSame('2024-01-10T09:15:00+00:00', $account->created_at?->format('c'));
        $this->assertSame('2024-01-20T12:00:00+00:00', $account->updated_at?->format('c'));
    }
}
