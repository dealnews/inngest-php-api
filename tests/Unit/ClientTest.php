<?php

namespace DealNews\InngestApi\Tests\Unit;

use DealNews\InngestApi\Client;
use DealNews\InngestApi\Resource\AccountResource;
use DealNews\InngestApi\Resource\AppsResource;
use DealNews\InngestApi\Resource\EnvironmentsResource;
use DealNews\InngestApi\Resource\EventsResource;
use DealNews\InngestApi\Resource\ExperimentsResource;
use DealNews\InngestApi\Resource\FunctionsResource;
use DealNews\InngestApi\Resource\InsightsResource;
use DealNews\InngestApi\Resource\KeysResource;
use DealNews\InngestApi\Resource\PartnerAccountsResource;
use DealNews\InngestApi\Resource\RunsResource;
use DealNews\InngestApi\Resource\SandboxesResource;
use DealNews\InngestApi\Resource\SandboxProcessesResource;
use DealNews\InngestApi\Resource\SessionsResource;
use DealNews\InngestApi\Resource\WebhooksResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {

    #[DataProvider('resourceGetterProvider')]
    public function testResourceGettersReturnSameInstance(string $method, string $expected_class): void {
        $client = new Client('test-key');

        $this->assertInstanceOf($expected_class, $client->{$method}());
        $this->assertSame($client->{$method}(), $client->{$method}());
    }

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function resourceGetterProvider(): array {
        return [
            'account'          => ['account', AccountResource::class],
            'apps'             => ['apps', AppsResource::class],
            'environments'     => ['environments', EnvironmentsResource::class],
            'events'           => ['events', EventsResource::class],
            'experiments'      => ['experiments', ExperimentsResource::class],
            'functions'        => ['functions', FunctionsResource::class],
            'insights'         => ['insights', InsightsResource::class],
            'keys'             => ['keys', KeysResource::class],
            'partnerAccounts'  => ['partnerAccounts', PartnerAccountsResource::class],
            'runs'             => ['runs', RunsResource::class],
            'sandboxes'        => ['sandboxes', SandboxesResource::class],
            'sandboxProcesses' => ['sandboxProcesses', SandboxProcessesResource::class],
            'sessions'         => ['sessions', SessionsResource::class],
            'webhooks'         => ['webhooks', WebhooksResource::class],
        ];
    }
}
