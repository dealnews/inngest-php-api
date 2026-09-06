<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Functions\FunctionDefinition;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class FunctionsResourceFunctionalTest extends FunctionalTestCase {

    protected static ?string $first_app_id = null;

    /**
     * @var FunctionDefinition[]|null
     */
    protected static ?array $first_function = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $apps = self::$client->apps()->list(limit: 1)->items;

        if ($apps !== []) {
            self::$first_app_id   = $apps[0]->id;
            self::$first_function = self::$client->functions()->list($apps[0]->id, limit: 1)->items;
        }
    }

    public function testListReturnsFunctionsForFirstApp(): void {
        if (self::$first_app_id === null) {
            $this->markTestSkipped('No apps exist in this account.');
        }

        $result = $this->client()->functions()->list(self::$first_app_id, limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $function) {
            $this->assertInstanceOf(FunctionDefinition::class, $function);
        }
    }

    public function testGetReturnsFirstListedFunction(): void {
        if (self::$first_function === null || self::$first_function === []) {
            $this->markTestSkipped('No functions exist for this app.');
        }

        $function = $this->client()->functions()->get(self::$first_app_id, self::$first_function[0]->id);

        $this->assertSame(self::$first_function[0]->id, $function->id);
    }
}
