<?php

namespace DealNews\InngestApi\Resource;

use DealNews\InngestApi\Model\Account\Account;
use DealNews\InngestApi\Model\Page;
use DealNews\InngestApi\Model\PartnerAccounts\NewPartnerAccount;
use DealNews\InngestApi\Model\ResponseMetadata;
use DealNews\InngestApi\Pagination\PaginatedResult;

/**
 * Client for the /partner/accounts endpoints. Requires partner access.
 */
class PartnerAccountsResource extends AbstractResource {

    /**
     * Lists sub-accounts.
     */
    public function list(?string $cursor = null, int $limit = 20): PaginatedResult {
        $response = $this->http->request('GET', '/partner/accounts', query: [
            'cursor' => $cursor,
            'limit'  => $limit,
        ]);

        return new PaginatedResult(
            items:    array_map(static fn (array $account) => Account::fromArray($account), $response['data'] ?? []),
            page:     isset($response['page']) ? Page::fromArray($response['page']) : null,
            metadata: isset($response['metadata']) ? ResponseMetadata::fromArray($response['metadata']) : null,
        );
    }

    /**
     * Creates a sub-account.
     */
    public function create(string $name, string $email): NewPartnerAccount {
        $response = $this->http->request('POST', '/partner/accounts', json: [
            'name'  => $name,
            'email' => $email,
        ]);

        return NewPartnerAccount::fromArray($response['data'] ?? []);
    }
}
