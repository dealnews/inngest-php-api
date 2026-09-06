<?php

namespace DealNews\InngestApi\Support;

/**
 * Parses the RFC 3339 date-time strings returned by the Inngest API.
 */
class DateTimeConverter {

    /**
     * Parses a date-time string, returning null for empty or unparsable
     * values instead of throwing.
     */
    public static function parse(?string $value): ?\DateTimeImmutable {
        $return = null;

        if (!empty($value)) {
            try {
                $return = new \DateTimeImmutable($value);
            } catch (\Exception $e) {
                $return = null;
            }
        }

        return $return;
    }
}
