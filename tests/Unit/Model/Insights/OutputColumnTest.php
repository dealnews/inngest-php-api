<?php

namespace DealNews\InngestApi\Tests\Unit\Model\Insights;

use DealNews\InngestApi\Model\Insights\OutputColumn;
use DealNews\InngestApi\Model\Insights\OutputColumnType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OutputColumnTest extends TestCase {

    #[DataProvider('typeProvider')]
    public function testFromArrayMapsType(?string $raw, ?OutputColumnType $expected): void {
        $column = OutputColumn::fromArray(array_filter([
            'name' => 'total',
            'type' => $raw,
        ], static fn ($value) => $value !== null));

        $this->assertSame($expected, $column->type);
    }

    /**
     * @return array<string, array{0: ?string, 1: ?OutputColumnType}>
     */
    public static function typeProvider(): array {
        return [
            'string type'      => ['STRING', OutputColumnType::String],
            'number type'      => ['NUMBER', OutputColumnType::Number],
            'boolean type'     => ['BOOLEAN', OutputColumnType::Boolean],
            'datetime type'    => ['DATETIME', OutputColumnType::DateTime],
            'complex type'     => ['COMPLEX', OutputColumnType::Complex],
            'unspecified type' => ['VALUE_TYPE_UNSPECIFIED', OutputColumnType::Unspecified],
            'missing type'     => [null, null],
            'unknown type'     => ['SOMETHING_NEW', null],
        ];
    }
}
