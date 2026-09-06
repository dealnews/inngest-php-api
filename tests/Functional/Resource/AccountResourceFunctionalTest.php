<?php

namespace DealNews\InngestApi\Tests\Functional\Resource;

use DealNews\InngestApi\Model\Account\Account;
use DealNews\InngestApi\Tests\Functional\FunctionalTestCase;

class AccountResourceFunctionalTest extends FunctionalTestCase {

    public function testGetReturnsTheAuthenticatedAccount(): void {
        $account = $this->client()->account()->get();

        $this->assertInstanceOf(Account::class, $account);
        $this->assertNotEmpty($account->id);
    }
}
