<?php

namespace OmegaUp\DAO\Enum;

/**
 * Base class for the ActiveStatus and RecommendedStatus enums below.
 *
 * It handles validation of input values, constants by name or by value,
 * and getting the corresponding SQL snippet for them.
 */
class StatusBase {
    /**
     * @param int|string $status Numeric or named constant.
     * @return null|int value on success, null otherwise.
     */
    public static function getIntValue($status): ?int {
        $cache = self::getConstCache(get_called_class());
        if (is_numeric($status)) {
            // $status may be a string, force it to an int.
            $status = intval($status);
            if ($cache['min'] <= $status && $status <= $cache['max']) {
                return $status;
            }
            return null;
        }
        if (in_array($status, $cache['constants'])) {
            return $cache['constants'][$status];
        }
        return null;
    }

    /**
     * @param int $status
     * @return string SQL snippet.
     */
    public static function sql(int $status): string {
        $class = get_called_class();
        $cache = self::getConstCache($class);
        // This should've been validated before, but lets be paranoid anyway.
        $status = max($cache['min'], min($cache['max'], $status));
        /**
         * @var string
         * @psalm-suppress MixedArrayAccess I swear $class:SQL_FOR_STATUS is a string[].
         */
        return $class::SQL_FOR_STATUS[$status];
    }

    /**
     * @return list<int>|list<string>
     */
    public static function getAll(): array {
        $reflectionClass = new \ReflectionClass(get_called_class());
        $constants = $reflectionClass->getConstants();

        return array_values($constants);
    }

    /**
     * @param class-string $className The derived class name.
     * @return array{constants: array<string, int>, min: int, max: int}
     */
    private static function getConstCache(string $className): array {
        if (!isset(self::$_constCache[$className])) {
            $reflection = new \ReflectionClass($className);
            /** @var array<string, int> */
            $constants = $reflection->getConstants();
            $values = array_values($constants);
            $min = 0;
            $max = 0;
            if (!empty($values)) {
                $max = max($values);
                $min = min($values);
            }
            self::$_constCache[$className] = [
                'constants' => $constants,
                'min' => $min,
                'max' => $max,
            ];
        }
        return self::$_constCache[$className];
    }

    /** @var array<string, array{constants: array<string, int>, min: int, max: int}> */
    private static $_constCache = [];
}
