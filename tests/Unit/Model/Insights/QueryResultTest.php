<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Insights;

use DealNews\InngestApi\Model\Insights\OutputColumnType;
use DealNews\InngestApi\Model\Insights\QueryResult;
use PHPUnit\Framework\TestCase;

class QueryResultTest extends TestCase {

    public function testFromArrayMapsColumnsRowsAndDiagnostics(): void {
        $result = QueryResult::fromArray([
            'columns' => [
                ['name' => 'name', 'type' => 'STRING'],
            ],
            'rows' => [
                ['values' => ['my-fn']],
            ],
            'diagnostics' => [
                ['code' => 'note', 'message' => 'informational', 'severity' => 'INFO'],
            ],
        ]);

        $this->assertCount(1, $result->columns);
        $this->assertSame('name', $result->columns[0]->name);
        $this->assertSame(OutputColumnType::String, $result->columns[0]->type);

        $this->assertCount(1, $result->rows);
        $this->assertSame(['my-fn'], $result->rows[0]->values);

        $this->assertCount(1, $result->diagnostics);
        $this->assertSame('note', $result->diagnostics[0]->code);
    }

    public function testFromArrayDefaultsMissingKeysToEmptyArrays(): void {
        $result = QueryResult::fromArray([]);

        $this->assertSame([], $result->columns);
        $this->assertSame([], $result->rows);
        $this->assertSame([], $result->diagnostics);
    }
}
