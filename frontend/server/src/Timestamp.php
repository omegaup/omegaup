<?php

namespace OmegaUp;

class Timestamp implements \JsonSerializable {
    /**
     * @var int
     * @readonly
     */
    public $time;

    public function __construct(int $time) {
        $this->time = $time;
    }

    public function jsonSerialize() {
        return $this->time;
    }
}
