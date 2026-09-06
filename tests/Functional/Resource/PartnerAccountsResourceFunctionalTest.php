<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Account\Account;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class PartnerAccountsResourceFunctionalTest extends FunctionalTestCase {

    public function testListReturnsPartnerAccounts(): void {
        $result = $this->skipIfUnavailable(
            fn () => $this->client()->partnerAccounts()->list(limit: 5),
        );

        $this->assertIsArray($result->items);

        foreach ($result->items as $account) {
            $this->assertInstanceOf(Account::class, $account);
        }
    }
}
