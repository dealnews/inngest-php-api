<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Environments\Env;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class EnvironmentsResourceFunctionalTest extends FunctionalTestCase {

    public function testListReturnsEnvironments(): void {
        $result = $this->client()->environments()->list(limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $env) {
            $this->assertInstanceOf(Env::class, $env);
        }
    }
}
