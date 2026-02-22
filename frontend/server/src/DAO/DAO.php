<?php

namespace OmegaUp\DAO;

/** Table Data Access Object.
 *
 * Esta clase comprende metodos comunes para manejar transacciones.
 * @access private
 * @abstract
 */
final class DAO {
    final public static function transBegin(): void {
        \OmegaUp\MySQLConnection::getInstance()->StartTrans();
    }

    final public static function transEnd(): void {
        \OmegaUp\MySQLConnection::getInstance()->CompleteTrans();
    }

    final public static function transRollback(): void {
        \OmegaUp\MySQLConnection::getInstance()->FailTrans();
        \OmegaUp\MySQLConnection::getInstance()->CompleteTrans();
    }

    final public static function isDuplicateEntryException(\Exception $e): bool {
        if (!($e instanceof \OmegaUp\Exceptions\DatabaseOperationException)) {
            return false;
        }
        return $e->isDuplicate();
    }

    /**
     * Helper function to convert from internal timestamps to the format that
     * MySQL expects.
     *
     * @param \OmegaUp\Timestamp|string|int|null $timestamp the POSIX timestamp.
     * @return string|null the timestamp in MySQL format.
     */
    final public static function toMySQLTimestamp($timestamp): ?string {
        if ($timestamp === null) {
            return null;
        }
        // Temporary migration code to allow the timestamps to be in either
        // format.
        if (is_string($timestamp)) {
            return $timestamp;
        }
        if ($timestamp instanceof \OmegaUp\Timestamp) {
            $timestamp = $timestamp->time;
        }
        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Helper function to convert from MySQL timestamps to the internal POSIX
     * timestamp format.
     *
     * @template T as string|int|float|\OmegaUp\Timestamp|null
     * @param T $timestamp the MySQL timestamp.
     * @return (T is null ? null : \OmegaUp\Timestamp) the POSIX timestamp.
     */
    final public static function fromMySQLTimestamp($timestamp): ?\OmegaUp\Timestamp {
        // Temporary migration code to allow the timestamps to be in either
        // format.
        if ($timestamp instanceof \OmegaUp\Timestamp) {
            return $timestamp;
        }
        if (is_int($timestamp)) {
            return new \OmegaUp\Timestamp($timestamp);
        }
        if (is_float($timestamp)) {
            return new \OmegaUp\Timestamp(intval($timestamp));
        }
        if (is_string($timestamp)) {
            return new \OmegaUp\Timestamp(strtotime($timestamp));
        }
        return null;
    }

    /**
     * @param $fieldNames array<string, bool>
     */
    public static function getFields(
        array $fieldNames,
        string $prefix
    ): string {
        return join(
            ', ',
            array_map(
                fn ($field) => "`{$prefix}`.`{$field}`",
                array_keys($fieldNames)
            )
        );
    }
}
