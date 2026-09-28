<?php

namespace DealNews\InngestApi\Tests\Unit;

use DealNews\InngestApi\Exception\AuthenticationException;
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Exception\RateLimitException;
use DealNews\InngestApi\Exception\ServerException;
use DealNews\InngestApi\Exception\ValidationException;
use DealNews\InngestApi\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase {

    public function testRequestSendsBearerAuthAndEnvironmentHeader(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => ['id' => 'acct-1']])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('secret-key', 'https://api.inngest.com/v2', 'branch-env', new Client(['handler' => $stack]));
        $http->request('GET', '/account');

        $request = $history[0]['request'];

        $this->assertSame('Bearer secret-key', $request->getHeaderLine('Authorization'));
        $this->assertSame('branch-env', $request->getHeaderLine('X-Inngest-Env'));
        $this->assertSame('https://api.inngest.com/v2/account', (string) $request->getUri());
    }

    public function testPerRequestHeaderOverridesDefaultEnvironment(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', 'default-env', new Client(['handler' => $stack]));
        $http->request('GET', '/env/webhooks', headers: ['X-Inngest-Env' => 'other-env']);

        $this->assertSame('other-env', $history[0]['request']->getHeaderLine('X-Inngest-Env'));
    }

    public function testEmptyJsonBodyIsSentAsAnObjectNotAnArray(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $http->request('POST', '/runs/run-1/cancel', json: []);

        $this->assertSame('{}', (string) $history[0]['request']->getBody());
    }

    public function testArrayQueryValuesAreSentAsRepeatedParamsNotBracketedIndices(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $http->request('GET', '/runs', query: ['status' => ['COMPLETED', 'FAILED']]);

        $this->assertSame('status=COMPLETED&status=FAILED', $history[0]['request']->getUri()->getQuery());
    }

    public function testBooleanQueryValuesAreSentAsLiteralTrueFalseStrings(): void {
        $history = [];
        $stack   = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['data' => []])),
        ]));
        $stack->push(Middleware::history($history));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $http->request('GET', '/apps', query: ['archived' => true, 'includeOutput' => false]);

        $this->assertSame('archived=true&includeOutput=false', $history[0]['request']->getUri()->getQuery());
    }

    public function testExtraSuccessStatusDoesNotThrow(): void {
        $stack = HandlerStack::create(new MockHandler([
            new Response(422, [], json_encode(['data' => ['ok' => false]])),
        ]));

        $http     = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));
        $response = $http->request('POST', '/apps/my-app/syncs', extra_success_statuses: [422]);

        $this->assertSame(['ok' => false], $response['data']);
    }

    #[DataProvider('errorStatusProvider')]
    public function testErrorStatusesAreMappedToExceptions(int $status, string $expected_class): void {
        $stack = HandlerStack::create(new MockHandler([
            new Response($status, [], json_encode([
                'errors' => [['code' => 'boom', 'message' => 'Something broke']],
            ])),
        ]));

        $http = new HttpClient('key', 'https://api.inngest.com/v2', null, new Client(['handler' => $stack]));

        $this->expectException($expected_class);
        $this->expectExceptionMessage('Something broke');

        $http->request('GET', '/account');
    }

    /**
     * @return array<string, array{0: int, 1: class-string}>
     */
    public static function errorStatusProvider(): array {
        return [
            'unauthorized' => [401, AuthenticationException::class],
            'not found'    => [404, NotFoundException::class],
            'validation'   => [400, ValidationException::class],
            'rate limited' => [429, RateLimitException::class],
            'server error' => [500, ServerException::class],
        ];
    }

    public function testV1StyleErrorShapeIsMappedToException(): void {
        $stack = HandlerStack::create(new MockHandler([
            new Response(404, [], json_encode([
                'error'  => 'Unable to load function run: 01HE8AM9DPK9N37V1RKY1DNQF5',
                'data'   => null,
                'status' => 404,
            ])),
        ]));

        $http = new HttpClient('key', 'https://api.inngest.com', null, new Client(['handler' => $stack]));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Unable to load function run: 01HE8AM9DPK9N37V1RKY1DNQF5');

        $http->request('GET', '/v1/runs/01HE8AM9DPK9N37V1RKY1DNQF5');
    }
}
