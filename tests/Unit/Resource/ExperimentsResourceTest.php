<?php

namespace DealNews\InngestApi\Tests\Unit\Resource;

use DealNews\InngestApi\Resource\ExperimentsResource;
use DealNews\InngestApi\Tests\Support\MockApi;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ExperimentsResourceTest extends TestCase {

    public function testListForFunctionReturnsPaginatedExperiments(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'                => 'exp-1',
                        'function'          => ['id' => 'send-welcome-email', 'name' => 'Send welcome email', 'app' => ['id' => 'production-app']],
                        'selectionStrategy' => 'weighted',
                        'variants'          => ['control', 'treatment'],
                        'variantCount'      => 2,
                        'totalRuns'         => 100,
                        'firstSeen'         => '2024-01-01T00:00:00Z',
                        'lastSeen'          => '2024-01-05T00:00:00Z',
                    ],
                ],
                'page' => ['cursor' => 'next-cursor', 'hasMore' => true, 'limit' => 20],
            ])),
        ]);

        $result = (new ExperimentsResource($http))->listForFunction('production-app', 'send-welcome-email');

        $this->assertCount(1, $result->items);
        $experiment = $result->items[0];
        $this->assertSame('exp-1', $experiment->id);
        $this->assertSame('send-welcome-email', $experiment->function->id);
        $this->assertSame('production-app', $experiment->function->app->id);
        $this->assertSame(['control', 'treatment'], $experiment->variants);
        $this->assertSame(100, $experiment->total_runs);
        $this->assertTrue($result->page->has_more);
        $this->assertSame('next-cursor', $result->page->cursor);
    }

    public function testGetReturnsExperimentDetail(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    'id'                => 'exp-1',
                    'selectionStrategy' => 'weighted',
                    'variantWeights'    => [
                        ['variantName' => 'control', 'weight' => 0.5],
                        ['variantName' => 'treatment', 'weight' => 0.5],
                    ],
                    'variants' => [
                        [
                            'variantName' => 'control',
                            'runCount'    => 50,
                            'metrics'     => [
                                ['key' => 'latency_ms', 'min' => 10.0, 'max' => 100.0, 'avg' => 42.5],
                            ],
                        ],
                    ],
                ],
            ])),
        ]);

        $detail = (new ExperimentsResource($http))->get('production-app', 'send-welcome-email', 'exp-1');

        $this->assertSame('exp-1', $detail->id);
        $this->assertSame('control', $detail->variant_weights[0]->variant_name);
        $this->assertSame(0.5, $detail->variant_weights[0]->weight);
        $this->assertSame(50, $detail->variants[0]->run_count);
        $this->assertSame(42.5, $detail->variants[0]->metrics[0]->avg);
    }

    public function testListReturnsPaginatedExperimentsAcrossApps(): void {
        $http = MockApi::client([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id'       => 'exp-2',
                        'function' => ['id' => 'other-function'],
                    ],
                ],
                'page' => ['cursor' => null, 'hasMore' => false, 'limit' => 20],
            ])),
        ]);

        $result = (new ExperimentsResource($http))->list();

        $this->assertCount(1, $result->items);
        $this->assertSame('exp-2', $result->items[0]->id);
        $this->assertFalse($result->page->has_more);
    }
}
