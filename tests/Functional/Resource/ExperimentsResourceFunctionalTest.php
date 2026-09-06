<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Experiments\Experiment;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class ExperimentsResourceFunctionalTest extends FunctionalTestCase {

    public function testListReturnsExperiments(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->experiments()->list(limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $experiment) {
            $this->assertInstanceOf(Experiment::class, $experiment);
        }
    }
}
