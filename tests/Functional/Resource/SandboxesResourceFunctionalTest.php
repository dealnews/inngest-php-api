<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Sandboxes\Sandbox;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class SandboxesResourceFunctionalTest extends FunctionalTestCase {

    /**
     * @var Sandbox[]|null
     */
    protected static ?array $first_sandbox = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        self::$first_sandbox = self::skipIfUnavailable(
            fn () => self::$client->sandboxes()->list(limit: 1)->items,
        );
    }

    public function testListReturnsSandboxes(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->sandboxes()->list(limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $sandbox) {
            $this->assertInstanceOf(Sandbox::class, $sandbox);
        }
    }

    public function testGetReturnsFirstListedSandbox(): void {
        if (self::$first_sandbox === []) {
            $this->markTestSkipped('No sandboxes exist in this account.');
        }

        $sandbox = $this->client()->sandboxes()->get(self::$first_sandbox[0]->id);

        $this->assertSame(self::$first_sandbox[0]->id, $sandbox->id);
    }
}
