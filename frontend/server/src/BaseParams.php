<?php

namespace OmegaUp;

class BaseParams {
    /**
     *
     */
    public function __construct() {
    }

    /**
     * Update properties of $object based on what is provided in this class.
     *
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool, alias?: string}> $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    public function updateValueParams(
        object $object,
        array $properties
    ): bool {
        $importantChange = false;
        foreach ($properties as $source => $info) {
            /** @var null|callable(mixed):mixed */
            $transform = null;
            $important = false;
            if (is_int($source)) {
                $thisFieldName = $info;
                $objectFieldName = $info;
            } else {
                $thisFieldName = $source;
                if (isset($info['transform'])) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
                if (!empty($info['alias'])) {
                    $objectFieldName = $info['alias'];
                } else {
                    $objectFieldName = $thisFieldName;
                }
            }
            // Get or calculate new value.
            /** @var null|mixed */
            $value = $this->$thisFieldName;
            if (is_null($value)) {
                continue;
            }
            if (!is_null($transform)) {
                /** @var mixed */
                $value = $transform($value);
            }
            // Important property, so check if it changes.
            if ($important && !$importantChange) {
                $importantChange = ($value != $object->$objectFieldName);
            }
            $object->$objectFieldName = $value;
        }
        return $importantChange;
    }
}
