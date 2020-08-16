<?php

namespace OmegaUp;

/**
 * Class to identify a string as being a translation string.
 */
class TranslationString implements \JsonSerializable {
    /**
     * @var string
     * @readonly
     */
    public $message;

    public function __construct(string $message) {
        $this->message = $message;
    }

    public function __toString(): string {
        return $this->message;
    }

    public function jsonSerialize(): string {
        return $this->message;
    }
}
