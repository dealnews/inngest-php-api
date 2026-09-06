<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Webhooks\Webhook;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class WebhooksResourceFunctionalTest extends FunctionalTestCase {

    public function testListReturnsWebhooksForTheProductionEnvironment(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->webhooks()->list('production', limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $webhook) {
            $this->assertInstanceOf(Webhook::class, $webhook);
        }
    }
}
