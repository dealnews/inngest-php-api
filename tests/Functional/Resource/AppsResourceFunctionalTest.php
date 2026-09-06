<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Apps\App;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class AppsResourceFunctionalTest extends FunctionalTestCase {

    /**
     * @var App[]|null
     */
    protected static ?array $first_app = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        self::$first_app = self::$client->apps()->list(limit: 1)->items;
    }

    public function testListReturnsApps(): void {
        $result = $this->client()->apps()->list(limit: 5);

        $this->assertIsArray($result->items);

        foreach ($result->items as $app) {
            $this->assertInstanceOf(App::class, $app);
        }
    }

    public function testGetReturnsFirstListedApp(): void {
        if (self::$first_app === []) {
            $this->markTestSkipped('No apps exist in this account.');
        }

        $app = $this->client()->apps()->get(self::$first_app[0]->id);

        $this->assertSame(self::$first_app[0]->id, $app->id);
    }
}
