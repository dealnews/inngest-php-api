<?php

namespace DealNews\InngestApi\Tests\Functional\V1\Resource;

use DealNews\InngestApi\Model\V1\Cancellations\Cancellation;
use DealNews\InngestApi\Tests\Functional\V1\V1FunctionalTestCase;

class CancellationsResourceFunctionalTest extends V1FunctionalTestCase {

    public function testListReturnsCancellations(): void {
        $result = $this->client()->cancellations()->list();

        foreach ($result->items as $cancellation) {
            $this->assertInstanceOf(Cancellation::class, $cancellation);
        }
    }
}
