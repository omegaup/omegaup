<?php

namespace OmegaUp;

/**
 * Helper class for NewRelic integration with error handling
 */
class NewRelicHelper {
    /**
     * Report an error to NewRelic if available
     */
    public static function noticeError($messageOrException): bool {
        if (!self::isAvailable()) {
            return false;
        }

        $result = newrelic_notice_error($messageOrException);
        return !is_null($result) ? boolval($result) : true;
    }

    /**
     * Name a transaction in NewRelic if available
     */
    public static function nameTransaction(string $name): void {
        if (
            extension_loaded(
                'newrelic'
            ) && function_exists(
                'newrelic_name_transaction'
            )
        ) {
            newrelic_name_transaction($name);
        }
    }

    /**
     * Add custom attribute to NewRelic if available
     */
    public static function addCustomAttribute(
        string $key,
        string $value
    ): void {
        if (
            extension_loaded(
                'newrelic'
            ) && function_exists(
                'newrelic_add_custom_attribute'
            )
        ) {
            newrelic_add_custom_attribute($key, $value);
        }
    }

    /**
     * Check if NewRelic is properly loaded and functional
     */
    public static function isAvailable(): bool {
        return extension_loaded('newrelic')
            && function_exists('newrelic_notice_error')
            && function_exists('newrelic_name_transaction');
    }

    /**
     * Get NewRelic status for debugging
     */
    public static function getStatus(): array {
        return [
            'extension_loaded' => extension_loaded('newrelic'),
            'notice_error_exists' => function_exists('newrelic_notice_error'),
            'name_transaction_exists' => function_exists(
                'newrelic_name_transaction'
            ),
            'add_custom_attribute_exists' => function_exists(
                'newrelic_add_custom_attribute'
            ),
        ];
    }
}
