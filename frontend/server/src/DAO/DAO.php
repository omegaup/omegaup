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
     * @param string|int|null $timestamp the POSIX timestamp.
     * @return string|null the timestamp in MySQL format.
     */
    final public static function toMySQLTimestamp($timestamp): ?string {
        if (is_null($timestamp)) {
            return null;
        }
        // Temporary migration code to allow the timestamps to be in either
        // format.
        if (is_string($timestamp)) {
            return $timestamp;
        }
        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Helper function to convert from MySQL timestamps to the internal POSIX
     * timestamp format.
     *
     * @param string|int|float|null $timestamp the MySQL timestamp.
     * @return int|null the POSIX timestamp.
     */
    final public static function fromMySQLTimestamp($timestamp): ?int {
        if (is_null($timestamp)) {
            return null;
        }
        // Temporary migration code to allow the timestamps to be in either
        // format.
        if (is_int($timestamp)) {
            return $timestamp;
        }
        if (is_float($timestamp)) {
            return intval($timestamp);
        }
        return strtotime($timestamp);
    }
}
