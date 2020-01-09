<?php

namespace OmegaUp\DAO\VO;

/** Value Object.
 *
 * Esta clase abstracta comprende metodos comunes para todas los objetos VO
 * @access private
 * @package docs
 *
 */
abstract class VO {
    /**
     * Gets an associative array that is good for JSON marshaling.
     *
     * @return array<string, mixed>
     */
    public function asArray(): array {
        return get_object_vars($this);
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto en forma de cadena.  La
     * representacion de este objeto en cadena es la forma JSON (JavaScript
     * Object Notation) para este objeto.
     *
     * @return string
     */
    public function __toString(): string {
        return json_encode($this->asArray()) ?: '{}';
    }

    /**
     * Gets an associative array where the keys are present in $filters that is
     * good for JSON marshaling.
     *
     * @param string[] $filters
     * @return array<string, mixed>
     */
    public function asFilteredArray(iterable $filters): array {
        // Get the complete representation of the array
        $completeArray = $this->asArray();
        // Declare an empty array to return
        /** @var array<string, mixed> */
        $returnArray = [];
        foreach ($filters as $filter) {
            // Only return properties included in $filters array
            if (isset($completeArray[$filter])) {
                /** @var array<string, mixed> */
                $returnArray[$filter] = $completeArray[$filter];
            } else {
                $returnArray[$filter] = null;
            }
        }
        return $returnArray;
    }
}
