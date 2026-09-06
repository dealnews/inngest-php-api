<?php

namespace DealNews\InngestApi\Tests\Functional;

use DealNews\InngestApi\Client;
use DealNews\InngestApi\Exception\AuthenticationException;
use DealNews\InngestApi\Exception\AuthorizationException;
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Exception\RateLimitException;
use PHPUnit\Framework\TestCase;

/**
 * Base class for functional tests that call the real Inngest API. These
 * are excluded from the default test suite; run them explicitly with:
 *
 *   vendor/bin/phpunit --testsuite functional
 *
 * They require tests/config.ini (gitignored) with:
 *
 *   inngest.status.api_key = sk-inn-api-...
 *
 * Only read-only (list/get) endpoints are exercised here; nothing that
 * creates, updates, or otherwise mutates account state runs in this
 * suite.
 */
abstract class FunctionalTestCase extends TestCase {

    protected static ?Client $client = null;

    public static function setUpBeforeClass(): void {
        self::$client = self::buildClient();
    }

    protected function client(): Client {
        return self::$client;
    }

    /**
     * Runs $callback and returns its result. If the API reports the
     * endpoint isn't usable right now for this account or key — access
     * not granted (401/403, e.g. a feature that isn't enabled on this
     * plan), the resource doesn't exist (404), or the call was rate
     * limited (429) — marks the test skipped instead of failing. Static
     * so it can also be called from setUpBeforeClass() fixtures.
     */
    protected static function skipIfUnavailable(callable $callback): mixed {
        try {
            return $callback();
        } catch (AuthenticationException|AuthorizationException|NotFoundException|RateLimitException $e) {
            self::markTestSkipped('Endpoint not available for this account: ' . $e->getMessage());
        }
    }

    protected static function buildClient(): Client {
        $config_file = __DIR__ . '/../config.ini';

        if (!is_file($config_file)) {
            self::markTestSkipped('Functional tests require tests/config.ini with an inngest.status.api_key value.');
        }

        $config  = parse_ini_file($config_file);
        $api_key = $config['inngest.status.api_key'] ?? '';

        if (empty($api_key)) {
            self::markTestSkipped('tests/config.ini is missing inngest.status.api_key.');
        }

        return new Client($api_key);
    }
}
