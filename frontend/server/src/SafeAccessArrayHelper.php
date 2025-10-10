<?php

namespace OmegaUp;

/**
 * Helper class for safe array access to prevent undefined key errors
 */
class SafeAccessArrayHelper {
    /**
     * Safely get array value with default fallback
     *
     * @template T
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @param T $default
     * @return mixed|T
     */
    public static function get(array $array, $key, $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Safely get string from array with default fallback
     *
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @param string $default
     * @return string
     */
    public static function getString(
        array $array,
        $key,
        string $default = ''
    ): string {
        $value = self::get($array, $key, $default);
        return is_string($value) ? $value : $default;
    }

    /**
     * Safely get integer from array with default fallback
     *
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @param int $default
     * @return int
     */
    public static function getInt(array $array, $key, int $default = 0): int {
        $value = self::get($array, $key, $default);
        return is_numeric($value) ? intval($value) : $default;
    }

    /**
     * Safely get float from array with default fallback
     *
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @param float $default
     * @return float
     */
    public static function getFloat(
        array $array,
        $key,
        float $default = 0.0
    ): float {
        $value = self::get($array, $key, $default);
        return is_numeric($value) ? floatval($value) : $default;
    }

    /**
     * Safely get boolean from array with default fallback
     *
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @param bool $default
     * @return bool
     */
    public static function getBool(
        array $array,
        $key,
        bool $default = false
    ): bool {
        $value = self::get($array, $key, $default);
        return is_bool($value) ? $value : boolval($value);
    }

    /**
     * Check if array has all required keys
     *
     * @param array<string|int, mixed> $array
     * @param array<string|int> $requiredKeys
     * @return bool
     */
    public static function hasKeys(array $array, array $requiredKeys): bool {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Safely access nested array values
     *
     * @template T
     * @param array<string|int, mixed> $array
     * @param array<string|int> $keys Path to the value (e.g., ['user', 'profile', 'score'])
     * @param T $default
     * @return mixed|T
     */
    public static function getPath(array $array, array $keys, $default = null) {
        $current = $array;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }
}
