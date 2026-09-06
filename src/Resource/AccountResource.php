<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Account\Account;

/**
 * Client for the /account endpoint.
 */
class AccountResource extends AbstractResource {

    /**
     * Returns the account for the authenticated user.
     */
    public function get(): Account {
        $response = $this->http->request('GET', '/account');

        return Account::fromArray($response['data'] ?? []);
    }
}
