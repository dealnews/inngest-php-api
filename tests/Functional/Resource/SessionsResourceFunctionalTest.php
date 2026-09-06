<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Sessions\SessionGroup;
use DealNews\InngestApi\Model\Sessions\SessionKey;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class SessionsResourceFunctionalTest extends FunctionalTestCase {

    protected static ?SessionKey $first_key = null;

    /**
     * @var SessionGroup[]|null
     */
    protected static ?array $first_group = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $keys = self::skipIfUnavailable(
            fn () => self::$client->sessions()->listKeys(limit: 1)->items,
        );

        self::$first_key = $keys[0] ?? null;

        if (self::$first_key !== null) {
            self::$first_group = self::skipIfUnavailable(
                fn () => self::$client->sessions()->list(self::$first_key->id, limit: 1)->items,
            );
        }
    }

    public function testListKeysReturnsSessionKeys(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->sessions()->listKeys(limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $key) {
            $this->assertInstanceOf(SessionKey::class, $key);
        }
    }

    public function testListReturnsSessionGroupsForFirstKey(): void {
        $key = $this->firstKey();

        $result = $this->skipIfUnavailable(
            fn () => $this->client()->sessions()->list($key->id, limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $group) {
            $this->assertInstanceOf(SessionGroup::class, $group);
        }
    }

    public function testListRunsReturnsRunsForFirstSession(): void {
        $key = $this->firstKey();

        if (self::$first_group === null || self::$first_group === []) {
            $this->markTestSkipped('No sessions exist under this session key.');
        }

        $result = $this->skipIfUnavailable(
            fn () => $this->client()->sessions()->listRuns($key->id, self::$first_group[0]->id, limit: 5),
        );

        $this->assertIsArray($result->items);
    }

    protected function firstKey(): SessionKey {
        if (self::$first_key === null) {
            $this->markTestSkipped('No session keys exist in this account.');
        }

        return self::$first_key;
    }
}
