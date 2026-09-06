<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Keys\EventKey;
use DealNews\InngestApi\Model\Keys\SigningKey;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class KeysResourceFunctionalTest extends FunctionalTestCase {

    public function testListEventKeysReturnsKeys(): void {
        $result = $this->client()->keys()->listEventKeys(limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $key) {
            $this->assertInstanceOf(EventKey::class, $key);
        }
    }

    public function testListSigningKeysReturnsKeys(): void {
        $result = $this->client()->keys()->listSigningKeys(limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $key) {
            $this->assertInstanceOf(SigningKey::class, $key);
        }
    }
}
