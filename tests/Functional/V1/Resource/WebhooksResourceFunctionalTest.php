<?php

namespace DealNews\InngestApi\Tests\Functional\V1\Resource;

use DealNews\InngestApi\Model\V1\Webhooks\Webhook;
use DealNews\InngestApi\Tests\Functional\V1\V1FunctionalTestCase;

class WebhooksResourceFunctionalTest extends V1FunctionalTestCase {

    protected static ?Webhook $first_webhook = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        self::skipIfUnavailable(function () {
            $webhooks = self::$client->webhooks()->list()->items;

            self::$first_webhook = $webhooks[0] ?? null;
        });
    }

    public function testListReturnsWebhooks(): void {
        $result = $this->client()->webhooks()->list();

        foreach ($result->items as $webhook) {
            $this->assertInstanceOf(Webhook::class, $webhook);
        }
    }

    public function testGetReturnsFirstListedWebhook(): void {
        if (self::$first_webhook === null) {
            $this->markTestSkipped('No webhooks exist in this account.');
        }

        $fetched = self::skipIfUnavailable(fn () => $this->client()->webhooks()->get(self::$first_webhook->id));

        $this->assertSame(self::$first_webhook->id, $fetched->id);
    }
}
