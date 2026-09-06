<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Insights\EventSchema;
use DealNews\InngestApi\Model\Insights\InsightsTable;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

/**
 * Only the list endpoints are exercised here. query() and
 * generateQueryFromPrompt() are POST endpoints that run an arbitrary
 * query (and, for the prompt variant, an LLM call) rather than list/get
 * a resource, so they're intentionally left out of this suite.
 */
class InsightsResourceFunctionalTest extends FunctionalTestCase {

    public function testListEventSchemasReturnsSchemas(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->insights()->listEventSchemas(limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $schema) {
            $this->assertInstanceOf(EventSchema::class, $schema);
        }
    }

    public function testListTablesReturnsTables(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->insights()->listTables(),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $table) {
            $this->assertInstanceOf(InsightsTable::class, $table);
        }
    }
}
