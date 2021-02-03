<?php

namespace OmegaUp;

class Timestamp implements \JsonSerializable {
    /**
     * @var int
     * @readonly
     */
    public $time;

    /**
     * @param int|\OmegaUp\Timestamp $time
     */
    public function __construct($time) {
        $this->time = (
            $time instanceof \OmegaUp\Timestamp ?
            $time->time :
            $time
        );
    }

    public function jsonSerialize() {
        return $this->time;
    }
}
