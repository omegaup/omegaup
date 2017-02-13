<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** Table Data Access Object.
 *
 * Esta clase abstracta comprende metodos comunes para todas las clases DAO que mapean una tabla
 * @access private
 * @abstract
 */
abstract class DAO {
    protected static function log($m = null) {
        // Your logging call here.
    }

    public static function transBegin() {
        self::log('Iniciando transaccion');
        global $conn;
        $conn->StartTrans();
    }

    public static function transEnd() {
        self::log('Transaccion commit');
        global $conn;
        $conn->CompleteTrans();
    }

    public static function transRollback() {
        self::log('Transaccion rollback');
        global $conn;
        $conn->FailTrans();
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
