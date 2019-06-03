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
    }

    public static function isDuplicateEntryException(Exception $e) : bool {
        if (!($e instanceof ADODB_Exception)) {
            return false;
        }
        return $e->getCode() == 1062;
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
    function asArray() {
        return get_object_vars($this);
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode($this->asArray());
    }

    public function asFilteredArray($filters) {
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

    protected function toUnixTime(array $fields) {
        foreach ($fields as $f) {
            $this->$f = strtotime($this->$f);
        }
    }
}
