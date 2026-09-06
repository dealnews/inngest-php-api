<?php

namespace DealNews\InngestApi\Model\Insights;

/**
 * The result of executing an Insights SQL query: the result's column
 * metadata, its rows of data, and any non-fatal diagnostics raised along
 * the way.
 */
class QueryResult {

    /**
     * @param OutputColumn[] $columns
     * @param Row[] $rows
     * @param Diagnostic[] $diagnostics
     */
    public function __construct(
        public readonly array $columns = [],
        public readonly array $rows = [],
        public readonly array $diagnostics = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self {
        return new self(
            columns: array_map(
                static fn (array $column) => OutputColumn::fromArray($column),
                $data['columns'] ?? [],
            ),
            rows: array_map(
                static fn (array $row) => Row::fromArray($row),
                $data['rows'] ?? [],
            ),
            diagnostics: array_map(
                static fn (array $diagnostic) => Diagnostic::fromArray($diagnostic),
                $data['diagnostics'] ?? [],
            ),
        );
    }
}
