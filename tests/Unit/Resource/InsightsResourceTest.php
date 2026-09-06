<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Model\Insights\DiagnosticSeverity;
use DealNews\InngestApi\Model\Insights\OutputColumnType;
use DealNews\InngestApi\Resource\InsightsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class InsightsResourceTest extends TestCase {

    public function testListEventSchemasReturnsPaginatedSchemas(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'name'   => 'orders/payment.created',
                        'schema' => ['type' => 'object', 'properties' => ['amount' => ['type' => 'number']]],
                    ],
                ],
                'page' => [
                    'cursor'  => 'next-cursor',
                    'hasMore' => true,
                    'limit'   => 20,
                ],
            ])),
        ]);

        $result = (new InsightsResource($http))->listEventSchemas();

        $this->assertCount(1, $result->items);
        $this->assertSame('orders/payment.created', $result->items[0]->name);
        $this->assertSame(['type' => 'object', 'properties' => ['amount' => ['type' => 'number']]], $result->items[0]->schema);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
    }

    public function testQueryReturnsColumnsRowsAndDiagnostics(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'columns' => [
                        ['name' => 'function_id', 'type' => 'STRING'],
                        ['name' => 'total', 'type' => 'NUMBER'],
                    ],
                    'rows' => [
                        ['values' => ['my-fn', 42]],
                        ['values' => ['other-fn', 7]],
                    ],
                    'diagnostics' => [
                        [
                            'code'     => 'deprecated_column',
                            'message'  => 'Column `total` is deprecated',
                            'severity' => 'WARNING',
                            'position' => ['context' => 'SELECT total', 'start' => 7, 'end' => 12],
                        ],
                    ],
                ],
                'metadata' => [
                    'fetchedAt' => '2024-01-01T00:00:00Z',
                ],
            ])),
        ]);

        $result = (new InsightsResource($http))->query('SELECT function_id, total FROM function_runs');

        $this->assertCount(2, $result->columns);
        $this->assertSame('function_id', $result->columns[0]->name);
        $this->assertSame(OutputColumnType::Number, $result->columns[1]->type);

        $this->assertCount(2, $result->rows);
        $this->assertSame(['my-fn', 42], $result->rows[0]->values);
        $this->assertSame(['other-fn', 7], $result->rows[1]->values);

        $this->assertCount(1, $result->diagnostics);
        $this->assertSame('deprecated_column', $result->diagnostics[0]->code);
        $this->assertSame(DiagnosticSeverity::Warning, $result->diagnostics[0]->severity);
        $this->assertSame(7, $result->diagnostics[0]->position->start);
        $this->assertSame(12, $result->diagnostics[0]->position->end);
    }

    public function testGenerateQueryFromPromptReturnsSqlAndSummary(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'sql'     => 'SELECT function_id, COUNT(*) AS total FROM function_runs GROUP BY function_id',
                    'summary' => 'Counts runs per function.',
                ],
            ])),
        ]);

        $result = (new InsightsResource($http))->generateQueryFromPrompt('Count runs per function');

        $this->assertSame('SELECT function_id, COUNT(*) AS total FROM function_runs GROUP BY function_id', $result->sql);
        $this->assertSame('Counts runs per function.', $result->summary);
    }

    public function testListTablesReturnsTablesWithColumns(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'name'        => 'function_runs',
                        'description' => 'One row per function run',
                        'columns'     => [
                            ['name' => 'function_id', 'description' => 'The function ID', 'type' => 'String'],
                            ['name' => 'started_at', 'description' => 'Start time', 'type' => 'DateTime'],
                        ],
                    ],
                ],
            ])),
        ]);

        $result = (new InsightsResource($http))->listTables();

        $this->assertCount(1, $result->items);
        $this->assertSame('function_runs', $result->items[0]->name);
        $this->assertCount(2, $result->items[0]->columns);
        $this->assertSame('function_id', $result->items[0]->columns[0]->name);
        $this->assertSame('String', $result->items[0]->columns[0]->type);
    }
}
