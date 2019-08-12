<?php

/** Table Data Access Object.
 *
 * Esta clase comprende metodos comunes para manejar transacciones.
 * @access private
 * @abstract
 */
final class DAO {
    final public static function transBegin() : void {
        global $conn;
        $conn->StartTrans();
    }

    final public static function transEnd() : void {
        global $conn;
        $conn->CompleteTrans();
    }

    final public static function transRollback() : void {
        global $conn;
        $conn->FailTrans();
        $conn->CompleteTrans();
    }

    final public static function isDuplicateEntryException(Exception $e) : bool {
        if (!($e instanceof DatabaseOperationException)) {
            return false;
        }
        return $e->isDuplicate();
    }

    /**
     * Helper function to convert from internal timestamps to the format that
     * MySQL expects.
     *
     * @param string|int $timestamp the POSIX timestamp.
     * @return int the timestamp in MySQL format.
     */
    final public static function toMySQLTimestamp($timestamp) : ?string {
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
     * @param string|int $timestamp the MySQL timestamp.
     * @return int the POSIX timestamp.
     */
    final public static function fromMySQLTimestamp($timestamp) : ?int {
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

/** Value Object.
 *
 * Esta clase abstracta comprende metodos comunes para todas los objetos VO
 * @access private
 * @package docs
 *
 */
abstract class VO {
    function asArray() : array {
        return get_object_vars($this);
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() : string {
        return json_encode($this->asArray());
    }

    public function asFilteredArray(iterable $filters) : array {
        // Get the complete representation of the array
        $completeArray = get_object_vars($this);
        // Declare an empty array to return
        $returnArray = [];
        foreach ($filters as $filter) {
            // Only return properties included in $filters array
            if (isset($completeArray[$filter])) {
                $returnArray[$filter] = $completeArray[$filter];
            } else {
                $returnArray[$filter] = null;
            }
        }
        return $returnArray;
    }
}
