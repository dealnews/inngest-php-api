<?php

namespace DealNews\InngestApi\Tests\Functional\V1;

use DealNews\InngestApi\Exception\AuthenticationException;
use DealNews\InngestApi\Exception\AuthorizationException;
use DealNews\InngestApi\Exception\NotFoundException;
use DealNews\InngestApi\Exception\RateLimitException;
use DealNews\InngestApi\V1Client;
use PHPUnit\Framework\TestCase;

/**
 * Base class for v1 functional tests that call the real Inngest API.
 * These are excluded from the default test suite; run them explicitly
 * with:
 *
 *   vendor/bin/phpunit --testsuite functional
 *
 * They require tests/config.ini (gitignored) with:
 *
 *   inngest.status.signing_key = signkey-...
 *
 * v1 only accepts an environment's signing key, not a dashboard API key
 * (see DealNews\InngestApi\V1Client). Only read-only (list/get) endpoints
 * are exercised here; nothing that creates, updates, or otherwise
 * mutates account state runs in this suite.
 */
abstract class V1FunctionalTestCase extends TestCase {

    protected static ?V1Client $client = null;

    public static function setUpBeforeClass(): void {
        self::$client = self::buildClient();
    }

    protected function client(): V1Client {
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

    protected static function buildClient(): V1Client {
        $config_file = __DIR__ . '/../../config.ini';

        if (!is_file($config_file)) {
            self::markTestSkipped('v1 functional tests require tests/config.ini with an inngest.status.signing_key value.');
        }

        $config       = parse_ini_file($config_file);
        $signing_key  = $config['inngest.status.signing_key'] ?? '';

        if (empty($signing_key)) {
            self::markTestSkipped('tests/config.ini is missing inngest.status.signing_key.');
        }

        return new V1Client($signing_key);
    }
}
