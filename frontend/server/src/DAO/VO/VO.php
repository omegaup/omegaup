<?php

namespace OmegaUp\DAO\VO;

/**
 * Value Object.
 *
 * Esta clase abstracta comprende metodos comunes para todos los objetos VO.
 */
abstract class VO {
    /**
     * Gets an associative array that is good for JSON marshaling.
     *
     * @return array<string, null|scalar|\OmegaUp\Timestamp>
     */
    public function asArray(): array {
        /** @var array<string, null|scalar|\OmegaUp\Timestamp> */
        $result = get_object_vars($this);
        return $result;
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
     * @return array<string, null|scalar|\OmegaUp\Timestamp>
     */
    public function asFilteredArray(iterable $filters): array {
        // Get the complete representation of the array
        $completeArray = $this->asArray();
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
